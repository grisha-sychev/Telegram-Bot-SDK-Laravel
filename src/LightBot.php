<?php

namespace Teg;

use Closure;
use Teg\Api\Skeleton;
use Teg\Support\Facades\Services;

class LightBot extends Skeleton
{
    public $getMessage;
    public $getVideo;
    public $getVideoId;
    public $getPhoto;
    public $getPhotoId;
    public $getCallback;
    public $getCallbackData;
    public $getMessageId;
    public $getMessageText;
    public $getUserId;

    public function __construct()
    {
        $this->getCallback = $this->getCallbackQuery();
        $this->getMessage = $this->getMessage();

        $this->getUserId = isset($this->getMessage) ? $this->getMessage->getFrom()->getId() : (isset($this->getCallback) ? $this->getCallback->getFrom()->getId() : null);
        $this->getMessageText = isset($this->getMessage) ? $this->getMessage->getText() : null;
        $this->getMessageId = isset($this->getMessage) ? $this->getMessage->getMessageId() : (isset($this->getCallback) ? $this->getCallback->getMessage()->getMessageId()  : null);
    }

    /**
     * Запускает основной процесс для клиента.
     *
     * Этот метод определяет класс, который его вызвал, извлекает части пространства имен
     * и устанавливает свойство bot в нижний регистр предпоследней части пространства имен.
     *
     * @return void
     */
    public function run()
    {
        $calledClass = get_called_class();
        $namespaceParts = explode('\\', $calledClass);
        $className = $namespaceParts[count($namespaceParts) - 1];
        $this->bot = strtolower(str_replace('Bot', '', $className));
        return $this;
    }


    /**
     * Отправляет отладочную информацию о текущем запросе в формате JSON.
     *
     * @return void
     */
    public function debug($data = null)
    {
        $data = $data ?? $this->request();

        $this->sendSelf("<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>");
        exit;
    }

    /**
     * Метод отправки сообщения другому пользователю
     *
     * @param int $id Идентификатор пользователя.
     * @param string $message Текст сообщения.
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * @param int $type_keyboard Тип каливатуры 0 - keyboard 1 - inlineKeyboard
     *
     */
    public function sendOut($id, $message, $keyboard = null, $layout = 2, $type_keyboard = 0)
    {
        $keyboard = $keyboard !== null ? Services::simpleKeyboard($keyboard) : $keyboard;
        is_array($message) ? $message = Services::html($message) : $message;
        $keyboard ? $keygrid = Services::grid($keyboard, $layout) : $keyboard;
        $type_keyboard === 1 ? $type = "inlineKeyboard" : $type = "keyboard";
        return $this->sendMessage($id, $message, null, null, "HTML", null, null, false, false, null, null, $keyboard ? Services::$type($keygrid) : $keyboard);
    }

    /**
     * Метод отправки сообщения текущему пользователю
     *
     * @param string|array $message Текст сообщения.
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int|array $layout Число делений или массив с ручным расположением.
     * @param int $type_keyboard Тип каливатуры 1 - keyboard 2 - inlineKeyboard
     * 
     */
    public function sendSelf($message, $keyboard = null, $layout = 2, $type_keyboard = 0)
    {
        return $this->sendOut($this->getUserId, $message, $keyboard, $layout, $type_keyboard, "HTML");
    }

    /**
     * Метод отправки фото другому пользователю
     *
     * @param int $chat_id Идентификатор чата.
     * @param string $photo URL или файл фото.
     * @param string|null $caption Подпись к фото (необязательно).
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * @param int $type_keyboard Тип клавиатуры 0 - keyboard 1 - inlineKeyboard
     * 
     */
    public function sendOutPhoto($chat_id, $photo, $caption = null, $keyboard = null, $layout = 2, $type_keyboard = 0)
    {
        $keyboard = $keyboard !== null ? Services::simpleKeyboard($keyboard) : $keyboard;
        $keyboard ? $keygrid = Services::grid($keyboard, $layout) : $keyboard;
        $type_keyboard === 1 ? $type = "inlineKeyboard" : $type = "keyboard";
        return $this->sendPhoto($chat_id, $photo, $caption, 'HTML', null, null, null, false, false, null, null, $keyboard ? Services::$type($keygrid) : $keyboard);
    }

