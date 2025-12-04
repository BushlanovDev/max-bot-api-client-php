<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * A class designed to process incoming webhook requests from the Max API.
 * It verifies the request's authenticity, parses it, and uses an UpdateDispatcher
 * to route it to the appropriate handler.
 */
final readonly class WebhookHandler
{
    /**
     * @param UpdateDispatcher $dispatcher The update dispatcher.
     * @param ModelFactory $modelFactory The model factory.
     * @param LoggerInterface $logger PSR LoggerInterface.
     * @param string|null $secret The secret key for request verification.
     */
    public function __construct(
        private UpdateDispatcher $dispatcher,
        private ModelFactory $modelFactory,
        private LoggerInterface $logger,
        private ?string $secret,
    ) {
    }

    /**
     * Processes an incoming webhook request.
     * It reads the HTTP request, verifies, parses, and dispatches the update.
     *
     * @param ServerRequestInterface|null $request The PSR-7 HTTP request. If null, created from globals.
     *
     * @throws \ReflectionException
     * @throws SecurityException
     * @throws SerializationException
     * @throws \LogicException
     */
    public function handle(?ServerRequestInterface $request = null): void
    {
        if ($request === null) {
            if (!class_exists(\GuzzleHttp\Psr7\ServerRequest::class)) {
                throw new \LogicException(
                    'No ServerRequest was provided and "guzzlehttp/psr7" is not found. ' .
                    'Please run "composer require guzzlehttp/psr7" or create and pass your own PSR-7 request object.',
                );
            }
            $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
        }

        $payload = (string)$request->getBody();
        $this->logger->debug('Received webhook payload', ['body' => $payload]);

        if (empty($payload)) {
            throw new SerializationException('Webhook body is empty.');
        }

        $this->verifySignature($request->getHeaderLine('X-Max-Bot-Api-Secret'));

        try {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->error('Failed to decode webhook JSON', ['payload' => $payload, 'exception' => $e]);
            throw new SerializationException('Failed to decode webhook body as JSON.', 0, $e);
        }

        try {
            $update = $this->modelFactory->createUpdate($data);
            $this->dispatcher->dispatch($update);
        } catch (\LogicException $e) {
            $this->logger->debug($e->getMessage(), ['payload' => $payload, 'exception' => $e]);
        }

        if (!headers_sent()) {
            http_response_code(200);
        }
    }

    /**
     * Verifies the 'X-Max-Bot-Api-Secret' header if a secret is configured.
     *
     * @param string $signature
     *
     * @throws SecurityException
     */
    private function verifySignature(string $signature): void
    {
        if ($this->secret === null) {
            return;
        }

        if (!hash_equals($this->secret, $signature)) {
            $this->logger->warning('Webhook signature verification failed', ['received_signature' => $signature]);
            throw new SecurityException('Signature verification failed.');
        }
    }
}
