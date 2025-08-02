<?php

namespace Bot\Modules;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * FSM (Finite State Machine) модуль для управления состояниями ботов
 */
trait FSMStateModule
{
    /**
     * FSM движок
     */
    private ?FSMEngine $fsm = null;
    
    /**
     * Настройки FSM
     */
    private array $fsmConfig = [
        'cache_ttl' => 3600, // 1 час
        'cache_prefix' => 'bot_fsm_',
        'auto_save' => true,
        'debug' => false,
        'log_actions' => true,
    ];

    /**
     * Инициализация FSM модуля
     */
    public function stateModule(): void
    {
        try {
            if (!$this->fsm) {
                $this->fsm = new FSMEngine($this->fsmConfig);
                
                if ($this->fsmConfig['log_actions']) {
                    Log::debug('FSM Module initialized', [
                        'user_id' => $this->getUserId() ?? 'unknown',
                        'config' => $this->fsmConfig
                    ]);
                }
            }
        } catch (Exception $e) {
            $this->logFSMError('FSM Module initialization failed', $e);
            throw $e;
        }
    }

    /**
     * Обработка входящих сообщений через FSM
     */
    public function processWithFSM($input): void
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return;
            }
            
            $this->stateModule();
            
            // Сохраняем входные данные в контекст
            $context = $this->fsm->getContext($userId);
            $context['last_input'] = $input;
            $context['timestamp'] = now()->toISOString();
            $this->fsm->updateContext($userId, $context);
            
            // Обрабатываем через FSM
            $newState = $this->fsm->process($userId, $input, $context);
            
            // Если это первое сообщение, устанавливаем начальное состояние
            if (!$newState) {
                $this->fsm->setState($userId, 'idle');
                $this->fsm->process($userId, $input, $context);
            }
            
        } catch (Exception $e) {
            $this->logFSMError('FSM processing failed', $e, ['input' => $input]);
        }
    }

    /**
     * Устанавливает состояние пользователя
     */
    public function setFSMState(string $state, array $context = []): void
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                $this->fsm->setState($userId, $state, $context);
            }
        } catch (Exception $e) {
            $this->logFSMError('Set FSM state failed', $e, ['state' => $state]);
        }
    }

    /**
     * Получает текущее состояние пользователя
     */
    public function getFSMState(): ?string
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                return $this->fsm->getCurrentState($userId);
            }
            return null;
        } catch (Exception $e) {
            $this->logFSMError('Get FSM state failed', $e);
            return null;
        }
    }

    /**
     * Проверяет, находится ли пользователь в определенном состоянии
     */
    public function isInFSMState(string $state): bool
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                return $this->fsm->isInState($userId, $state);
            }
            return false;
        } catch (Exception $e) {
            $this->logFSMError('Check FSM state failed', $e, ['state' => $state]);
            return false;
        }
    }

    /**
     * Очищает состояние пользователя
     */
    public function clearFSMState(): void
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                $this->fsm->clearState($userId);
            }
        } catch (Exception $e) {
            $this->logFSMError('Clear FSM state failed', $e);
        }
    }

    /**
     * Получает контекст пользователя
     */
    public function getFSMContext(): array
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                return $this->fsm->getContext($userId);
            }
            return [];
        } catch (Exception $e) {
            $this->logFSMError('Get FSM context failed', $e);
            return [];
        }
    }

    /**
     * Обновляет контекст пользователя
     */
    public function updateFSMContext(array $context): void
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                $this->fsm->updateContext($userId, $context);
            }
        } catch (Exception $e) {
            $this->logFSMError('Update FSM context failed', $e, ['context' => $context]);
        }
    }

    /**
     * Получает данные из контекста
     */
    public function getFSMData(string $key, $default = null)
    {
        $context = $this->getFSMContext();
        return $context[$key] ?? $default;
    }

    /**
     * Сохраняет данные в контекст
     */
    public function setFSMData(string $key, $value): void
    {
        $context = $this->getFSMContext();
        $context[$key] = $value;
        $this->updateFSMContext($context);
    }

    /**
     * Проверяет наличие данных в контексте
     */
    public function hasFSMData(string $key): bool
    {
        $context = $this->getFSMContext();
        return isset($context[$key]);
    }

    /**
     * Удаляет данные из контекста
     */
    public function removeFSMData(string $key): void
    {
        $context = $this->getFSMContext();
        unset($context[$key]);
        $this->updateFSMContext($context);
    }

    /**
     * Получает все возможные переходы из текущего состояния
     */
    public function getFSMTransitions(): array
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                return $this->fsm->getPossibleTransitions($userId);
            }
            return [];
        } catch (Exception $e) {
            $this->logFSMError('Get FSM transitions failed', $e);
            return [];
        }
    }

    /**
     * Выполняет событие
     */
    public function triggerFSMEvent(string $event, array $data = []): void
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                $this->fsm->triggerEvent($userId, $event, $data);
            }
        } catch (Exception $e) {
            $this->logFSMError('Trigger FSM event failed', $e, ['event' => $event]);
        }
    }

    /**
     * Экспортирует состояние FSM для отладки
     */
    public function exportFSMState(): array
    {
        try {
            $userId = $this->getUserId();
            if ($userId) {
                $this->stateModule();
                return $this->fsm->exportState();
            }
            return [];
        } catch (Exception $e) {
            $this->logFSMError('Export FSM state failed', $e);
            return [];
        }
    }

    /**
     * Логирование ошибок FSM
     */
    private function logFSMError(string $message, Exception $e, array $context = []): void
    {
        if ($this->fsmConfig['log_actions']) {
            Log::error($message, array_merge([
                'error' => $e->getMessage(),
                'user_id' => $this->getUserId() ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'module' => 'FSM',
            ], $context));
        }
    }
}