    /**
     * Метод отправки фото текущему пользователю
     *
     * @param string $photo URL или файл фото.
     * @param string|null $caption Подпись к фото (необязательно).
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * @param int $type_keyboard Тип клавиатуры 0 - keyboard 1 - inlineKeyboard
     * 
     */
    public function sendSelfPhoto($photo, $caption = null, $keyboard = null, $layout = 2, $type_keyboard = 0)
    {
        return $this->sendOutPhoto($this->getUserId, $photo, $caption, $keyboard, $layout, $type_keyboard);
    }

    /**
     * Метод отправки фото текущему пользователю
     *
     * @param string $photo URL или файл фото.
     * @param string|null $caption Подпись к фото (необязательно).
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * 
     */
    public function sendSelfPhotoInline($photo, $caption = null, $keyboard = null, $layout = 2)
    {
        return $this->sendOutPhoto($this->getUserId, $photo, $caption, $keyboard, $layout, 1);
    }

    /**
     * Метод отправки видео другому пользователю
     *
     * @param int $chat_id Идентификатор чата.
     * @param string $video URL или файл видео.
     * @param string|null $caption Подпись к видео (необязательно).
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * @param int $type_keyboard Тип клавиатуры 0 - keyboard 1 - inlineKeyboard
     * 
     */
    public function sendOutVideo($chat_id, $video, $caption = null, $keyboard = null, $layout = 2, $type_keyboard = 0)
    {
        $keyboard = $keyboard !== null ? Services::simpleKeyboard($keyboard) : $keyboard;
        $keyboard ? $keygrid = Services::grid($keyboard, $layout) : $keyboard;
        $type_keyboard === 1 ? $type = "inlineKeyboard" : $type = "keyboard";
        return $this->sendVideo($chat_id, $video, null, null, null, null, null, null, $caption, 'HTML', null, false, false, null, null, $keyboard ? Services::$type($keygrid) : $keyboard);
    }

    /**
     * Метод отправки видео текущему пользователю
     *
     * @param string $video URL или файл видео.
     * @param string|null $caption Подпись к видео (необязательно).
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * @param int $type_keyboard Тип клавиатуры 0 - keyboard 1 - inlineKeyboard
     * 
     */
    public function sendSelfVideo($video, $caption = null, $keyboard = null, $layout = 2, $type_keyboard = 0)
    {
        return $this->sendOutVideo($this->getUserId, $video, $caption, $keyboard, $layout, $type_keyboard);
    }

    /**
     * Метод отправки видео текущему пользователю
     *
     * @param string $video URL или файл видео.
     * @param string|null $caption Подпись к видео (необязательно).
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * 
     */
    public function sendSelfVideoInline($video, $caption = null, $keyboard = null, $layout = 2)
    {
        return $this->sendOutVideo($this->getUserId, $video, $caption, $keyboard, $layout, 1);
    }

    /**
     * Метод отправки сообщения текущему пользователю использует inlineKeyboard
     *
     * @param string|array $message Текст сообщения.
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * 
     */
    public function sendSelfInline($message, $keyboard = null, $layout = 2)
    {
        return $this->sendSelf($message, $keyboard, $layout, 1);
    }

    /**
     * Метод отправки сообщения другому пользователю использует inlineKeyboard
     *
     * @param int $id Идентификатор пользователя.
     * @param string|array $message Текст сообщения.
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * 
     */
    public function sendOutInline($id, $message, $keyboard = null, $layout = 2)
    {
        return $this->sendOut($id, $message, $keyboard, $layout, 1);
    }

    /**
     * Метод удаления сообщений в чате для другого пользователя
     *
     * @param int $chat_id Идентификатор чата.
     * @param string|array $message_id ID сообщения.
     * 
     */
    public function deleteOut($chat_id, $message_id)
    {
        return $this->deleteMessage($chat_id, $message_id);
    }

    /**
     * Метод удаления сообщений в чате для текущего пользователя
     *
     * @param string|array $message_id ID сообщения.
     * 
     */
    public function deleteSelf($message_id)
    {
        return $this->deleteOut($this->getUserId, $message_id);
    }

    /**
     * Метод редактирования сообщения другому пользователю
     *
     * @param int $chat_id Идентификатор чата.
     * @param string $message_id id сообщения
     * @param string|array $message Текст сообщения.
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * @param int $type_keyboard Тип каливатуры 1 - keyboard 2 - inlineKeyboard
     * @param string|null $parse_mode Включение HTML мода, по умолчанию включен (необязательно).
     * 
     */
    public function editOut($chat_id, $message_id, $message, $keyboard = null, $layout = 2, $type_keyboard = 0)
    {
        $keyboard = $keyboard !== null ? Services::simpleKeyboard($keyboard) : $keyboard;
        is_array($message) ? $message = Services::html($message) : $message;
        $keyboard ? $keygrid = Services::grid($keyboard, $layout) : $keyboard;
        $type_keyboard === 1 ? $type = "inlineKeyboard" : $type = "keyboard";
        return $this->editMessageText($chat_id, $message_id, $message, $keyboard ? Services::$type($keygrid) : $keyboard, "HTML");
    }

