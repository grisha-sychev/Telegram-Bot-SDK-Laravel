<?php

namespace Teg\Modules;

use Teg\Storage\MessagesRedis;
use Teg\Storage\MessagesSQL;
use Teg\Modules\Enum\Connect;

/**
 * Модуль управления состоянием сообщений в боте
 */

trait StateModule
{
    private $state;
    public Connect $typeConnect;

    public function stateModule()
    {
        $this->typeConnect = Connect::Sql;

        switch ($this->typeConnect) {
            case Connect::Redis:
                $this->state = new MessagesRedis($this);
                break;
            case Connect::Sql:
                $this->state = new MessagesSQL($this);
                break;
        }
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
        return $this->state->clue($pattern, $callback);
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
        return $this->state->payload($pattern, $callback);
    }

    /**
     * Метод для очистки значений payload и clue последнего сообщения
     *
     * @return void
     */
    public function deleteState(): void
    {
        $this->state->delete();
    }

    /**
     * Метод для очистки значений payload и clue последнего сообщения
     *
     * @return void
     */
    public function deletePayload(): void
    {
        $this->state->deletePayload();
    }

    /**
     * Метод для очистки значений payload и clue последнего сообщения
     *
     * @return void
     */
    public function deleteClue(): void
    {
        $this->state->deleteClue();
    }

    /**
     * Получает сообщение для текущего пользователя бота.
     *
     * @param mixed|null $input Входное значение для проверки существования сообщения.
     * @return Message|null|bool Возвращает первое сообщение, соответствующее идентификатору пользователя Telegram, или null, если сообщение не найдено. Если $input не null, возвращает булево значение существования сообщения.
     */
    public function getState($input = null)
    {
        return $this->state->getMessage($input);
    }

    /**
     * Метод для установки значения сообщения
     *
     * @param string $clue Значение подсказки сообщения
     * @param mixed|null $payload Дополнительные данные сообщения
     * @return void
     */
    public function setState($clue, $payload = null): void
    {
        $this->state->setMessage($clue, $payload);
    }

    /**
     * Метод для получения значения payload последнего сообщения
     *
     * @return mixed|null Значение payload или null, если сообщение не найдено
     */
    public function getPayload()
    {
        $this->state->getPayload();
    }


    /**
     * Метод для установки значения payload последнего сообщения
     *
     * @param mixed $payload Значение payload
     * @return void
     */
    public function setPayload($payload): void
    {
        $this->state->setPayload($payload);
    }

    /**
     * Метод для получения значения подсказки последнего сообщения
     *
     * @return string|null Значение подсказки или null, если сообщение не найдено
     */
    public function getClue(): ?string
    {
        return $this->state->getClue();
    }

    /**
     * Метод для установки значения подсказки последнего сообщения
     *
     * @param string $clue Значение подсказки
     * @return void
     */
    public function setClue(string $clue): void
    {
        $this->state->setClue($clue);
    }
}
