<?php

namespace Teg\Storage;

use Illuminate\Support\Facades\Cache;
use Teg\Storage\Interface\Messages;

/**
 * Класс для работы с сообщениями через Cache
 */
class MessagesCache implements Messages
{
    /**
     * Экземпляр бота
     *
     * @var mixed
     */
    private $bot;

    /**
     * Конструктор класса
     *
     * @param \Tgb\Base\Bot $bot Экземпляр бота
     */
    public function __construct($bot)
    {
        $this->bot = $bot;
    }

    /**
     * Метод для получения значения последнего сообщения и выполнения callback
     *
     * @param string|array $pattern Шаблон сообщения или массив шаблонов
     * @param Closure $callback Функция обратного вызова
     * 
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function clue($pattern, $callback): mixed
    {
        $messageText = $this->bot->getMessageText;
        
        $cb = $this->bot->getCallback;

        // Проверяем, что это не callback и сообщение не пусто
        if (!empty($cb) && empty($messageText)) {
            return null;
        }

        $patterns = is_array($pattern) ? $pattern : [$pattern];
        $message = Cache::get('message_' . $this->bot->getUserId);

        if ($message && $message['clue']) {
            foreach ($patterns as $singlePattern) {
                // Проверяем, является ли паттерн регулярным выражением
                $isRegex = preg_match('/^\/.*\/[a-z]*$/i', $singlePattern);

                // Если это не регулярное выражение, преобразуем паттерн с параметрами в регулярное выражение
                if (!$isRegex) {
                    $singlePattern = str_replace(['{', '}'], ['(?P<', '>[^}]+)'], $singlePattern);
                    $singlePattern = "/^" . str_replace('/', '\/', $singlePattern) . "$/";
                }

                if (preg_match($singlePattern, $message['clue'], $matches)) {
                    // Извлекаем только значения параметров из совпавших данных и передаем их в функцию-обработчик
                    $parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                    // Вызываем функцию-обработчик с параметрами
                    $callback(...$parameters);
                    exit; // Завершаем выполнение скрипта после выполнения callback
                }
            }
        }
        return null;
    }

    /**
     * Метод для получения значения последнего сообщения и выполнения callback
     *
     * @param string|array $pattern Шаблон сообщения или массив шаблонов
     * @param Closure $callback Функция обратного вызова
     * 
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function payload($pattern, $callback): mixed
    {
        $messageText = $this->bot->getMessageText;

        $cb = $this->bot->getCallback;

        // Проверяем, что это не callback и сообщение не пусто
        if (!empty($cb) && empty($messageText)) {
            return null;
        }

        $patterns = is_array($pattern) ? $pattern : [$pattern];
        $message = Cache::get('message_' . $this->bot->getUserId);

        if ($message && $message['payload']) {
            foreach ($patterns as $singlePattern) {
                // Проверяем, является ли паттерн регулярным выражением
                $isRegex = preg_match('/^\/.*\/[a-z]*$/i', $singlePattern);

                // Если это не регулярное выражение, преобразуем паттерн с параметрами в регулярное выражение
                if (!$isRegex) {
                    $singlePattern = str_replace(['{', '}'], ['(?P<', '>[^}]+)'], $singlePattern);
                    $singlePattern = "/^" . str_replace('/', '\/', $singlePattern) . "$/";
                }

                if (preg_match($singlePattern, $message['payload'], $matches)) {
                    // Извлекаем только значения параметров из совпавших данных и передаем их в функцию-обработчик
                    $parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                    // Вызываем функцию-обработчик с параметрами
                    $callback(...$parameters);
                    exit; // Завершаем выполнение скрипта после выполнения callback
                }
            }
        }
        return null;
    }


    public function delete(): void
    {
        Cache::forget('message_' . $this->bot->getUserId);
    }

    public function deleteClue(): void
    {
        $message = Cache::get('message_' . $this->bot->getUserId);
        if ($message) {
            unset($message['clue']);
            Cache::put('message_' . $this->bot->getUserId, $message);
        }
    }

    public function deletePayload(): void
    {
        $message = Cache::get('message_' . $this->bot->getUserId);
        if ($message) {
            unset($message['payload']);
            Cache::put('message_' . $this->bot->getUserId, $message);
        }
    }

    /**
     * Получает сообщение для текущего пользователя бота.
     *
     * @return Cache|null Возвращает первое сообщение, соответствующее идентификатору пользователя Telegram, или null, если сообщение не найдено.
     */
    public function getMessage()
    {
        return Cache::get('message_' . $this->bot->getUserId);
    }

    /**
     * Метод для установки значения сообщения
     *
     * @param string $clue Значение подсказки сообщения
     * @param mixed|null $payload Дополнительные данные сообщения
     * @return void
     */
    public function setMessage($clue, $payload = null): void
    {
        $message = Cache::get('message_' . $this->bot->getUserId);

        if (!$message) {
            $message = [
                'tg_id' => $this->bot->getUserId,
                'clue' => $clue,
                'payload' => $payload
            ];
        } else {
            $message['clue'] = $clue;
            $message['payload'] = $payload;
        }

        Cache::put('message_' . $this->bot->getUserId, $message);
    }

    /**
     * Метод для получения значения payload последнего сообщения
     *
     * @return mixed|null Значение payload или null, если сообщение не найдено
     */
    public function getPayload()
    {
        $message = Cache::get('message_' . $this->bot->getUserId);
        return $message ? $message['payload'] : null;
    }

    /**
     * Метод для установки значения payload последнего сообщения
     *
     * @param mixed $payload Значение payload
     * @return void
     */
    public function setPayload($payload): void
    {
        $message = Cache::get('message_' . $this->bot->getUserId);

        if ($message) {
            $message['payload'] = $payload;
            Cache::put('message_' . $this->bot->getUserId, $message);
        }
    }

    /**
     * Метод для получения значения подсказки последнего сообщения
     *
     * @return string|null Значение подсказки или null, если сообщение не найдено
     */
    public function getClue(): ?string
    {
        $message = Cache::get('message_' . $this->bot->getUserId);
        return $message ? $message['clue'] : null;
    }

    /**
     * Метод для установки значения подсказки последнего сообщения
     *
     * @param string $clue Значение подсказки
     * @return void
     */
    public function setClue(string $clue): void
    {
        $message = Cache::get('message_' . $this->bot->getUserId);

        if ($message) {
            $message['clue'] = $clue;
            Cache::put('message_' . $this->bot->getUserId, $message);
        }
    }
}