    /**
     * Метод редактирования сообщения текущему пользователю
     *
     * @param string|array $message Текст сообщения.
     * @param string $message_id id сообщения
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * @param int $type_keyboard Тип каливатуры 1 - keyboard 2 - inlineKeyboard
     * @param string|null $parse_mode Включение HTML мода, по умолчанию включен (необязательно).
     * 
     */
    public function editSelf($message_id, $message, $keyboard = null, $layout = 2, $type_keyboard = 0, $parse_mode = "HTML")
    {
        return $this->editOut($this->getUserId, $message_id, $message, $keyboard, $layout, $type_keyboard, $parse_mode);
    }

    /**
     * Метод редактирования сообщения текущему пользователю
     *
     * @param string|array $message Текст сообщения.
     * @param string $message_id id сообщения
     * @param array|null $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * 
     */
    public function editSelfInline($message_id, $message, $keyboard = null, $layout = 2)
    {
        return $this->editOut($this->getUserId, $message_id, $message, $keyboard, $layout, 1, "HTML");
    }

    /**
     * Метод редактирования разметки клавиатуры для другого пользователя
     *
     * @param int $chat_id Идентификатор чата.
     * @param string $message_id id сообщения
     * @param array $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * 
     */
    public function editReplyMarkupOut($chat_id, $message_id, $keyboard, $layout = 2)
    {
        $keyboard = Services::simpleKeyboard($keyboard);
        $keyboard ? $keygrid = Services::grid($keyboard, $layout) : $keyboard;
        return $this->editMessageReplyMarkup($chat_id, $message_id, $keyboard ? Services::inlineKeyboard($keygrid) : $keyboard);
    }

    /**
     * Метод редактирования разметки клавиатуры текущему пользователю
     *
     * @param string $message_id id сообщения
     * @param array $keyboard Клавиатура для сообщения (необязательно).
     * @param int $layout Число делений или массив с ручным расположением.
     * 
     */
    public function editReplyMarkupSelf($message_id, $keyboard = null, $layout = 2)
    {
        return $this->editReplyMarkupOut($this->getUserId, $message_id, $keyboard, $layout);
    }

    /**
     * Определяет команду для бота и выполняет соответствующий обработчик, если команда совпадает с текстом сообщения или callback.
     *
     * @param string|array $command Команда, начинающаяся с символа "/" (например, "/start") или массив команд.
     * @param Closure $callback Функция-обработчик для выполнения, если команда или callback совпадают.
     *
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function command($command, $callback)
    {
        // Приводим команду к массиву, если это строка
        $commands = is_array($command) ? $command : [$command];

        // Преобразуем команды, добавляя "/" к каждой, если необходимо
        $commands = array_map(function ($cmd) {
            return "/" . ltrim($cmd, '/');
        }, $commands);

        // Привязываем callback к текущему объекту
        $callback = $callback->bindTo($this, $this);

        // Получаем текст сообщения и данные callback
        $messageText = $this->getMessageText;

        // Проверка для текста сообщения
        foreach ($commands as $cmd) {
            if (strpos($messageText, $cmd) === 0) {
                $arguments = Services::getArguments($messageText);
                $callback($arguments); // Завершаем выполнение после нахождения совпадения
                return;
            }
        }

        return null;
    }

    /**
     * Определяет сообщение для бота и выполняет соответствующий обработчик, если сообщение совпадает с паттерном.
     *
     * @param string|array $pattern Это строка или массив строк/регулярных выражений, по которым будет искать совпадение с сообщением.
     * @param Closure $callback Функция-обработчик для выполнения, если сообщение совпадает с паттерном.
     *
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function message($pattern, $callback)
    {
        return Services::pattern($pattern, $this->getMessageText, $callback);
    }

    /**
     * Определяет сообщение от пользователя и выполняет ошибку.
     *
     * @param mixed $message Любое сообщение кроме команды.
     * @param array|null $array Данные
     * @param Closure $callback Функция-обработчик для выполнения, если команда совпадает.
     *
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function error($message, $array, $callback)
    {
        $callback = $callback->bindTo($this);

        if ($array === null) {
            if ($message === $this->getMessageText) {
                $callback();
            }
        } else {
            if (Services::findMatch($message, $array)) {
                $callback();
            }
        }
    }

    /**
     * Определяет действие для бота и выполняет соответствующий обработчик, если текст сообщения не начинается с "/".
     *
     * @param Closure $callback Функция-обработчик для выполнения, если текст сообщения не является командой.
     *
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function anyMessage($callback)
    {
        $text = $this->getMessageText;
        $callbackData = $this->getCallback;
        if (mb_substr($text, 0, 1) !== "/" && !$callbackData) {
            return $callback($text);
        }
    }

    /**
     * Обрабатывает callback-запросы.
     *
     * @param string $pattern Шаблон для сопоставления с данными callback-запроса.
     * @param Closure $callback Функция, которая будет вызвана при совпадении шаблона.
     * @param string|null $text Текст сообщения, который будет отправлен в ответ на callback-запрос (по умолчанию null).
     * @param bool $show_alert Флаг, указывающий, нужно ли показывать alert при ответе на callback-запрос (по умолчанию false).
     * @param string|null $url URL, который будет открыт при ответе на callback-запрос (по умолчанию null).
     * @param int $cache_time Время кэширования ответа на callback-запрос в секундах (по умолчанию 0).
     */
    public function callback($pattern, $callback, $text = null, $show_alert = false, $url = null, $cache_time = 0)
    {
        $callbackQuery = $this->getCallback;

        // Добавляем проверку на существование и тип переменной $cb
        if ($callbackQuery) {
            return Services::pattern($pattern, $callbackQuery->getData(), $callback, function () use ($callbackQuery, $text, $show_alert, $url, $cache_time) {
                $this->answerCallbackQuery($callbackQuery->getId(), $text, $show_alert, $url, $cache_time);
            });
        }

        return null;
    }

