<?php

namespace TelegramBot\Api\BotApi;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyParameters;

trait PaymentTrait
{
    public function sendInvoice(
        $chatId,
        $title,
        $description,
        $payload,
        $providerToken,
        $startParameter,
        $currency,
        $prices,
        $isFlexible = false,
        $photoUrl = null,
        $photoSize = null,
        $photoWidth = null,
        $photoHeight = null,
        $needName = false,
        $needPhoneNumber = false,
        $needEmail = false,
        $needShippingAddress = false,
        $replyToMessageId = null,
        $replyMarkup = null,
        $disableNotification = false,
        $providerData = null,
        $sendPhoneNumberToProvider = false,
        $sendEmailToProvider = false,
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

        return Message::fromResponse($this->call('sendInvoice', [
            'chat_id' => $chatId,
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'provider_token' => $providerToken,
            'start_parameter' => $startParameter,
            'currency' => $currency,
            'prices' => json_encode($prices),
            'is_flexible' => $isFlexible,
            'photo_url' => $photoUrl,
            'photo_size' => $photoSize,
            'photo_width' => $photoWidth,
            'photo_height' => $photoHeight,
            'need_name' => $needName,
            'need_phone_number' => $needPhoneNumber,
            'need_email' => $needEmail,
            'need_shipping_address' => $needShippingAddress,
            'message_thread_id' => $messageThreadId,
            'reply_markup' => is_null($replyMarkup) ? $replyMarkup : $replyMarkup->toJson(),
            'disable_notification' => (bool) $disableNotification,
            'provider_data' => $providerData,
            'send_phone_number_to_provider' => (bool) $sendPhoneNumberToProvider,
            'send_email_to_provider' => (bool) $sendEmailToProvider,
            'protect_content' => (bool) $protectContent,
            'reply_parameters' => is_null($replyParameters) ? $replyParameters : $replyParameters->toJson()
        ]));
    }

    public function answerShippingQuery($shippingQueryId, $ok = true, $shippingOptions = [], $errorMessage = null)
    {
        return $this->call('answerShippingQuery', [
            'shipping_query_id' => $shippingQueryId,
            'ok' => (bool) $ok,
            'shipping_options' => json_encode($shippingOptions),
            'error_message' => $errorMessage
        ]);
    }

    public function answerPreCheckoutQuery($preCheckoutQueryId, $ok = true, $errorMessage = null)
    {
        return $this->call('answerPreCheckoutQuery', [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'ok' => (bool) $ok,
            'error_message' => $errorMessage
        ]);
    }
}