/**
 * FSM движок
 */
class FSMEngine
{
    /**
     * Состояния и их обработчики
     */
    private array $states = [];
    
    /**
     * Переходы между состояниями
     */
    private array $transitions = [];
    
    /**
     * Глобальные обработчики событий
     */
    private array $eventHandlers = [];
    
    /**
     * Настройки FSM
     */
    private array $config;

    /**
     * Конструктор с настройками
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'cache_ttl' => 3600,
            'cache_prefix' => 'bot_fsm_',
            'auto_save' => true,
            'debug' => false,
        ], $config);
    }

    /**
     * Добавляет состояние с обработчиком
     */
    public function addState(string $name, callable $handler, array $options = []): self
    {
        $this->states[$name] = [
            'handler' => $handler,
            'options' => $options,
            'name' => $name,
        ];
        
        if ($this->config['debug']) {
            Log::info("FSM: Added state '{$name}'");
        }
        
        return $this;
    }

    /**
     * Добавляет переход между состояниями
     */
    public function addTransition(string $from, string $to, callable $condition, array $options = []): self
    {
        $this->transitions[] = [
            'from' => $from,
            'to' => $to,
            'condition' => $condition,
            'options' => $options,
        ];
        
        if ($this->config['debug']) {
            Log::info("FSM: Added transition from '{$from}' to '{$to}'");
        }
        
        return $this;
    }

    /**
     * Добавляет глобальный обработчик события
     */
    public function onEvent(string $event, callable $handler): self
    {
        $this->eventHandlers[$event] = $handler;
        return $this;
    }

    /**
     * Обрабатывает входные данные и выполняет переходы
     */
    public function process(int $userId, $input, array $context = []): ?string
    {
        $currentState = $this->getCurrentState($userId);
        
        if (!$currentState) {
            return null;
        }

        // Проверяем переходы
        foreach ($this->transitions as $transition) {
            if ($transition['from'] === $currentState || $transition['from'] === '*') {
                $condition = $transition['condition'];
                
                if ($condition($input, $context)) {
                    $newState = $transition['to'];
                    $this->setState($userId, $newState, $context);
                    
                    if ($this->config['debug']) {
                        Log::info("FSM: User {$userId} transitioned from '{$currentState}' to '{$newState}'");
                    }
                    
                    // Выполняем обработчик нового состояния
                    $this->executeState($userId, $newState, $input, $context);
                    
                    return $newState;
                }
            }
        }

        // Если переход не найден, выполняем обработчик текущего состояния
        $this->executeState($userId, $currentState, $input, $context);
        
        return $currentState;
    }

