<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Types\ArrayOfBotCommand;
use TelegramBot\Api\Types\ArrayOfUpdates;
use TelegramBot\Api\Types\User;
use TelegramBot\Api\Types\UserProfilePhotos;
use TelegramBot\Api\Types\WebhookInfo;

trait WebhookTrait
{
    public function getUserProfilePhotos($userId, $offset = 0, $limit = 100): UserProfilePhotos
    {
        return UserProfilePhotos::fromResponse($this->call('getUserProfilePhotos', [
            'user_id' => (int) $userId,
            'offset' => (int) $offset,
            'limit' => (int) $limit,
        ]));
    }

    public function setWebhook(
        $url = '',
        $certificate = null,
        $ipAddress = null,
        $maxConnections = 40,
        $allowedUpdates = null,
        $dropPendingUpdates = false,
        $secretToken = null
    ) {
        return $this->call('setWebhook', [
            'url' => $url,
            'certificate' => $certificate,
            'ip_address' => $ipAddress,
            'max_connections' => $maxConnections,
            'allowed_updates' => \is_array($allowedUpdates) ? json_encode($allowedUpdates) : $allowedUpdates,
            'drop_pending_updates' => $dropPendingUpdates,
            'secret_token' => $secretToken
        ]);
    }

    public function deleteWebhook($dropPendingUpdates = false)
    {
        return $this->call('deleteWebhook', ['drop_pending_updates' => $dropPendingUpdates]);
    }

    public function getWebhookInfo(): WebhookInfo
    {
        return WebhookInfo::fromResponse($this->call('getWebhookInfo'));
    }

    public function getMe(): User
    {
        return User::fromResponse($this->call('getMe'));
    }

    public function getUpdates($offset = 0, $limit = 100, $timeout = 0): array
    {
        return ArrayOfUpdates::fromResponse($this->call('getUpdates', [
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => $timeout,
        ]));
    }

    public function setMyCommands($commands, $scope = null, $languageCode = null)
    {
        if (!$commands instanceof ArrayOfBotCommand) {
            @trigger_error(sprintf('Passing array of BotCommand to "%s::%s" is deprecated. Use %s', __CLASS__, __METHOD__, ArrayOfBotCommand::class), \E_USER_DEPRECATED);
            $commands = new ArrayOfBotCommand($commands);
        }

        return $this->call('setMyCommands', [
            'commands' => $commands->toJson(),
            'scope' => $scope,
            'language_code' => $languageCode,
        ]);
    }

    public function getMyCommands(): ArrayOfBotCommand
    {
        return ArrayOfBotCommand::fromResponse($this->call('getMyCommands'));
    }
}
