<?php

namespace Teg\Modules;

use App\Models\UserTelegram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

trait UserModule
{
    public $updateUserTelegram = true;
    public $cacheUserData = true;
    public $logUserActions = true;

    /**
     * Initialize user module
     */
    public function userModule()
    {
        try {
            $this->setUserTelegram();

            if ($this->updateUserTelegram) {
                $this->command("start", function () {
                    $this->handleStartCommand();
                });
            }
        } catch (Exception $e) {
            $this->logError('userModule initialization failed', $e);
        }
    }

    /**
     * Handle start command
     */
    private function handleStartCommand(): bool
    {
        try {
            $user = $this->setUserTelegram(true);
            
            if ($user && $this->logUserActions) {
                Log::info('User started bot', [
                    'telegram_id' => $this->getUserId(),
                    'user_id' => $user->id,
                    'username' => $user->username,
                ]);
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Start command failed', $e);
            return false;
        }
    }

    /**
     * Get user telegram with caching
     */
    private function getUserTelegram(): ?UserTelegram
    {
        try {
            $telegramId = $this->getUserId();
            
            if (!$telegramId) {
                return null;
            }

            if ($this->cacheUserData) {
                $cacheKey = "user_telegram_{$telegramId}";
                return Cache::remember($cacheKey, 3600, function () use ($telegramId) {
                    return UserTelegram::findByTelegramId($telegramId);
                });
            }

            return UserTelegram::findByTelegramId($telegramId);
        } catch (Exception $e) {
            $this->logError('Failed to get user telegram', $e);
            return null;
        }
    }

    /**
     * Set or update user telegram data
     */
    private function setUserTelegram(bool $forceUpdate = false): ?UserTelegram
    {
        try {
            $message = $this->getMessage();
            
            if (!$message) {
                return null;
            }

            $data = $message->getFrom();
            
            if (!$data) {
                return null;
            }

            $telegramData = $this->extractTelegramData($data);
            
            if (!$this->validateTelegramData($telegramData)) {
                Log::warning('Invalid telegram data received', $telegramData);
                return null;
            }

            $user = $this->getUserTelegram();

            if (!$user) {
                // Create new user
                $user = UserTelegram::createOrUpdateFromTelegram($telegramData);
                
                if ($this->logUserActions) {
                    Log::info('New user registered', [
                        'telegram_id' => $telegramData['id'],
                        'username' => $telegramData['username'] ?? null,
                        'first_name' => $telegramData['first_name'] ?? null,
                    ]);
                }
            } elseif ($forceUpdate || $this->shouldUpdateUser($user, $telegramData)) {
                // Update existing user
                $user->updateFromTelegramData($telegramData);
                
                if ($this->logUserActions) {
                    Log::info('User data updated', [
                        'telegram_id' => $telegramData['id'],
                        'changes' => $this->getChangedFields($user, $telegramData),
                    ]);
                }
            }

            // Clear cache after update
            if ($this->cacheUserData) {
                Cache::forget("user_telegram_{$telegramData['id']}");
            }

            return $user;
        } catch (Exception $e) {
            $this->logError('Failed to set user telegram', $e);
            return null;
        }
    }

    /**
     * Extract telegram data from API response
     */
    private function extractTelegramData($data): array
    {
        return [
            'id' => $data->getId(),
            'is_bot' => $data->getIsBot() ?? false,
            'first_name' => $data->getFirstName() ?? '',
            'last_name' => $data->getLastName(),
            'username' => $data->getUsername(),
            'language_code' => $data->getLanguageCode() ?? 'en',
            'is_premium' => $data->getIsPremium() ?? false,
        ];
    }

    /**
     * Validate telegram data
     */
    private function validateTelegramData(array $data): bool
    {
        return isset($data['id']) && 
               is_numeric($data['id']) && 
               !empty($data['first_name']) &&
               strlen($data['first_name']) <= 255;
    }

    /**
     * Check if user should be updated
     */
    private function shouldUpdateUser(UserTelegram $user, array $newData): bool
    {
        $fieldsToCheck = ['first_name', 'last_name', 'username', 'language_code', 'is_premium'];
        
        foreach ($fieldsToCheck as $field) {
            if ($user->{$field} !== $newData[$field]) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get changed fields for logging
     */
    private function getChangedFields(UserTelegram $user, array $newData): array
    {
        $changes = [];
        $fieldsToCheck = ['first_name', 'last_name', 'username', 'language_code', 'is_premium'];
        
        foreach ($fieldsToCheck as $field) {
            if ($user->{$field} !== $newData[$field]) {
                $changes[$field] = [
                    'old' => $user->{$field},
                    'new' => $newData[$field],
                ];
            }
        }
        
        return $changes;
    }

    /**
     * Get current user with error handling
     */
    public function getCurrentUser(): ?UserTelegram
    {
        try {
            return $this->getUserTelegram();
        } catch (Exception $e) {
            $this->logError('Failed to get current user', $e);
            return null;
        }
    }

    /**
     * Check if user exists
     */
    public function userExists(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    /**
     * Get user display name
     */
    public function getUserDisplayName(): string
    {
        $user = $this->getCurrentUser();
        return $user ? $user->display_name : 'Unknown User';
    }

    /**
     * Check if user is premium
     */
    public function isUserPremium(): bool
    {
        $user = $this->getCurrentUser();
        return $user ? $user->isPremium() : false;
    }

    /**
     * Get user language code
     */
    public function getUserLanguage(): string
    {
        $user = $this->getCurrentUser();
        return $user ? $user->language_code : 'en';
    }

    /**
     * Log error with context
     */
    private function logError(string $message, Exception $e): void
    {
        if ($this->logUserActions) {
            Log::error($message, [
                'error' => $e->getMessage(),
                'telegram_id' => $this->getUserId() ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        try {
            return [
                'new_users_today' => UserTelegram::getNewUsersToday(),
                'active_users_week' => UserTelegram::getActiveUsers(7),
                'total_users' => UserTelegram::count(),
            ];
        } catch (Exception $e) {
            $this->logError('Failed to get user stats', $e);
            return [];
        }
    }
}
