<?php

namespace Teg\Storage;

use App\Models\Message;
use Teg\Storage\Interface\Messages;

/**
 * Класс для работы с сообщениями через базы данных
 */
class MessagesSQL implements Messages
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
        $messageText = $this->bot->getMessageText();


        $cb = $this->bot->getCallbackData();

        // Проверяем, что это не callback или сообщение не пусто
        if ($cb || empty($messageText)) {
            return null;
        }

        $patterns = is_array($pattern) ? $pattern : [$pattern];
        $message = Message::where('tg_id', $this->bot->getUserId())->first();

        if ($message && $message->clue) {
            foreach ($patterns as $singlePattern) {
                // Проверяем, является ли паттерн регулярным выражением
                $isRegex = preg_match('/^\/.*\/[a-z]*$/i', $singlePattern);

                // Если это не регулярное выражение, преобразуем паттерн с параметрами в регулярное выражение
                if (!$isRegex) {
                    $singlePattern = str_replace(['{', '}'], ['(?P<', '>[^}]+)'], $singlePattern);
                    $singlePattern = "/^" . str_replace('/', '\/', $singlePattern) . "$/";
                }

                if (preg_match($singlePattern, $message->clue, $matches)) {
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
        $messageText = $this->bot->getMessageText();


        $cb = $this->bot->getCallbackData();

        // Проверяем, что это не callback или сообщение не пусто
        if ($cb || empty($messageText)) {
            return null;
        }

        $patterns = is_array($pattern) ? $pattern : [$pattern];
        $message = Message::where('tg_id', $this->bot->getUserId())->first();

        if ($message && $message->payload) {
            foreach ($patterns as $singlePattern) {
                // Проверяем, является ли паттерн регулярным выражением
                $isRegex = preg_match('/^\/.*\/[a-z]*$/i', $singlePattern);

                // Если это не регулярное выражение, преобразуем паттерн с параметрами в регулярное выражение
                if (!$isRegex) {
                    $singlePattern = str_replace(['{', '}'], ['(?P<', '>[^}]+)'], $singlePattern);
                    $singlePattern = "/^" . str_replace('/', '\/', $singlePattern) . "$/";
                }


                if (preg_match($singlePattern, $message->payload, $matches)) {
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
     * Метод для очистки значений payload и clue последнего сообщения
     *
     * @return void
     */
    public function delete(): void
    {
        $message = Message::where('tg_id', $this->bot->getUserId())->first();

        if ($message) {
            $message->payload = '';
            $message->clue = '';
            $message->save();
        }
    }

    /**
     * Метод для очистки значений payload и clue последнего сообщения
     *
     * @return void
     */
    public function deletePayload(): void
    {
        $message = Message::where('tg_id', $this->bot->getUserId())->first();
        $message->payload = '';
        $message->save();
    }

    /**
     * Метод для очистки значений payload и clue последнего сообщения
     *
     * @return void
     */
    public function deleteClue(): void
    {
        $message = Message::where('tg_id', $this->bot->getUserId())->first();
        $message->clue = '';
        $message->save();
    }

    /**
     * Получает сообщение для текущего пользователя бота.
     *
     * @param mixed|null $input Входное значение для проверки существования сообщения.
     * @return Message|null|bool Возвращает первое сообщение, соответствующее идентификатору пользователя Telegram, или null, если сообщение не найдено. Если $input не null, возвращает булево значение существования сообщения.
     */
    public function getMessage($input = null)
    {
        if ($input !== null) {
            return Message::where('tg_id', $this->bot->getUserId())->where('clue', $input)->exists();
        }

        return Message::where('tg_id', $this->bot->getUserId())->first();
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
        $message = Message::where('tg_id', $this->bot->getUserId())->first();

        if (! $message) {
            $message = new Message;
            $message->tg_id = $this->bot->getUserId();
            $message->clue = $clue;
            $message->payload = $payload;
            $message->save();
        } else {
            $message->clue = $clue;
            $message->payload = $payload;
            $message->save();
        }
    }

    /**
     * Метод для получения значения payload последнего сообщения
     *
     * @return mixed|null Значение payload или null, если сообщение не найдено
     */
    public function getPayload()
    {
        $message = Message::where('tg_id', $this->bot->getUserId())->first();
        return $message ? $message->payload : null;
    }

    /**
     * Метод для установки значения payload последнего сообщения
     *
     * @param mixed $payload Значение payload
     * @return void
     */
    public function setPayload($payload): void
    {
        $message = Message::where('tg_id', $this->bot->getUserId())->first();

        if ($message) {
            $message->payload = $payload;
            $message->save();
        }
    }

    /**
     * Метод для получения значения подсказки последнего сообщения
     *
     * @return string|null Значение подсказки или null, если сообщение не найдено
     */
    public function getClue(): ?string
    {
        $message = Message::where('tg_id', $this->bot->getUserId())->first();
        return $message ? $message->clue : null;
    }

    /**
     * Метод для установки значения подсказки последнего сообщения
     *
     * @param string $clue Значение подсказки
     * @return void
     */
    public function setClue(string $clue): void
    {
        $message = Message::where('tg_id', $this->bot->getUserId())->first();

        if ($message) {
            $message->clue = $clue;
            $message->save();
        }
    }
}
