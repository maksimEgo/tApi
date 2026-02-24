<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyParameters;

trait LocationTrait
{
    public function sendLocation(
        $chatId,
        $latitude,
        $longitude,
        $replyToMessageId = null,
        $replyMarkup = null,
        $disableNotification = false,
        $livePeriod = null,
        $messageThreadId = null,
        $protectContent = null,
        $allowSendingWithoutReply = null,
        $replyParameters = null
    ): Message {
        if (null !== $replyToMessageId || null !== $allowSendingWithoutReply) {
            @trigger_error(
                'setting $replyToMessageId or $allowSendingWithoutReply is now deprecated use $replyParameters instead',
                E_USER_DEPRECATED
            );

            $replyParameters = new ReplyParameters();
            $replyParameters->map([
                'message_id' => $replyToMessageId,
                'allow_sending_without_reply' => (bool) $allowSendingWithoutReply
            ]);
        }

        return Message::fromResponse($this->call('sendLocation', [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'live_period' => $livePeriod,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function editMessageLiveLocation(
        $chatId,
        $messageId,
        $inlineMessageId,
        $latitude,
        $longitude,
        $replyMarkup = null
    ): Message|bool {
        $response = $this->call('editMessageLiveLocation', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'inline_message_id' => $inlineMessageId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
        ]);
        if ($response === true) {
            return true;
        }

        return Message::fromResponse($response);
    }

    public function stopMessageLiveLocation(
        $chatId,
        $messageId,
        $inlineMessageId,
        $replyMarkup = null
    ): Message|bool {
        $response = $this->call('stopMessageLiveLocation', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'inline_message_id' => $inlineMessageId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
        ]);
        if ($response === true) {
            return true;
        }

        return Message::fromResponse($response);
    }

    public function sendVenue(
        $chatId,
        $latitude,
        $longitude,
        $title,
        $address,
        $foursquareId = null,
        $replyToMessageId = null,
        $replyMarkup = null,
        $disableNotification = false,
        $messageThreadId = null,
        $protectContent = null,
        $allowSendingWithoutReply = null,
        $replyParameters = null
    ): Message {
        if (null !== $replyToMessageId || null !== $allowSendingWithoutReply) {
            @trigger_error(
                'setting $replyToMessageId or $allowSendingWithoutReply is now deprecated use $replyParameters instead',
                E_USER_DEPRECATED
            );

            $replyParameters = new ReplyParameters();
            $replyParameters->map([
                'message_id' => $replyToMessageId,
                'allow_sending_without_reply' => (bool) $allowSendingWithoutReply
            ]);
        }

        return Message::fromResponse($this->call('sendVenue', [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'title' => $title,
            'address' => $address,
            'foursquare_id' => $foursquareId,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }
}