    // /**
    //  * Определяет обработчик для события pre-checkout.
    //  *
    //  * @param Closure $callback Функция-обработчик для выполнения, если событие pre-checkout происходит.
    //  *
    //  * @return mixed Результат выполнения функции-обработчика.
    //  */
    // public function preCheckout($callback)
    // {
    //     $preCheckoutQuery = $this->getPreCheckoutData();

    //     if ($preCheckoutQuery !== null) {
    //         $callback = $callback->bindTo($this, $this);
    //         return $callback((object) $preCheckoutQuery);
    //     }

    //     return null;
    // }

    // /**
    //  * Обрабатывает запрос pre-checkout и автоматически подтверждает его.
    //  *
    //  * @param bool $ok Указывает, следует ли подтвердить запрос pre-checkout (по умолчанию: true).
    //  * @param string|null $error_message Сообщение об ошибке в читаемом виде, объясняющее причину невозможности продолжить оформление заказа (обязательно, если ok равно False).
    //  * @return mixed Результат выполнения функции-обработчика.
    //  */
    // public function preCheckoutOk($ok = true, $error_message = null)
    // {
    //     $data = (object) $this->getPreCheckoutData();
    //     return $this->answerPreCheckoutQuery(isset($data->id) ? $data->id : null, $ok, $error_message);
    // }

    // /**
    //  * Отправляет счет самому себе.
    //  *
    //  * @param int $chat_id Идентификатор чата.
    //  * @param string $title Название счета.
    //  * @param string $description Описание счета.
    //  * @param string $payload Полезная нагрузка счета.
    //  * @param string $provider_token Токен провайдера.
    //  * @param string $start_parameter Параметр запуска.
    //  * @param string $currency Валюта счета.
    //  * @param array $prices Массив цен.
    //  * @param int|null $reply_to_message_id ID сообщения, на которое нужно ответить (необязательно).
    //  * @param bool $disable_notification Отключить уведомления (по умолчанию false).
    //  * @param string|null $photo_url URL фотографии (необязательно).
    //  * @param int|null $photo_size Размер фотографии (необязательно).
    //  * @param int|null $photo_width Ширина фотографии (необязательно).
    //  * @param int|null $photo_height Высота фотографии (необязательно).
    //  * @param bool $need_name Требуется ли имя (по умолчанию false).
    //  * @param bool $need_phone_number Требуется ли номер телефона (по умолчанию false).
    //  * @param bool $need_email Требуется ли email (по умолчанию false).
    //  * @param bool $need_shipping_address Требуется ли адрес доставки (по умолчанию false).
    //  * @param bool $send_phone_number_to_provider Отправить ли номер телефона провайдеру (по умолчанию false).
    //  * @param bool $send_email_to_provider Отправить ли email провайдеру (по умолчанию false).
    //  * @param bool $is_flexible Гибкий ли счет (по умолчанию false).
    //  *
    //  * @return mixed Результат отправки счета.
    //  */
    // public function sendInvoiceOut($chat_id, $title, $description, $payload, $provider_token, $start_parameter, $currency, $prices, $reply_to_message_id = null, $disable_notification = false, $photo_url = null, $photo_size = null, $photo_width = null, $photo_height = null, $need_name = false, $need_phone_number = false, $need_email = false, $need_shipping_address = false, $send_phone_number_to_provider = false, $send_email_to_provider = false, $is_flexible = false)
    // {
    //     return $this->sendInvoice($chat_id, $title, $description, $payload, $provider_token, $start_parameter, $currency, $prices, $reply_to_message_id, $disable_notification, $photo_url, $photo_size, $photo_width, $photo_height, $need_name, $need_phone_number, $need_email, $need_shipping_address, $send_phone_number_to_provider, $send_email_to_provider, $is_flexible);
    // }

