- [Быстрый старт](#Быстрый-старт)
    - [Получение токена](#Получение-токена)
    - [Установка библиотеки](#Установка-библиотеки)
    - [Инициализация бота](#Инициализация-бота)
- [Информация о боте](#Информация-о-боте)
    - `GET /me` (`getBotInfo`) - [*Получение информации о боте.*](#Получение-информации-о-боте)
    - `PATCH /me` (`editBotInfo`) - [*Редактирование информации о боте.*](#Редактирование-информации-о-боте)
- [Чаты](#Чаты)
    - `GET /chats` (`getChats`) - [*Получение списка всех чатов бота.*](#Получение-списка-всех-чатов-бота)
    - `GET /chats/{chatLink}` (`getChatByLink`) - [*Получение информации о чате по ссылке.*](#Получение-информации-о-чате-по-ссылке)
    - `GET /chats/{chatId}` (`getChat`) - [*Получение информации о чате по ID.*](#Получение-информации-о-чате-по-ID)
    - `PATCH /chats/{chatId}` (`editChat`) - [*Редактирование информации о чате.*](#Редактирование-информации-о-чате)
    - `DELETE /chats/{chatId}` (`deleteChat`) - [*Удаление чата.*](#Удаление-чата)
    - `POST /chats/{chatId}/actions` (`sendAction`) - [*Отправка действия в чат (например, "печатает...").*](#Отправка-действия-в-чат)
    - `GET /chats/{chatId}/pin` (`getPinnedMessage`) - [*Получение закрепленного сообщения.*](#Получение-закрепленного-сообщения)
    - `PUT /chats/{chatId}/pin` (`pinMessage`) - [*Закрепление сообщения.*](#Закрепление-сообщения)
    - `DELETE /chats/{chatId}/pin` (`unpinMessage`) - [*Открепление сообщения.*](#Открепление-сообщения)
    - `GET /chats/{chatId}/members/me` (`getMembership`) - [*Получение информации о членстве бота в чате.*](#Получение-информации-о-членстве-бота-в-чате)
    - `DELETE /chats/{chatId}/members/me` (`leaveChat`) - [*Выход бота из чата.*](#Выход-бота-из-чата)
    - `GET /chats/{chatId}/members/admins` (`getAdmins`) - [*Получение администраторов чата.*](#Получение-администраторов-чата)
    - `POST /chats/{chatId}/members/admins` (`addAdmin`) - [*Назначение администраторов чата.*](#Назначение-администраторов-чата)
    - `DELETE /chats/{chatId}/members/admins/{userId}` (`deleteAdmins`) - [*Снятие прав администратора.*](#Снятие-прав-администратора)
    - `GET /chats/{chatId}/members` (`getMembers`) - [*Получение участников чата.*](#Получение-участников-чата)
    - `POST /chats/{chatId}/members` (`addMembers`) - [*Добавление участников в чат.*](#Добавление-участников-в-чат)
    - `DELETE /chats/{chatId}/members` (`deleteMember`) -[*Удаление участника из чата.*](#Удаление-участника-из-чата)
- [Получение обновлений](#Получение-обновлений)
    - `GET /subscriptions` (`getSubscriptions`) - [*Получение списка Webhook-подписок.*](#Получение-списка-Webhook-подписок)
    - `POST /subscriptions` (`subscribe`) - [*Создание Webhook-подписки.*](#Создание-Webhook-подписки)
    - `DELETE /subscriptions` (`unsubscribe`) - [*Удаление Webhook-подписки.*](#Удаление-Webhook-подписки)
    - `GET /updates` (`getUpdates`) - [*Получение обновлений через Long-Polling.*](#Получение-обновлений-через-Long-Polling)
- [Загрузка файлов](#Загрузка-файлов)
    - `POST /uploads` (`getUploadUrl`) - [*Получение URL для загрузки файла.*](#Получение-URL-для-загрузки-файла)
    - `uploadAttachment` - [*Загрузка файла.*](#Загрузка-файла)
- [Сообщения](#Сообщения)
    - `GET /messages` (`getMessages`) - [*Получение списка сообщений из чата.*](#Получение-списка-сообщений-из-чата)
    - `POST /messages` (`sendMessage`) - [*Отправка сообщения.*](#Отправка-сообщения)
    - `PUT /messages` (`editMessage`) - [*Редактирование сообщения.*](#Редактирование-сообщения)
    - `DELETE /messages` (`deleteMessage`) - [*Удаление сообщения.*](#Удаление-сообщения)
    - `GET /messages/{messageId}` (`getMessageById`) - [*Получение сообщения по ID.*](#Получение-сообщения-по-ID)
    - `GET /videos/{videoToken}` (`getVideoAttachmentDetails`) - [*Получение детальной информации о видео.*](#Получение-детальной-информации-о-видео)
    - `POST /answers` (`answerOnCallback`) - [*Ответ на нажатие callback-кнопки.*](#Ответ-на-нажатие-callback-кнопки)
- [Laravel](#Laravel)
    - [Регистрация пакета](#Регистрация-пакета)
    - [Настройка](#Настройка)
    - [Использование](#Использование)
    - [Artisan команды](#Artisan-команды)
        - [Подписка на Webhook](#Подписка-на-Webhook)
        - [Удаление подписки](#Удаление-подписки)
        - [Список подписок](#Список-подписок)
    - [Обработка хуков](#Обработка-хуков)
    - [Long Polling](#Long-Polling)
    - [Handler Classes](#Handler-Classes)
    - [Тестирование](#Тестирование)

## Быстрый старт

> Если вы новичок, то можете прочитать [официальную документацию](https://dev.max.ru/), написанную разработчиками Max.

### Получение токена

Откройте диалог с [MasterBot](https://max.ru/MasterBot), следуйте инструкциям и создайте нового бота. После создания
бота MasterBot отправит вам токен.

### Установка библиотеки

```bash
composer require bushlanov-dev/max-bot-api-client-php
```

### Инициализация бота

Единственной обязательной настройкой является токен вашего бота.  
⚠️ Никогда, и ни при каких обстоятельствах не храните токен в коде. ⚠️  
Используйте переменные окружения!

```php
require __DIR__.'/vendor/autoload.php';

use BushlanovDev\MaxMessengerBot\Api;

$api = new Api('YOUR_BOT_API_TOKEN');
```

Так же вы можете создать экземпляр бота гибко настроив все зависимости под свои нужды.

```php
$api = new Api(
    client: new Client(...),
    modelFactory: new ModelFactory(),
    logger: new YourPsrLogger(),
);
```

## Информация о боте

### Получение информации о боте

Возвращает информацию о текущем боте, который идентифицируется с помощью токена доступа. 
Метод возвращает ID бота, его имя и аватар (если есть).

```php
$botInfo = $api->getBotInfo();
```

### Редактирование информации о боте

Обратите внимание, что данный метод отправляется PATCH запросом. Это значит, что будут обновлены только переданные
поля.  
В следующем примере мы изменяем только название бота и отчистим его описание. Остальные поля останутся неизменными.

```php
$botInfo = $api->editBotInfo(
    new BotPatch(
        name: 'Супер бот',
        description: null,
    )
);
```

## Чаты

### Получение списка всех чатов бота

Возвращает информацию о чатах, в которых участвовал бот. 
Результат включает список чатов и маркер для перехода к следующей странице.

```php
$chats = $api->getChats(
    count: 10, // Количество запрашиваемых чатов
    marker: 2, // Указатель на следующую страницу данных. Для первой страницы передайте null
);
```

### Получение информации о чате по ссылке

Возвращает информацию о чате по его публичной ссылке, либо информацию о диалоге с пользователем по его username.

```php
$chat = $api->getChatByLink('@super_chat'); // Публичная ссылка на чат или username пользователя
```

### Получение информации о чате по ID

```php
$chat = $api->getChat(12345);
```

### Редактирование информации о чате

Позволяет редактировать информацию о чате, включая название, иконку и закреплённое сообщение.

```php
$chat = $api->editChat(
    chatId: 12345,
    chatPatch: new ChatPatch(
        title: 'Новое название чата',
    ),
);
```

### Удаление чата

```php
$api->deleteChat(12345);
```

### Отправка действия в чат

Позволяет отправлять действия бота в чат, такие как «набор текста» или «отправка фото».

```php
$api->sendAction(
    chatId: 12345,
    action: SenderAction::SendingVideo,
);
```

### Получение закрепленного сообщения

```php
$message = $api->getPinnedMessage(12345);
```

### Закрепление сообщения

```php
$api->pinMessage(
    chatId: 12345,
    messageId: 54321,
    notify: true,
);
```

### Открепление сообщения

```php
$api->unpinMessage(12345);
```

### Получение информации о членстве бота в чате

```php
$chatMember = $api->getMembership(12345);
```

### Выход бота из чата

```php
$api->leaveChat(12345);
```

### Получение администраторов чата

Возвращает всех администраторов чата. Бот должен быть администратором в запрашиваемом чате.

```php
$adminsChatMemberList = $api->getAdmins(12345);
```

### Назначение администраторов чата

```php
$api->addAdmins(
    chatId: 12345,
    admins: [
        new ChatAdmin(123, [ChatAdminPermission::ReadAllMessages]),
        new ChatAdmin(456, [ChatAdminPermission::Write]),
    ],
);
```

### Снятие прав администратора

```php
$api->deleteAdmin(
    chatId: 12345,
    userId: 123,
);
```

### Получение участников чата

```php
$chatMemberList = $api->getMembers(12345);
```

### Добавление участников в чат

```php
$api->addMembers(
    chatId: 12345,
    userIds: [123, 456],
);
``` 

### Удаление участника из чата

```php
$api->deleteMember(
    chatId: 12345,
    userId: 123,
    block: true, // Пользователь будет заблокирован в чате
);
```

## Получение обновлений

### Получение списка Webhook-подписок

```php
$subscriptions = $api->getSubscriptions();
```

### Создание Webhook-подписки

Подписывает бота на получение обновлений через WebHook.  
После вызова этого метода бот будет получать уведомления о новых событиях в чатах на указанный URL.  
Ваш сервер должен прослушивать один из следующих портов: 80, 8080, 443, 8443, 16384-32383.

```php
$api->subscribe(
    url: 'https://example.com/webhook',        // URL на который будут приходить хуки. Должен начинаться с http(s)://
    secret: 'super_secret',                    // Секретная фраза для проверки хуков (необязательно)
    updateTypes: [UpdateType::MessageCreated], // Типы хуков которые вы хотите получать (либо ничего не указывать, чтобы получать все)
);
```

### Удаление Webhook-подписки

```php
$api->unsubscribe('https://example.com/webhook');
```

### Получение обновлений через Long-Polling

Этот метод можно использовать для получения обновлений, если ваш бот не подписан на WebHook. Метод использует долгий опрос (long polling).  
Каждое обновление имеет свой номер последовательности. Свойство marker в ответе указывает на следующее ожидаемое обновление.  
Все предыдущие обновления считаются завершенными после прохождения параметра marker.   
Если параметр marker не передан, бот получит все обновления, произошедшие после последнего подтверждения.

```php
$updateList = $api->getUpdates(
    limit: 10,                           // Максимальное количество обновлений для получения [1-1000] (необязательно) 
    timeout: 10,                         // Таймаут в секундах [0-90] (необязательно)
    marker: 123,                         // Если передан, бот получит обновления, которые еще не были получены (необязательно)
    types: [UpdateType::MessageCreated], // Типы обновлений которые вы хотите получать (необязательно)
);
```

## Загрузка файлов

### Получение URL для загрузки файла

```php
$uploadEndpoint = $api->getUploadUrl(UploadType::Video);
// Далее вы можете загрузить файл по полученному URL самостоятельно
// или воспользоваться методами Client::multipartUpload(), Client::resumableUpload(), Api::uploadFile()
```

### Загрузка файла

Данный метод получит URL для загрузки, отправит файл и вернет готовый аттачмент

```php
$photoAttachmentRequest = $api->uploadAttachment(
    type: UploadType::Image,
    filePath: __DIR__ . '/test.jpg',
);
```

## Сообщения

### Получение списка сообщений из чата

Возвращает сообщения в чате: страницу с результатами и маркер, указывающий на следующую страницу.  
Сообщения возвращаются в обратном порядке, то есть последние сообщения в чате будут первыми в массиве.  
Поэтому, если вы используете параметры from и to, то to должно быть меньше, чем from.

```php
$messages = $api->getMessages(
    chatId: 12345,          // ID чата, чтобы получить сообщения из определённого чата (необязательно)
    messageIds: [123, 456], // Список ID сообщений, которые нужно получить (необязательно)
    from: 10,               // Время начала для запрашиваемых сообщений [Unix timestamp] (необязательно)
    to: 20,                 // Время окончания для запрашиваемых сообщений [Unix timestamp] (необязательно)
    count: 10,              // Максимальное количество сообщений в ответе [1-100] (необязательно)
);
```

### Отправка сообщения

```php
$fileAttachmentRequest = $api->uploadAttachment(
    type: UploadType::File,
    filePath: __DIR__ . '/test.pdf',
);

$message = $api->sendMessage(
    userId: 12345,                      // Если вы отправляете сообщение пользователю, укажите его ID (необязательно)
    chatId: 54321,                      // Если сообщение отправляется в чат, укажите его ID (необязательно)
    text: 'Привет мир!',                // Текст сообщения (необязательно)
    attachments: [                      // Прикрепленные элементы (необязательно)
        $fileAttachmentRequest,
        PhotoAttachmentRequest::fromUrl('https://example.com/image.jpg'),
        new LocationAttachmentRequest(
            latitude: 55.7520233,
            longitude: 37.6174994,
        ),
    ],
    format: MessageFormat::Markdown,    // Формат сообщения Markdown или HTML (необязательно)
    link: null,                         // Ссылка на сообщение (необязательно)
    notify: true,                       // Если false, участники чата не будут уведомлены (необязательно)
    disableLinkPreview: false,          // Если false, сервер не будет генерировать превью для ссылок в тексте сообщения (необязательно)
);
```

### Редактирование сообщения

Редактирует сообщение в чате. Если поле attachments равно null, вложения текущего сообщения не изменяются.   
Если в этом поле передан пустой список, все вложения будут удалены.

```php
$api->editMessage(
    messageId: 12345,
    text: 'Привет мир!',
    attachments: null,
    format: null,
    link: null,
    notify: true,
);
```

### Удаление сообщения

```php
$api->deleteMessage(12345);
```

### Получение сообщения по ID

```php
$message = $api->getMessageById(12345);
```

### Получение детальной информации о видео

Возвращает подробную информацию о приклеплённом видео. URL-адреса воспроизведения и дополнительные метаданные.

```php
$videoAttachmentDetails = $api->getVideoAttachmentDetails('some-video-token');
```

### Ответ на нажатие callback-кнопки

Этот метод используется для отправки ответа после того, как пользователь нажал на кнопку.  
Ответом может быть обновленное сообщение и/или одноразовое уведомление для пользователя.

```php
$api->answerOnCallback(
    callbackId: 'some-callback-id',    // Идентификатор кнопки, по которой пользователь кликнул
    notification: 'some-notification', // Заполните это, если хотите просто отправить одноразовое уведомление пользователю (необязательно)
    text: 'some-text',                 // Новый текст сообщения (необязательно)
    attachments: null,                 // Вложения сообщения. Если пусто, все вложения будут удалены (необязательно)
    link: null,                        // Ссылка на сообщение (необязательно)
    format: null,                      // Формат сообщения Markdown или HTML (необязательно)
    notify: true,                      // Заполните это, если хотите просто отправить одноразовое уведомление пользователю (необязательно)
);
```

## Laravel

### Регистрация пакета
Пакет будет автоматически обнаружен Laravel.  
Если автоматическое обнаружение отключено можно зарегистрировать сервис провайдер и фасад в `config/app.php`:

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
### Настройка

При не необходимости опубликовать конфиг выполните следующую команду:

```bash
php artisan vendor:publish --provider="BushlanovDev\MaxMessengerBot\Laravel\MaxBotServiceProvider"
```

Для работы вам потребуется добавить ваш токен в файл `.env`

```env
MAXBOT_ACCESS_TOKEN=your_bot_access_token_here
MAXBOT_WEBHOOK_SECRET=your_webhook_secret_here
```

### Использование

Все методы бота доступны через фасад MaxBot, например:

```php
use MaxBot;

// Отправка сообщения пользователю
MaxBot::sendUserMessage(123456, 'Hello from Laravel!');

// Получение обновления
$updates = MaxBot::getUpdates();
```

### Artisan команды

#### Подписка на Webhook

```bash
# Подписка на получение обновлений
php artisan maxbot:webhook:subscribe https://yourapp.com/bot/webhook

# С верификацией
php artisan maxbot:webhook:subscribe https://yourapp.com/bot/webhook --secret=your_secret_key

# Подписка только на определенные типы событий
php artisan maxbot:webhook:subscribe https://yourapp.com/bot/webhook --types=message_created --types=message_callback
```

#### Удаление подписки

```bash
php artisan maxbot:webhook:unsubscribe https://yourapp.com/bot/webhook
```

#### Список подписок

```bash
php artisan maxbot:webhook:list
```

### Обработка хуков

Создайте контроллер для обработчика:

```php
use Illuminate\Http\Request;
use BushlanovDev\MaxMessengerBot\Laravel\MaxBotManager;

class WebhookController extends Controller
{
    public function handle(Request $request, MaxBotManager $botManager)
    {
        // Обработчик сообщений
        $botManager->onMessageCreated(function (MessageCreatedUpdate $update) {
            $message = $update->message;
            // ...
        });

        // Обработчик команды /start
        $botManager->onCommand('start', function (MessageCreatedUpdate $update) {
            // ...
        });

        // Using Laravel container bindings
        $botManager->onMessageCreated(MessageHandler::class);
        $botManager->onCommand('help', HelpCommandHandler::class);

        return $botManager->handleWebhook($request);
    }
}
```

Добавьте маршрут в  `routes/web.php`:

```php
Route::post('/bot/webhook', [WebhookController::class, 'handle']);
```

### Long Polling

Создайте artisan команду для получения long polling обновлений:

```php
use Illuminate\Console\Command;
use BushlanovDev\MaxMessengerBot\Laravel\MaxBotManager;

class BotPollingCommand extends Command
{
    protected $signature = 'bot:polling';
    
    protected $description = 'Start bot long polling';

    public function handle(MaxBotManager $botManager)
    {
        $this->info('Starting bot polling...');
        $botManager->startLongPolling();
    }
}
```

### Handler Classes

Примеры классов обработчиков обновлений:

```php
class MessageHandler
{
    public function handle(MessageCreatedUpdate $update)
    {
        $message = $update->message;
        $text = $message->body?->text;
        
        if ($text) {
            app(Api::class)->sendMessage(
                userId: $message->sender->userId,
                text: "You said: $text",
            );
        }
    }
}

class HelpCommandHandler
{
    public function handle(MessageCreatedUpdate $update)
    {
        app(Api::class)->sendMessage(
            userId: $update->message->sender->userId,
            text: "This is help message",
        );
    }
}
```

### Тестирование

Вы можете использовать мок API для тестирования:

```php
use BushlanovDev\MaxMessengerBot\Api;

class BotTest extends TestCase
{
    public function test_bot_sends_message()
    {
        $apiMock = $this->createMock(Api::class);
        $apiMock->expects($this->once())
            ->method('sendUserMessage')
            ->with(123456, 'Hello!');

        $this->app->instance(Api::class, $apiMock);

        // Your test code here
    }
}
```
