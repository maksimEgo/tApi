<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Types\File;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyParameters;
use TelegramBot\Api\Types\StickerSet;

trait StickerTrait
{
    public function sendSticker(
        $chatId,
        $sticker,
        $replyToMessageId = null,
        $replyMarkup = null,
        $disableNotification = false,
        $protectContent = false,
        $allowSendingWithoutReply = false,
        $messageThreadId = null,
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

        return Message::fromResponse($this->call('sendSticker', [
            'chat_id' => $chatId,
            'sticker' => $sticker,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function getStickerSet($name): StickerSet
    {
        return StickerSet::fromResponse($this->call('getStickerSet', [
            'name' => $name,
        ]));
    }

    public function getCustomEmojiStickers($customEmojiIds = []): StickerSet
    {
        return StickerSet::fromResponse($this->call('getCustomEmojiStickers', [
            'custom_emoji_ids' => $customEmojiIds,
        ]));
    }

    public function uploadStickerFile($userId, $pngSticker): File
    {
        return File::fromResponse($this->call('uploadStickerFile', [
            'user_id' => $userId,
            'png_sticker' => $pngSticker,
        ]));
    }

    public function createNewStickerSet(
        $userId,
        $name,
        $title,
        $emojis,
        $pngSticker,
        $tgsSticker = null,
        $webmSticker = null,
        $stickerType = null,
        $maskPosition = null,
        $attachments = []
    ) {
        return $this->call('createNewStickerSet', [
                'user_id' => $userId,
                'name' => $name,
                'title' => $title,
                'png_sticker' => $pngSticker,
                'tgs_sticker' => $tgsSticker,
                'webm_sticker' => $webmSticker,
                'sticker_type' => $stickerType,
                'emojis' => $emojis,
                'mask_position' => is_null($maskPosition) ? $maskPosition : $maskPosition->toJson(),
            ] + $attachments);
    }

    public function addStickerToSet(
        $userId,
        $name,
        $emojis,
        $pngSticker,
        $tgsSticker = null,
        $webmSticker = null,
        $maskPosition = null,
        $attachments = []
    ) {
        return $this->call('addStickerToSet', [
                'user_id' => $userId,
                'name' => $name,
                'png_sticker' => $pngSticker,
                'tgs_sticker' => $tgsSticker,
                'webm_sticker' => $webmSticker,
                'emojis' => $emojis,
                'mask_position' => is_null($maskPosition) ? $maskPosition : $maskPosition->toJson(),
            ] + $attachments);
    }

    public function setStickerPositionInSet($sticker, $position)
    {
        return $this->call('setStickerPositionInSet', [
            'sticker' => $sticker,
            'position' => $position,
        ]);
    }

    public function setStickerSetThumbnail($name, $userId, $thumbnail = null)
    {
        return $this->call('setStickerSetThumb', [
            'name' => $name,
            'user_id' => $userId,
            'thumbnail' => $thumbnail,
        ]);
    }

    public function setStickerSetThumb($name, $userId, $thumb = null)
    {
        return $this->setStickerSetThumbnail($name, $userId, $thumb);
    }
}