    // /**
    //  * Отправляет счет самому себе.
    //  *
    //  * @param string $title Название счета.
    //  * @param string $description Описание счета.
    //  * @param string $payload Полезная нагрузка счета.
    //  * @param string $provider_token Токен провайдера.
    //  * @param string $currency Валюта счета.
    //  * @param array $prices Массив цен.
    //  * @param int|null $max_tip_amount Максимально допустимая сумма чаевых в наименьших единицах валюты (целое число, не float/double). По умолчанию 0.
    //  * @param array|null $suggested_tip_amounts JSON-сериализованный массив предложенных сумм чаевых в наименьших единицах валюты (целое число, не float/double). Максимум 4 предложенные суммы чаевых.
    //  * @param string|null $start_parameter Параметр запуска.
    //  * @param string|null $provider_data JSON-сериализованные данные о счете, которые будут переданы провайдеру платежей.
    //  * @param string|null $photo_url URL фотографии продукта для счета.
    //  * @param int|null $photo_size Размер фотографии в байтах.
    //  * @param int|null $photo_width Ширина фотографии.
    //  * @param int|null $photo_height Высота фотографии.
    //  * @param bool $need_name Требуется ли имя.
    //  * @param bool $need_phone_number Требуется ли номер телефона.
    //  * @param bool $need_email Требуется ли email.
    //  * @param bool $need_shipping_address Требуется ли адрес доставки.
    //  * @param bool $send_phone_number_to_provider Отправить ли номер телефона провайдеру.
    //  * @param bool $send_email_to_provider Отправить ли email провайдеру.
    //  * @param bool $is_flexible Гибкий ли счет.
    //  * @param bool $disable_notification Отключить уведомления.
    //  * @param bool $protect_content Защитить содержимое отправленного сообщения от пересылки и сохранения.
    //  * @param string|null $message_effect_id Уникальный идентификатор эффекта сообщения, который будет добавлен к сообщению; только для личных чатов.
    //  * @param array|null $reply_parameters Описание сообщения, на которое нужно ответить.
    //  * @param array|null $reply_markup JSON-сериализованный объект для встроенной клавиатуры.
    //  *
    //  * @return \Illuminate\Http\Client\Response|null Ответ от Telegram API.
    //  */
    // public function sendInvoiceSelf($title, $description, $payload, $provider_token, $currency, $prices, $max_tip_amount = null, $suggested_tip_amounts = null, $start_parameter = null, $provider_data = null, $photo_url = null, $photo_size = null, $photo_width = null, $photo_height = null, $need_name = false, $need_phone_number = false, $need_email = false, $need_shipping_address = false, $send_phone_number_to_provider = false, $send_email_to_provider = false, $is_flexible = false, $disable_notification = false, $protect_content = false, $message_effect_id = null, $reply_parameters = null, $reply_markup = null)
    // {
    //     return $this->sendInvoiceOut($this->getUserId(), $title, $description, $payload, $provider_token, $currency, $prices, $max_tip_amount, $suggested_tip_amounts, $start_parameter, $provider_data, $photo_url, $photo_size, $photo_width, $photo_height, $need_name, $need_phone_number, $need_email, $need_shipping_address, $send_phone_number_to_provider, $send_email_to_provider, $is_flexible, $disable_notification, $protect_content, $message_effect_id, $reply_parameters, $reply_markup);
    // }
}
