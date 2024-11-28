<?php

namespace Teg\Modules;

use App\Models\UserTelegram;

trait UserModule
{
    public $updateUserTelegram = true;

    public function userModule()
    {
        $this->setUserTelegram(false);

        if ($this->updateUserTelegram) {
            $this->command("start", function () {
                $this->setUserTelegram();
            });
        }
    }

    private function getUserTelegram()
    {
        return UserTelegram::where('telegram_id', $this->getUserId)->first();
    }

    private function setUserTelegram($update = false)
    {
        $user = $this->getUserTelegram();

        $message = $this->getMessage();

        if (isset($message)) {

            $data = $message->getFrom();

            if (!$user) {
                $reg = new UserTelegram;
                $reg->telegram_id = $data->getId();
                $reg->is_bot = $data->getIsBot();
                $reg->first_name = $data->getFirstName();
                $reg->last_name = $data->getLastName();
                $reg->username = $data->getUsername();
                $reg->language_code = $data->getLanguageCode();
                $reg->is_premium = $data->getIsPremium() ? $data->getIsPremium() : 0;
                $reg->save();
            } else {
                if ($update) {
                    $user->first_name = $data->getFirstName();
                    $user->last_name = $data->getLastName();
                    $user->username = $data->getUsername();
                    $user->language_code = $data->getLanguageCode();
                    $user->is_premium = $data->getIsPremium() ? $data->getIsPremium() : 0;
                    $user->save();
                }
            }
        }
    }
}
