<?php

namespace Teg\Storage\Interface;

interface Messages
{

    /**
     * Метод для получения значения последнего сообщения payload и выполнения callback
     *
     * @param string|array $pattern Шаблон сообщения или массив шаблонов
     * @param Closure $callback Функция обратного вызова
     * 
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function payload($pattern, $callback): mixed;

    /**
     * Метод для получения значения последнего сообщения clue и выполнения callback
     *
     * @param string|array $pattern Шаблон сообщения или массив шаблонов
     * @param Closure $callback Функция обратного вызова
     * 
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function clue($pattern, $callback): mixed;

    /**
     * Метод для удаления всех значений
     */
    public function delete(): void;
    public function deletePayload(): void;
    public function deleteClue(): void;

    /**
     * Получает сообщение для текущего пользователя бота.
     *
     * @return mixed Возвращает первое сообщение, соответствующее идентификатору пользователя Telegram, или null, если сообщение не найдено.
     */
    public function getMessage();

    /**
     * Метод для установки значения сообщения
     *
     * @param string $clue Значение подсказки сообщения
     * @param mixed|null $payload Дополнительные данные сообщения
     * @return void
     */
    public function setMessage($clue, $payload = null): void;

    /**
     * Метод для получения значения payload последнего сообщения
     *
     * @return mixed|null Значение payload или null, если сообщение не найдено
     */
    public function getPayload();

    /**
     * Метод для установки значения payload последнего сообщения
     *
     * @param mixed $payload Значение payload
     * @return void
     */
    public function setPayload($payload): void;

    /**
     * Метод для получения значения подсказки последнего сообщения
     *
     * @return string|null Значение подсказки или null, если сообщение не найдено
     */
    public function getClue(): ?string;

    /**
     * Метод для установки значения подсказки последнего сообщения
     *
     * @param string $clue Значение подсказки
     * @return void
     */
    public function setClue(string $clue): void;
}
