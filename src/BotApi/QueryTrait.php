<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Types\Inline\QueryResult\AbstractInlineQueryResult;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Poll;
use TelegramBot\Api\Types\ReplyParameters;
use TelegramBot\Api\Types\SentWebAppMessage;

trait QueryTrait
{
    public function answerInlineQuery(
        $inlineQueryId,
        $results,
        $cacheTime = 300,
        $isPersonal = false,
        $nextOffset = '',
        $switchPmText = null,
        $switchPmParameter = null
    ) {
        $results = array_map(
        /**
         * @param AbstractInlineQueryResult $item
         * @return array
         */
            function ($item) {
                /** @var array $array */
                $array = $item->toJson(true);

                return $array;
            },
            $results
        );

        return $this->call('answerInlineQuery', [
            'inline_query_id' => $inlineQueryId,
            'results' => json_encode($results),
            'cache_time' => $cacheTime,
            'is_personal' => $isPersonal,
            'next_offset' => $nextOffset,
            'switch_pm_text' => $switchPmText,
            'switch_pm_parameter' => $switchPmParameter,
        ]);
    }

    public function answerCallbackQuery($callbackQueryId, $text = null, $showAlert = false, $url = null, $cacheTime = 0)
    {
        return $this->call('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => (bool) $showAlert,
            'url' => $url,
            'cache_time' => $cacheTime
        ]);
    }

    public function answerWebAppQuery($webAppQueryId, $result): SentWebAppMessage
    {
        return SentWebAppMessage::fromResponse($this->call('answerWebAppQuery', [
            'web_app_query_id' => $webAppQueryId,
            'result' => $result->toJson(),
        ]));
    }

    public function sendPoll(
        $chatId,
        $question,
        $options,
        $isAnonymous = false,
        $type = null,
        $allowsMultipleAnswers = false,
        $correctOptionId = null,
        $isClosed = false,
        $disableNotification = false,
        $replyToMessageId = null,
        $replyMarkup = null,
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

        return Message::fromResponse($this->call('sendPoll', [
            'chat_id' => $chatId,
            'question' => $question,
            'options' => json_encode($options),
            'is_anonymous' => (bool) $isAnonymous,
            'type' => (string) $type,
            'allows_multiple_answers' => (bool) $allowsMultipleAnswers,
            'correct_option_id' => (int) $correctOptionId,
            'is_closed' => (bool) $isClosed,
            'disable_notification' => (bool) $disableNotification,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => $replyMarkup === null ? $replyMarkup : $replyMarkup->toJson(),
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function sendDice(
        $chatId,
        $emoji,
        $disableNotification = false,
        $replyToMessageId = null,
        $allowSendingWithoutReply = false,
        $replyMarkup = null,
        $messageThreadId = null,
        $protectContent = null,
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

        return Message::fromResponse($this->call('sendDice', [
            'chat_id' => $chatId,
            'emoji' => $emoji,
            'disable_notification' => (bool) $disableNotification,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => $replyMarkup === null ? $replyMarkup : $replyMarkup->toJson(),
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function stopPoll(
        $chatId,
        $messageId,
        $replyMarkup = null
    ): Poll {
        return Poll::fromResponse($this->call('stopPoll', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
        ]));
    }
}