    /**
     * Выполняет обработчик состояния
     */
    private function executeState(int $userId, string $stateName, $input, array $context = []): void
    {
        if (!isset($this->states[$stateName])) {
            Log::warning("FSM: State '{$stateName}' not found for user {$userId}");
            return;
        }

        $state = $this->states[$stateName];
        $handler = $state['handler'];
        
        try {
            $handler($input, $context, $userId);
        } catch (\Exception $e) {
            Log::error("FSM: Error executing state '{$stateName}' for user {$userId}: " . $e->getMessage());
        }
    }

    /**
     * Устанавливает состояние пользователя
     */
    public function setState(int $userId, string $state, array $context = []): void
    {
        $data = [
            'state' => $state,
            'context' => $context,
            'updated_at' => now()->toISOString(),
        ];
        
        $key = $this->getCacheKey($userId);
        Cache::put($key, $data, $this->config['cache_ttl']);
        
        if ($this->config['debug']) {
            Log::info("FSM: Set state '{$state}' for user {$userId}");
        }
    }

    /**
     * Получает текущее состояние пользователя
     */
    public function getCurrentState(int $userId): ?string
    {
        $data = $this->getStateData($userId);
        return $data['state'] ?? null;
    }

    /**
     * Получает контекст пользователя
     */
    public function getContext(int $userId): array
    {
        $data = $this->getStateData($userId);
        return $data['context'] ?? [];
    }

    /**
     * Обновляет контекст пользователя
     */
    public function updateContext(int $userId, array $context): void
    {
        $data = $this->getStateData($userId);
        $data['context'] = array_merge($data['context'] ?? [], $context);
        $data['updated_at'] = now()->toISOString();
        
        $key = $this->getCacheKey($userId);
        Cache::put($key, $data, $this->config['cache_ttl']);
    }

    /**
     * Очищает состояние пользователя
     */
    public function clearState(int $userId): void
    {
        $key = $this->getCacheKey($userId);
        Cache::forget($key);
        
        if ($this->config['debug']) {
            Log::info("FSM: Cleared state for user {$userId}");
        }
    }

    /**
     * Проверяет, находится ли пользователь в определенном состоянии
     */
    public function isInState(int $userId, string $state): bool
    {
        return $this->getCurrentState($userId) === $state;
    }

    /**
     * Получает все доступные состояния
     */
    public function getAvailableStates(): array
    {
        return array_keys($this->states);
    }

    /**
     * Получает все переходы
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    /**
     * Получает возможные переходы из текущего состояния
     */
    public function getPossibleTransitions(int $userId): array
    {
        $currentState = $this->getCurrentState($userId);
        $transitions = [];
        
        foreach ($this->transitions as $transition) {
            if ($transition['from'] === $currentState || $transition['from'] === '*') {
                $transitions[] = $transition;
            }
        }
        
        return $transitions;
    }

    /**
     * Выполняет событие
     */
    public function triggerEvent(int $userId, string $event, array $data = []): void
    {
        if (isset($this->eventHandlers[$event])) {
            $handler = $this->eventHandlers[$event];
            $handler($userId, $data);
        }
    }

    /**
     * Получает данные состояния из кэша
     */
    private function getStateData(int $userId): array
    {
        $key = $this->getCacheKey($userId);
        return Cache::get($key, []);
    }

    /**
     * Генерирует ключ кэша для пользователя
     */
    private function getCacheKey(int $userId): string
    {
        return $this->config['cache_prefix'] . $userId;
    }

    /**
     * Экспортирует состояние FSM для отладки
     */
    public function exportState(): array
    {
        return [
            'states' => array_keys($this->states),
            'transitions' => $this->transitions,
            'event_handlers' => array_keys($this->eventHandlers),
            'config' => $this->config,
        ];
    }
}