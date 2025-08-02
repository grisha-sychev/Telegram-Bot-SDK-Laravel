<?php

namespace Bot\Modules;

use Bot\Storage\MessagesRedis;
use Bot\Storage\MessagesSQL;
use Bot\Modules\Enum\Connect;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Модуль управления состоянием сообщений в боте
 */

trait StateModule
{
    private $state;
    public Connect $typeConnect;
    public bool $logStateActions = true;
    public bool $enableStateValidation = true;

    public function stateModule()
    {
        try {
            $this->typeConnect = Connect::Sql;

            switch ($this->typeConnect) {
                case Connect::Redis:
                    $this->state = new MessagesRedis($this);
                    break;
                case Connect::Sql:
                    $this->state = new MessagesSQL($this);
                    break;
            }

            if ($this->logStateActions) {
                Log::debug('StateModule initialized', [
                    'connection_type' => $this->typeConnect->name,
                    'user_id' => $this->getUserId() ?? 'unknown'
                ]);
            }
        } catch (Exception $e) {
            $this->logError('StateModule initialization failed', $e);
            throw $e;
        }
    }

    /**
     * Метод для получения значения последнего сообщения и выполнения callback
     *
     * @param string|array $pattern Шаблон сообщения или массив шаблонов
     * @param \Closure $callback Функция обратного вызова
     * 
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function clue($pattern, $callback): mixed
    {
        try {
            if ($this->enableStateValidation && !$this->validatePattern($pattern)) {
                throw new Exception('Invalid pattern provided to clue method');
            }

            $result = $this->state->clue($pattern, $callback);
            
            if ($this->logStateActions) {
                Log::debug('Clue processed', [
                    'pattern' => $pattern,
                    'user_id' => $this->getUserId(),
                    'has_result' => !is_null($result)
                ]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logError('Clue processing failed', $e, ['pattern' => $pattern]);
            throw $e;
        }
    }

    /**
     * Метод для получения значения последнего сообщения и выполнения callback
     *
     * @param string|array $pattern Шаблон сообщения или массив шаблонов
     * @param \Closure $callback Функция обратного вызова
     * 
     * @return mixed Результат выполнения функции-обработчика.
     */
    public function payload($pattern, $callback): mixed
    {
        try {
            if ($this->enableStateValidation && !$this->validatePattern($pattern)) {
                throw new Exception('Invalid pattern provided to payload method');
            }

            $result = $this->state->payload($pattern, $callback);
            
            if ($this->logStateActions) {
                Log::debug('Payload processed', [
                    'pattern' => $pattern,
                    'user_id' => $this->getUserId(),
                    'has_result' => !is_null($result)
                ]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logError('Payload processing failed', $e, ['pattern' => $pattern]);
            throw $e;
        }
    }

    /**
     * Метод для очистки значений payload и clue последнего сообщения
     *
     * @return void
     */
    public function deleteState(): void
    {
        try {
            $this->state->delete();
            
            if ($this->logStateActions) {
                Log::debug('State deleted', ['user_id' => $this->getUserId()]);
            }
        } catch (Exception $e) {
            $this->logError('State deletion failed', $e);
            throw $e;
        }
    }

    /**
     * Метод для очистки значений payload последнего сообщения
     *
     * @return void
     */
    public function deletePayload(): void
    {
        try {
            $this->state->deletePayload();
            
            if ($this->logStateActions) {
                Log::debug('Payload deleted', ['user_id' => $this->getUserId()]);
            }
        } catch (Exception $e) {
            $this->logError('Payload deletion failed', $e);
            throw $e;
        }
    }

    /**
     * Метод для очистки значений clue последнего сообщения
     *
     * @return void
     */
    public function deleteClue(): void
    {
        try {
            $this->state->deleteClue();
            
            if ($this->logStateActions) {
                Log::debug('Clue deleted', ['user_id' => $this->getUserId()]);
            }
        } catch (Exception $e) {
            $this->logError('Clue deletion failed', $e);
            throw $e;
        }
    }

    /**
     * Получает сообщение для текущего пользователя бота.
     *
     * @param mixed|null $input Входное значение для проверки существования сообщения.
     * @return mixed Возвращает первое сообщение, соответствующее идентификатору пользователя Telegram, или null, если сообщение не найдено. Если $input не null, возвращает булево значение существования сообщения.
     */
    public function getState($input = null): mixed
    {
        try {
            return $this->state->getMessage($input);
        } catch (Exception $e) {
            $this->logError('Get state failed', $e);
            return null;
        }
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
        try {
            if ($this->enableStateValidation) {
                $this->validateClue($clue);
                $this->validatePayload($payload);
            }

            $this->state->setMessage($clue, $payload);
            
            if ($this->logStateActions) {
                Log::debug('State set', [
                    'user_id' => $this->getUserId(),
                    'clue' => $clue,
                    'has_payload' => !is_null($payload)
                ]);
            }
        } catch (Exception $e) {
            $this->logError('Set state failed', $e, ['clue' => $clue]);
            throw $e;
        }
    }

    /**
     * Метод для получения значения payload последнего сообщения
     *
     * @return mixed|null Значение payload или null, если сообщение не найдено
     */
    public function getPayload(): mixed
    {
        try {
            return $this->state->getPayload();
        } catch (Exception $e) {
            $this->logError('Get payload failed', $e);
            return null;
        }
    }

    /**
     * Метод для установки значения payload последнего сообщения
     *
     * @param mixed $payload Значение payload
     * @return void
     */
    public function setPayload($payload): void
    {
        try {
            if ($this->enableStateValidation) {
                $this->validatePayload($payload);
            }

            $this->state->setPayload($payload);
            
            if ($this->logStateActions) {
                Log::debug('Payload set', [
                    'user_id' => $this->getUserId(),
                    'payload_type' => gettype($payload)
                ]);
            }
        } catch (Exception $e) {
            $this->logError('Set payload failed', $e);
            throw $e;
        }
    }

    /**
     * Метод для получения значения подсказки последнего сообщения
     *
     * @return string|null Значение подсказки или null, если сообщение не найдено
     */
    public function getClue(): ?string
    {
        try {
            return $this->state->getClue();
        } catch (Exception $e) {
            $this->logError('Get clue failed', $e);
            return null;
        }
    }

    /**
     * Метод для установки значения подсказки последнего сообщения
     *
     * @param string $clue Значение подсказки
     * @return void
     */
    public function setClue(string $clue): void
    {
        try {
            if ($this->enableStateValidation) {
                $this->validateClue($clue);
            }

            $this->state->setClue($clue);
            
            if ($this->logStateActions) {
                Log::debug('Clue set', [
                    'user_id' => $this->getUserId(),
                    'clue' => $clue
                ]);
            }
        } catch (Exception $e) {
            $this->logError('Set clue failed', $e, ['clue' => $clue]);
            throw $e;
        }
    }

    /**
     * Simplified state methods using Message model directly
     */

    /**
     * Set state using Message model
     */
    public function setStateSimple(string $clue, array $payload = null): bool
    {
        try {
            $telegramId = $this->getUserId();
            if (!$telegramId) {
                return false;
            }

            Message::setClueForUser($telegramId, $clue, $payload);
            
            if ($this->logStateActions) {
                Log::debug('Simple state set', [
                    'user_id' => $telegramId,
                    'clue' => $clue
                ]);
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Set simple state failed', $e);
            return false;
        }
    }

    /**
     * Get clue using Message model
     */
    public function getClueSimple(): ?string
    {
        try {
            $telegramId = $this->getUserId();
            if (!$telegramId) {
                return null;
            }

            return Message::getClueForUser($telegramId);
        } catch (Exception $e) {
            $this->logError('Get simple clue failed', $e);
            return null;
        }
    }

    /**
     * Get payload using Message model
     */
    public function getPayloadSimple(): ?array
    {
        try {
            $telegramId = $this->getUserId();
            if (!$telegramId) {
                return null;
            }

            return Message::getPayloadForUser($telegramId);
        } catch (Exception $e) {
            $this->logError('Get simple payload failed', $e);
            return null;
        }
    }

    /**
     * Clear state using Message model
     */
    public function clearStateSimple(): bool
    {
        try {
            $telegramId = $this->getUserId();
            if (!$telegramId) {
                return false;
            }

            Message::clearStateForUser($telegramId);
            
            if ($this->logStateActions) {
                Log::debug('Simple state cleared', ['user_id' => $telegramId]);
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Clear simple state failed', $e);
            return false;
        }
    }

    /**
     * Check if user has specific clue
     */
    public function hasClue(string $clue): bool
    {
        try {
            $telegramId = $this->getUserId();
            if (!$telegramId) {
                return false;
            }

            return Message::userHasClue($telegramId, $clue);
        } catch (Exception $e) {
            $this->logError('Check clue failed', $e);
            return false;
        }
    }

    /**
     * Check if user has any clue from array
     */
    public function hasAnyClue(array $clues): bool
    {
        try {
            $telegramId = $this->getUserId();
            if (!$telegramId) {
                return false;
            }

            return Message::userHasAnyClue($telegramId, $clues);
        } catch (Exception $e) {
            $this->logError('Check any clue failed', $e);
            return false;
        }
    }

    /**
     * Validation methods
     */

    private function validatePattern($pattern): bool
    {
        if (is_string($pattern)) {
            return !empty(trim($pattern)) && strlen($pattern) <= 1000;
        }
        
        if (is_array($pattern)) {
            return !empty($pattern) && count($pattern) <= 100;
        }
        
        return false;
    }

    private function validateClue(string $clue): void
    {
        if (empty(trim($clue))) {
            throw new Exception('Clue cannot be empty');
        }
        
        if (strlen($clue) > 255) {
            throw new Exception('Clue is too long (max 255 characters)');
        }
    }

    private function validatePayload($payload): void
    {
        if (is_string($payload) && strlen($payload) > 65535) {
            throw new Exception('Payload string is too long');
        }
        
        if (is_array($payload) && json_encode($payload) === false) {
            throw new Exception('Payload array cannot be serialized to JSON');
        }
    }

    private function logError(string $message, Exception $e, array $context = []): void
    {
        if ($this->logStateActions) {
            Log::error($message, array_merge([
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId() ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $context));
        }
    }
}
