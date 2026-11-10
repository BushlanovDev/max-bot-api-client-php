<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Client;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use BushlanovDev\MaxMessengerBot\Exceptions\RateLimitExceededException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)]
final class GuzzleCompatibilityTest extends TestCase
{
    #[Test]
    public function sendsJsonThroughTheRealGuzzlePsr18Client(): void
    {
        $history = [];
        $client = $this->client([new Response(200, [], '{"message_id":"message-1"}')], $history);

        self::assertSame(['message_id' => 'message-1'], $client->request('POST', '/messages', ['chat_id' => 123], ['text' => 'Hello']));
        self::assertCount(1, $history);
        $request = $history[0]['request'];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('chat_id=123', $request->getUri()->getQuery());
        self::assertSame('test-token', $request->getHeaderLine('Authorization'));
        self::assertSame('application/json; charset=utf-8', $request->getHeaderLine('Content-Type'));
        self::assertSame('{"text":"Hello"}', (string) $request->getBody());
    }

    #[Test]
    public function sendsALowerCaseMethodInUpperCase(): void
    {
        $history = [];
        $client = $this->client([new Response(200, [], '{"user_id":1}')], $history);

        self::assertSame(['user_id' => 1], $client->request('get', '/me'));
        self::assertCount(1, $history);
        self::assertSame('GET', $history[0]['request']->getMethod());
    }

    #[Test]
    public function preservesHttpErrorsForTheSdkToHandle(): void
    {
        $history = [];
        $client = $this->client([new Response(429, [], '{"message":"Too many requests"}')], $history);

        $this->expectException(RateLimitExceededException::class);
        $client->request('POST', '/messages', [], ['text' => 'Hello']);
    }

    #[Test]
    public function wrapsTransportFailuresWithoutRetryingAnOutboundMessage(): void
    {
        $history = [];
        $failure = new ConnectException('Connection timed out', new Request('POST', 'https://example.test/messages'));
        $client = $this->client([$failure, new Response(200, [], '{"success":true}')], $history);

        try {
            $client->request('POST', '/messages', [], ['text' => 'Hello']);
            self::fail('The network failure must reach the caller.');
        } catch (NetworkException $exception) {
            self::assertSame($failure, $exception->getPrevious());
            self::assertCount(1, $history);
        }
    }

    #[Test]
    public function uploadsAMultipartStreamUsingTheRealPsr7Factory(): void
    {
        $history = [];
        $client = $this->client([new Response(200, [], '{"token":"upload-1"}')], $history);
        $file = fopen('php://temp', 'w+');
        fwrite($file, 'file contents');
        rewind($file);

        try {
            self::assertSame('{"token":"upload-1"}', $client->multipartUpload('https://example.test/upload', $file, 'ticket.pdf'));
        } finally {
            fclose($file);
        }

        $request = $history[0]['request'];
        self::assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));
        self::assertStringContainsString('filename="ticket.pdf"', (string) $request->getBody());
        self::assertStringContainsString('file contents', (string) $request->getBody());
    }

    #[Test]
    public function uploadsEveryResumableChunkExactlyOnce(): void
    {
        $history = [];
        $client = $this->client([new Response(200), new Response(200, [], '{"token":"upload-1"}')], $history);
        $file = fopen('php://temp', 'w+');
        fwrite($file, 'abcdef');
        rewind($file);

        try {
            self::assertSame('{"token":"upload-1"}', $client->resumableUpload('https://example.test/upload', $file, 'video.mp4', 6, 3));
        } finally {
            fclose($file);
        }

        self::assertCount(2, $history);
        self::assertSame('bytes 0-2/6', $history[0]['request']->getHeaderLine('Content-Range'));
        self::assertSame('bytes 3-5/6', $history[1]['request']->getHeaderLine('Content-Range'));
        self::assertSame('abc', (string) $history[0]['request']->getBody());
        self::assertSame('def', (string) $history[1]['request']->getBody());
    }

    private function client(array $queue, array &$history): Client
    {
        $stack = HandlerStack::create(new MockHandler($queue));
        $stack->push(Middleware::history($history));
        $factory = new HttpFactory();

        return new Client('test-token', new GuzzleClient(['handler' => $stack]), $factory, $factory, 'https://example.test');
    }
}
