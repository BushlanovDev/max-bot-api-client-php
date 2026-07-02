# Max Messenger Bot API Client library for PHP

[![Actions status](https://github.com/BushlanovDev/max-bot-api-client-php/actions/workflows/ci.yml/badge.svg?style=flat-square)](https://github.com/BushlanovDev/max-bot-api-client-php/actions)
[![Coverage](https://raw.githubusercontent.com/BushlanovDev/max-bot-api-client-php/refs/heads/master/.github/badge-coverage.svg?v=2)](https://github.com/BushlanovDev/max-bot-api-client-php/actions)
[![Packagist Version](https://img.shields.io/packagist/v/bushlanov-dev/max-bot-api-client-php.svg?style=flat-square)](https://packagist.org/packages/bushlanov-dev/max-bot-api-client-php)
[![PHP version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg?style=flat-square)](https://github.com/BushlanovDev/max-bot-api-client-php)
[![Laravel](https://img.shields.io/badge/%20Laravel%20Package-available-success?logo=laravel&style=flat-square)](https://github.com/BushlanovDev/max-bot-api-client-php)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

> [!CAUTION]  
> На мой взгляд `Max Messenger` является ни чем иным как малварью, созданной для слежки за гражданами РФ. Настоятельно
> не рекомендую использовать его на реальных устройствах, с настоящим номером телефона, и для личной переписки.  
> Обязательно к прочтению - [Месседжер MAX следит за пользователями VPN](https://habr.com/ru/articles/1006666/)

## Быстрый старт

> Если вы новичок, то можете прочитать [официальную документацию](https://dev.max.ru/), написанную разработчиками Max.

> ℹ️ С полной документацией [вы можете ознакомиться тут](./docs/README.md).

### Получение токена

Откройте диалог с [MasterBot](https://max.ru/MasterBot), следуйте инструкциям и создайте нового бота. После создания
бота MasterBot отправит вам токен.

### Установка библиотеки

```bash
composer require bushlanov-dev/max-bot-api-client-php
```

Пользователи Laravel могут зарегистрировать сервис провайдер и фасад в `config/app.php`:

```php
'providers' => [
    // ...
    BushlanovDev\MaxMessengerBot\Laravel\MaxBotServiceProvider::class,
],
// ...
'aliases' => [
    // ...
    'MaxBot' => BushlanovDev\MaxMessengerBot\Laravel\MaxBotFacade::class,
],
```

### Использование

> [!TIP] Гибкая настройка Guzzle  
> Мах часто меняют домен API а теперь еще и сертификат от минцифры.  
> Если вы не хотите или не можете установить сертификат на прямую в систему, можно собрать объект API с кастомным Guzzle клиентом  
> Во всех остальных случаях достаточно минимального $api = new Api('YOUR_BOT_API_TOKEN');

```php
$guzzle = new \GuzzleHttp\Client([
    'timeout' => 10,
    'connect_timeout' => 5,
    'read_timeout' => 10,
    'headers' => ['User-Agent' => 'max-bot-api-client-php'],
    'verify' => false, // Отключить проверку либо путь до сертификата '/path/to/cert.pem'
]);

$httpFactory = new \GuzzleHttp\Psr7\HttpFactory();

$client = new \BushlanovDev\MaxMessengerBot\Client(
    accessToken: 'YOUR_BOT_API_TOKEN',
    httpClient: $guzzle,
    requestFactory: $httpFactory,
    streamFactory: $httpFactory,
    baseUrl: 'https://platform-api2.max.ru',
);

$api = new BushlanovDev\MaxMessengerBot\Api(
    client: $client,
    modelFactory: new BushlanovDev\MaxMessengerBot\ModelFactory(),
);
```

Отправка сообщения с клавиатурой

```php
require __DIR__.'/vendor/autoload.php';

use BushlanovDev\MaxMessengerBot\Api;

$api = new Api('YOUR_BOT_API_TOKEN');

// Загрузка файла
$fileAttachmentRequest = $api->uploadAttachment(
    type: UploadType::File,
    filePath: __DIR__ . '/test.pdf',
);

$api->sendMessage(
    userId: 123,     // ID пользователя получателя сообщения
    chatId: 321,     // Или ID чата, в который нужно отправить сообщение
    text: 'Привет!', // Текст сообщения, вы можете использовать HTML или Markdown
    attachments: [
        $fileAttachmentRequest,
        new InlineKeyboardAttachmentRequest([
            [new CallbackButton('Нажми меня!', 'payload_button1')],
            [new LinkButton('Нажми меня!', 'https://example.com')],
        ]),
    ],
    format: MessageFormat::Markdown, // Формат сообщения (Markdown или HTML)
);
```

Отправка сообщения с использованием фасада Laravel

```php
MaxBot::sendUserMessage(123456, 'Привет из Laravel!');
```

Создание универсального обработчика обновлений

```php
$dispatcher = $api->getUpdateDispatcher();

$dispatcher->onMessageCreated(function (MessageCreatedUpdate $update, Api $api) {
    $api->sendMessage(
        userId: $update->message->recipient->userId,
        text: 'Привет!',
    );
});
// или
$dispatcher->addHandler(UpdateType::BotStarted, function (BotStartedUpdate $update, Api $api) {
    $api->sendMessage(
        chatId: $update->chatId,
        text: 'Я запущен!',
    );
});
```

Подписка на вэб хуки

```php
$api->subscribe(
    url: 'https://example.com/webhook', // HTTPS URL на который будут приходить хуки
    secret: 'super_secret',             // Секретная фраза для проверки хуков
    updateTypes: [
        // Типы хуков которые вы хотите получать (либо ничего не указывать, чтобы получать все)
        UpdateType::BotStarted,
        UpdateType::MessageCreated,
    ],
);
```

Обработка обновлений

```php
$handler = $api->createWebhookHandler('super_secret'); // Обновления через вебхук
// ИЛИ
$handler = $api->createLongPollingHandler();           // Обновления через лонгполлинг

$handler->handle();
```

> ℹ️ С полной документацией [вы можете ознакомиться тут](./docs/README.md).

## Реализованные методы

#### Bots

- [x] `GET /me` (`getBotInfo`) - [*Получение информации о боте.*](./docs/README.md#Получение-информации-о-боте)
- [x] `PATCH /me` (`editBotInfo`) - [*Редактирование информации о боте.*](./docs/README.md#Редактирование-информации-о-боте)

#### Chats

- [x] ~~`GET /chats` (`getChats`) - [*Получение списка всех чатов бота.*](./docs/README.md#Получение-списка-всех-чатов-бота)~~ (deprecated)
- [x] `GET /chats/{chatLink}` (`getChatByLink`) - [*Получение информации о чате по ссылке.*](./docs/README.md#Получение-информации-о-чате-по-ссылке)
- [x] `GET /chats/{chatId}` (`getChat`) - [*Получение информации о чате по ID.*](./docs/README.md#Получение-информации-о-чате-по-ID)
- [x] `PATCH /chats/{chatId}` (`editChat`) - [*Редактирование информации о чате.*](./docs/README.md#Редактирование-информации-о-чате)
- [x] `DELETE /chats/{chatId}` (`deleteChat`) - [*Удаление чата.*](./docs/README.md#Удаление-чата)
- [x] `POST /chats/{chatId}/actions` (`sendAction`) - [*Отправка действия в чат (например, "печатает...").*](./docs/README.md#Отправка-действия-в-чат)
- [x] `GET /chats/{chatId}/pin` (`getPinnedMessage`) - [*Получение закрепленного сообщения.*](./docs/README.md#Получение-закрепленного-сообщения)
- [x] `PUT /chats/{chatId}/pin` (`pinMessage`) - [*Закрепление сообщения.*](./docs/README.md#Закрепление-сообщения)
- [x] `DELETE /chats/{chatId}/pin` (`unpinMessage`) - [*Открепление сообщения.*](./docs/README.md#Открепление-сообщения)
- [x] `GET /chats/{chatId}/members/me` (`getMembership`) - [*Получение информации о членстве бота в чате.*](./docs/README.md#Получение-информации-о-членстве-бота-в-чате)
- [x] `DELETE /chats/{chatId}/members/me` (`leaveChat`) - [*Выход бота из чата.*](./docs/README.md#Выход-бота-из-чата)
- [x] `GET /chats/{chatId}/members/admins` (`getAdmins`) - [*Получение администраторов чата.*](./docs/README.md#Получение-администраторов-чата)
- [x] `POST /chats/{chatId}/members/admins` (`addAdmins`) - [*Назначение администраторов чата.*](./docs/README.md#Назначение-администраторов-чата)
- [x] `DELETE /chats/{chatId}/members/admins/{userId}` (`deleteAdmin`) - [*Снятие прав администратора.*](./docs/README.md#Снятие-прав-администратора)
- [x] `GET /chats/{chatId}/members` (`getMembers`) - [*Получение участников чата.*](./docs/README.md#Получение-участников-чата)
- [x] `POST /chats/{chatId}/members` (`addMembers`) - [*Добавление участников в чат.*](./docs/README.md#Добавление-участников-в-чат)
- [x] `DELETE /chats/{chatId}/members` (`deleteMember`) - [*Удаление участника из чата.*](./docs/README.md#Удаление-участника-из-чата)

#### Subscriptions

- [x] `GET /subscriptions` (`getSubscriptions`) - [*Получение списка Webhook-подписок.*](./docs/README.md#Получение-списка-Webhook-подписок)
- [x] `POST /subscriptions` (`subscribe`) - [*Создание Webhook-подписки.*](./docs/README.md#Создание-Webhook-подписки)
- [x] `DELETE /subscriptions` (`unsubscribe`) - [*Удаление Webhook-подписки.*](./docs/README.md#Удаление-Webhook-подписки)
- [x] `GET /updates` (`getUpdates`) - [*Получение обновлений через Long-Polling.*](./docs/README.md#Получение-обновлений-через-Long-Polling)

#### Upload

- [x] `POST /uploads` (`getUploadUrl`) - [*Получение URL для загрузки файла.*](./docs/README.md#Получение-URL-для-загрузки-файла)

#### Messages

- [x] `GET /messages` (`getMessages`) - [*Получение списка сообщений из чата.*](./docs/README.md#Получение-списка-сообщений-из-чата)
- [x] `POST /messages` (`sendMessage`) - [*Отправка сообщения.*](./docs/README.md#Отправка-сообщения)
- [x] `PUT /messages` (`editMessage`) - [*Редактирование сообщения.*](./docs/README.md#Редактирование-сообщения)
- [x] `DELETE /messages` (`deleteMessage`) - [*Удаление сообщения.*](./docs/README.md#Удаление-сообщения)
- [x] `GET /messages/{messageId}` (`getMessageById`) - [*Получение сообщения по ID.*](./docs/README.md#Получение-сообщения-по-ID)
- [x] `GET /videos/{videoToken}` (`getVideoAttachmentDetails`) - [*Получение детальной информации о видео.*](./docs/README.md#Получение-детальной-информации-о-видео)
- [x] `POST /answers` (`answerOnCallback`) - [*Ответ на нажатие callback-кнопки.*](./docs/README.md#Ответ-на-нажатие-callback-кнопки)

## Лицензия

Данная библиотека распространяется под лицензией MIT - подробности см. в файле [LICENSE](LICENSE).
