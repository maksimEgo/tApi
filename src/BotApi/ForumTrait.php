<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Types\ArrayOfSticker;
use TelegramBot\Api\Types\ForumTopic;

trait ForumTrait
{
    public function createForumTopic(
        $chatId,
        $name,
        $iconColor,
        $iconCustomEmojiId = null
    ): ForumTopic {
        return ForumTopic::fromResponse($this->call('createForumTopic', [
            'chat_id' => $chatId,
            'name' => $name,
            'icon_color' => $iconColor,
            'icon_custom_emoji_id' => $iconCustomEmojiId,
        ]));
    }

    public function editForumTopic(
        $chatId,
        $messageThreadId,
        $name,
        $iconCustomEmojiId = null
    ) {
        return $this->call('editForumTopic', [
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
            'name' => $name,
            'icon_custom_emoji_id' => $iconCustomEmojiId,
        ]);
    }

    public function closeForumTopic($chatId, $messageThreadId)
    {
        return $this->call('closeForumTopic', [
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);
    }

    public function reopenForumTopic($chatId, $messageThreadId)
    {
        return $this->call('reopenForumTopic', [
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);
    }

    public function deleteForumTopic($chatId, $messageThreadId)
    {
        return $this->call('deleteForumTopic', [
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);
    }

    public function unpinAllForumTopicMessages($chatId, $messageThreadId)
    {
        return $this->call('unpinAllForumTopicMessages', [
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);
    }

    public function getForumTopicIconStickers(): array
    {
        return ArrayOfSticker::fromResponse($this->call('getForumTopicIconStickers'));
    }
}
