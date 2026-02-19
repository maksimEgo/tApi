<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\ArrayOfMessages;
use TelegramBot\Api\Types\File;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyParameters;

trait MediaTrait
{
    public function sendVideo(
        $chatId,
        $video,
        $duration = null,
        $caption = null,
        $replyToMessageId = null,
        $replyMarkup = null,
        $disableNotification = false,
        $supportsStreaming = false,
        $parseMode = null,
        $messageThreadId = null,
        $protectContent = null,
        $allowSendingWithoutReply = null,
        $thumbnail = null,
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

        return Message::fromResponse($this->call('sendVideo', [
            'chat_id' => $chatId,
            'video' => $video,
            'duration' => $duration,
            'caption' => $caption,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'supports_streaming' => (bool) $supportsStreaming,
            'parse_mode' => $parseMode,
            'protect_content' => (bool) $protectContent,
            'thumbnail' => $thumbnail,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendAnimation(
        $chatId,
        $animation,
        $duration = null,
        $caption = null,
        $replyToMessageId = null,
        $replyMarkup = null,
        $disableNotification = false,
        $parseMode = null,
        $messageThreadId = null,
        $protectContent = null,
        $allowSendingWithoutReply = null,
        $thumbnail = null,
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

        return Message::fromResponse($this->call('sendAnimation', [
            'chat_id' => $chatId,
            'animation' => $animation,
            'duration' => $duration,
            'caption' => $caption,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'parse_mode' => $parseMode,
            'protect_content' => (bool) $protectContent,
            'thumbnail' => $thumbnail,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendVoice(
        $chatId,
        $voice,
        $caption = null,
        $duration = null,
        $replyMarkup = null,
        $disableNotification = false,
        $parseMode = null,
        $messageThreadId = null,
        $protectContent = null,
        $replyParameters = null
    ): Message {
        return Message::fromResponse($this->call('sendVoice', [
            'chat_id' => $chatId,
            'voice' => $voice,
            'caption' => $caption,
            'duration' => $duration,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'parse_mode' => $parseMode,
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendAudio(
        $chatId,
        $audio,
        $duration = null,
        $performer = null,
        $title = null,
        $caption = null,
        $replyMarkup = null,
        $disableNotification = false,
        $parseMode = null,
        $protectContent = null,
        $thumbnail = null,
        $messageThreadId = null,
        $replyParameters = null
    ): Message {
        return Message::fromResponse($this->call('sendAudio', [
            'chat_id' => $chatId,
            'audio' => $audio,
            'duration' => $duration,
            'performer' => $performer,
            'title' => $title,
            'caption' => $caption,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'parse_mode' => $parseMode,
            'protect_content' => (bool) $protectContent,
            'thumbnail' => $thumbnail,
            'message_thread_id' => $messageThreadId,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendPhoto(
        $chatId,
        $photo,
        $caption = null,
        $replyToMessageId = null,
        $replyMarkup = null,
        $disableNotification = false,
        $parseMode = null,
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

        return Message::fromResponse($this->call('sendPhoto', [
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'parse_mode' => $parseMode,
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendDocument(
        $chatId,
        $document,
        $caption = null,
        $replyMarkup = null,
        $disableNotification = false,
        $parseMode = null,
        $messageThreadId = null,
        $protectContent = null,
        $thumbnail = null,
        $replyParameters = null
    ): Message {
        return Message::fromResponse($this->call('sendDocument', [
            'chat_id' => $chatId,
            'document' => $document,
            'caption' => $caption,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'parse_mode' => $parseMode,
            'protect_content' => (bool) $protectContent,
            'thumbnail' => $thumbnail,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function getFile($fileId): File
    {
        return File::fromResponse($this->call('getFile', ['file_id' => $fileId]));
    }

    public function downloadFile($fileId): bool|string
    {
        $file = $this->getFile($fileId);
        if (!$path = $file->getFilePath()) {
            throw new Exception('Empty file_path property');
        }
        if (!$this->fileEndpoint) {
            return file_get_contents($path);
        }

        return $this->httpClient->download($this->fileEndpoint . '/' . $path);
    }

    public function sendVideoNote(
        $chatId,
        $videoNote,
        $duration = null,
        $length = null,
        $replyMarkup = null,
        $disableNotification = false,
        $messageThreadId = null,
        $protectContent = null,
        $thumbnail = null,
        $replyParameters = null
    ): Message {
        return Message::fromResponse($this->call('sendVideoNote', [
            'chat_id' => $chatId,
            'video_note' => $videoNote,
            'duration' => $duration,
            'length' => $length,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'protect_content' => (bool) $protectContent,
            'thumbnail' => $thumbnail,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendMediaGroup(
        $chatId,
        $media,
        $disableNotification = false,
        $replyToMessageId = null,
        $messageThreadId = null,
        $protectContent = null,
        $allowSendingWithoutReply = null,
        $attachments = [],
        $replyParameters = null
    ): array {
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

        return ArrayOfMessages::fromResponse($this->call('sendMediaGroup', [
                'chat_id' => $chatId,
                'media' => $media->toJson(),
                'message_thread_id' => $messageThreadId,
                'disable_notification' => (bool) $disableNotification,
                'protect_content' => (bool) $protectContent,
                'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
            ] + $attachments));
    }
}
