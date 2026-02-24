<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Types\LinkPreviewOptions;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageId;
use TelegramBot\Api\Types\InputMedia\InputMedia;
use TelegramBot\Api\Types\ReplyParameters;

trait MessageTrait
{
    public function sendMessage(
        $chatId,
        $text,
        $parseMode = null,
        $disablePreview = false,
        $replyToMessageId = null,
        $replyMarkup = null,
        $disableNotification = false,
        $messageThreadId = null,
        $protectContent = null,
        $allowSendingWithoutReply = null,
        $replyParameters = null,
        $linkPreviewOptions = null
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

        if (null === $linkPreviewOptions && false !== $disablePreview) {
            @trigger_error('setting $disablePreview is now deprecated use $linkPreviewOptions instead', E_USER_DEPRECATED);

            $linkPreviewOptions = new LinkPreviewOptions();
            $linkPreviewOptions->map([
                'is_disabled' => $disablePreview
            ]);
        }

        return Message::fromResponse($this->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'message_thread_id' => $messageThreadId,
            'parse_mode' => $parseMode,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson(),
            'link_preview_options' => is_null($linkPreviewOptions) ? $linkPreviewOptions : $linkPreviewOptions->toJson()
        ]));
    }

    public function copyMessage(
        $chatId,
        $fromChatId,
        $messageId,
        $caption = null,
        $parseMode = null,
        $captionEntities = null,
        $disableNotification = false,
        $replyToMessageId = null,
        $allowSendingWithoutReply = false,
        $replyMarkup = null,
        $messageThreadId = null,
        $protectContent = null,
        $replyParameters = null
    ) {
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

        return MessageId::fromResponse($this->call('copyMessage', [
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => (int) $messageId,
            'caption' => $caption,
            'parse_mode' => $parseMode,
            'caption_entities' => $captionEntities,
            'disable_notification' => (bool) $disableNotification,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendContact(
        $chatId,
        $phoneNumber,
        $firstName,
        $lastName = null,
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

        return Message::fromResponse($this->call('sendContact', [
            'chat_id' => $chatId,
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendChatAction($chatId, $action)
    {
        return $this->call('sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action,
        ]);
    }

    public function forwardMessage(
        $chatId,
        $fromChatId,
        $messageId,
        $protectContent = false,
        $disableNotification = false,
        $messageThreadId = null
    ): Message {
        return Message::fromResponse($this->call('forwardMessage', [
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
            'message_thread_id' => $messageThreadId,
            'protect_content' => $protectContent,
            'disable_notification' => (bool) $disableNotification,
        ]));
    }

    public function editMessageText(
        $chatId,
        $messageId,
        $text,
        $parseMode = null,
        $disablePreview = false,
        $replyMarkup = null,
        $inlineMessageId = null,
        $linkPreviewOptions = null
    ): Message|bool {
        if (null === $linkPreviewOptions && false !== $disablePreview) {
            @trigger_error('setting $disablePreview is now deprecated use $linkPreviewOptions instead', E_USER_DEPRECATED);

            $linkPreviewOptions = new LinkPreviewOptions();
            $linkPreviewOptions->map([
                'is_disabled' => $disablePreview
            ]);
        }

        $response = $this->call('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'inline_message_id' => $inlineMessageId,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => $disablePreview,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
        ]);
        if ($response === true) {
            return true;
        }

        return Message::fromResponse($response);
    }

    public function editMessageCaption(
        $chatId,
        $messageId,
        $caption = null,
        $replyMarkup = null,
        $inlineMessageId = null,
        $parseMode = null
    ): Message|bool {
        $response = $this->call('editMessageCaption', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'inline_message_id' => $inlineMessageId,
            'caption' => $caption,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'parse_mode' => $parseMode
        ]);
        if ($response === true) {
            return true;
        }

        return Message::fromResponse($response);
    }

    public function editMessageMedia(
        $chatId,
        $messageId,
        InputMedia $media,
        $inlineMessageId = null,
        $replyMarkup = null,
        $attachments = []
    ): Message|bool {
        $response = $this->call('editMessageMedia', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'inline_message_id' => $inlineMessageId,
                'media' => $media->toJson(),
                'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            ] + $attachments);
        if ($response === true) {
            return true;
        }

        return Message::fromResponse($response);
    }

    public function editMessageReplyMarkup(
        $chatId,
        $messageId,
        $replyMarkup = null,
        $inlineMessageId = null
    ): Message|bool {
        $response = $this->call('editMessageReplyMarkup', [
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

    public function deleteMessage($chatId, $messageId)
    {
        return $this->call('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function setMessageReaction($chatId, $messageId, $reaction, $isBig = false)
    {
        return $this->call('setMessageReaction', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reaction' => $reaction,
            'is_big' => $isBig
        ]);
    }

    public function deleteMessages($chatId, $messageIds)
    {
        return $this->call('deleteMessages', [
            'chat_id' => $chatId,
            'message_ids' => $messageIds
        ]);
    }

    public function forwardMessages(
        $chatId,
        $fromChatId,
        $messageIds,
        $messageThreadId = null,
        $disableNotification = false,
        $protectContent = false
    ) {
        return $this->call('forwardMessages', [
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_ids' => $messageIds,
            'message_thread_id' => $messageThreadId,
            'disable_notification' => (bool) $disableNotification,
            'protect_content' => (bool) $protectContent
        ]);
    }

    public function copyMessages(
        $chatId,
        $fromChatId,
        $messageIds,
        $messageThreadId,
        $disableNotification = false,
        $protectContent = false,
        $removeCaption = false
    ) {
        return $this->call('copyMessages', [
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_ids' => $messageIds,
            'message_thread_id' => $messageThreadId,
            'disable_notification' => (bool) $disableNotification,
            'protect_content' => (bool) $protectContent,
            'remove_caption' => (bool) $removeCaption
        ]);
    }
}
