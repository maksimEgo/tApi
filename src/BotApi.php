<?php

namespace TelegramBot\Api;

use TelegramBot\Api\Http\PersistentCurlHttpClient;
use TelegramBot\Api\Types\UserChatBoosts;
use TelegramBot\Api\Types\ReplyParameters;
use TelegramBot\Api\Http\HttpClientInterface;
use TelegramBot\Api\Types\ArrayOfBotCommand;
use TelegramBot\Api\Types\LinkPreviewOptions;
use TelegramBot\Api\Types\ArrayOfChatMemberEntity;
use TelegramBot\Api\Types\ArrayOfMessages;
use TelegramBot\Api\Types\ArrayOfSticker;
use TelegramBot\Api\Types\ArrayOfUpdates;
use TelegramBot\Api\Types\ChatFullInfo;
use TelegramBot\Api\Types\ChatInviteLink;
use TelegramBot\Api\Types\ChatMember;
use TelegramBot\Api\Types\File;
use TelegramBot\Api\Types\ForumTopic;
use TelegramBot\Api\Types\Inline\QueryResult\AbstractInlineQueryResult;
use TelegramBot\Api\Types\InputMedia\InputMedia;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\MessageId;
use TelegramBot\Api\Types\Poll;
use TelegramBot\Api\Types\SentWebAppMessage;
use TelegramBot\Api\Types\StickerSet;
use TelegramBot\Api\Types\User;
use TelegramBot\Api\Types\UserProfilePhotos;
use TelegramBot\Api\Types\WebhookInfo;

class BotApi
{
    public static array $codes = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    const URL_PREFIX = 'https://api.telegram.org/bot';

    const FILE_URL_PREFIX = 'https://api.telegram.org/file/bot';

    const DEFAULT_STATUS_CODE = 200;

    const NOT_MODIFIED_STATUS_CODE = 304;

    const MAX_TRACKED_EVENTS = 200;

    private HttpClientInterface $httpClient;

    private string $token;

    private string $endpoint;

    private ?string $fileEndpoint;

    public function __construct(string $token, ?HttpClientInterface $httpClient = null, string $endpoint = null)
    {
        $this->token = $token;
        $this->endpoint = ($endpoint ?: self::URL_PREFIX) . $token;
        $this->fileEndpoint = $endpoint ? null : (self::FILE_URL_PREFIX . $token);

        $this->httpClient = $httpClient ?: new PersistentCurlHttpClient();
    }

    public function validateWebAppData($rawData, $authDateDiff = null): bool
    {
        parse_str($rawData, $data);

        $sign = $data['hash'];
        unset($data['hash']);

        if ($authDateDiff && (time() - $data['auth_date'] > $authDateDiff)) {
            return false;
        }

        ksort($data);
        $checkString = '';
        foreach ($data as $k => $v) {
            $checkString .= "$k=$v\n";
        }
        $checkString = trim($checkString);

        $secret = hash_hmac('sha256', $this->token, 'WebAppData', true);

        return bin2hex(hash_hmac('sha256', $checkString, $secret, true)) === $sign;
    }

    public function setModeObject($mode = true): static
    {
        @trigger_error(sprintf('Method "%s::%s" is deprecated', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        return $this;
    }

    public function call($method, ?array $data = null, $timeout = null)
    {
        if ($timeout !== null) {
            @trigger_error(sprintf('Passing $timeout parameter in %s::%s is deprecated. Use http client options', __CLASS__, __METHOD__), \E_USER_DEPRECATED);
        }

        $endpoint = $this->endpoint . '/' . $method;

        return $this->httpClient->request($endpoint, $data);
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

    public static function curlValidate($curl, $response = null): void
    {
        @trigger_error(sprintf('Method "%s::%s" is deprecated', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        if ($response) {
            $json = json_decode($response, true) ?: [];
        } else {
            $json = [];
        }
        if (($httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE))
            && !in_array($httpCode, [self::DEFAULT_STATUS_CODE, self::NOT_MODIFIED_STATUS_CODE])
        ) {
            $errorDescription = array_key_exists('description', $json) ? $json['description'] : self::$codes[$httpCode];
            $errorParameters = array_key_exists('parameters', $json) ? $json['parameters'] : [];
            throw new HttpException($errorDescription, $httpCode, null, $errorParameters);
        }
    }

    public static function jsonValidate($jsonString, $asArray)
    {
        $json = json_decode($jsonString, $asArray);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new InvalidJsonException(json_last_error_msg(), json_last_error());
        }

        return $json;
    }

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

    public function kickChatMember($chatId, $userId, $untilDate = null)
    {
        @trigger_error(sprintf('Method "%s::%s" is deprecated. Use "banChatMember"', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        return $this->call('kickChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate
        ]);
    }

    public function banChatMember($chatId, $userId, $untilDate = null, $revokeMessages = null)
    {
        return $this->call('banChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate,
            'revoke_messages' => $revokeMessages,
        ]);
    }

    public function unbanChatMember($chatId, $userId)
    {
        return $this->call('unbanChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
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

    public function restrictChatMember(
        $chatId,
        $userId,
        $untilDate = null,
        $canSendMessages = false,
        $canSendMediaMessages = false,
        $canSendOtherMessages = false,
        $canAddWebPagePreviews = false
    ) {
        return $this->call('restrictChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate,
            'can_send_messages' => $canSendMessages,
            'can_send_media_messages' => $canSendMediaMessages,
            'can_send_other_messages' => $canSendOtherMessages,
            'can_add_web_page_previews' => $canAddWebPagePreviews
        ]);
    }

    public function promoteChatMember(
        $chatId,
        $userId,
        $canChangeInfo = true,
        $canPostMessages = true,
        $canEditMessages = true,
        $canDeleteMessages = true,
        $canInviteUsers = true,
        $canRestrictMembers = true,
        $canPinMessages = true,
        $canPromoteMembers = true,
        $canManageTopics = true,
        $isAnonymous = false
    ) {
        return $this->call('promoteChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'is_anonymous' => $isAnonymous,
            'can_change_info' => $canChangeInfo,
            'can_post_messages' => $canPostMessages,
            'can_edit_messages' => $canEditMessages,
            'can_delete_messages' => $canDeleteMessages,
            'can_invite_users' => $canInviteUsers,
            'can_restrict_members' => $canRestrictMembers,
            'can_pin_messages' => $canPinMessages,
            'can_promote_members' => $canPromoteMembers,
            'can_manage_topics' => $canManageTopics
        ]);
    }

    public function exportChatInviteLink($chatId)
    {
        return $this->call('exportChatInviteLink', [
            'chat_id' => $chatId
        ]);
    }

    public function createChatInviteLink($chatId, $name = null, $expireDate = null, $memberLimit = null, $createsJoinRequest = null): ChatInviteLink
    {
        return ChatInviteLink::fromResponse($this->call('createChatInviteLink', [
            'chat_id' => $chatId,
            'name' => $name,
            'expire_date' => $expireDate,
            'member_limit' => $memberLimit,
            'creates_join_request' => $createsJoinRequest,
        ]));
    }

    public function editChatInviteLink($chatId, $inviteLink, $name = null, $expireDate = null, $memberLimit = null, $createsJoinRequest = null): ChatInviteLink
    {
        return ChatInviteLink::fromResponse($this->call('editChatInviteLink', [
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
            'name' => $name,
            'expire_date' => $expireDate,
            'member_limit' => $memberLimit,
            'creates_join_request' => $createsJoinRequest,
        ]));
    }

    public function revokeChatInviteLink($chatId, $inviteLink): ChatInviteLink
    {
        return ChatInviteLink::fromResponse($this->call('revokeChatInviteLink', [
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ]));
    }

    public function approveChatJoinRequest($chatId, $userId)
    {
        return $this->call('approveChatJoinRequest', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    public function declineChatJoinRequest($chatId, $userId)
    {
        return $this->call('declineChatJoinRequest', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    public function setChatPhoto($chatId, $photo)
    {
        return $this->call('setChatPhoto', [
            'chat_id' => $chatId,
            'photo' => $photo
        ]);
    }

    public function deleteChatPhoto($chatId)
    {
        return $this->call('deleteChatPhoto', [
            'chat_id' => $chatId
        ]);
    }

    public function setChatTitle($chatId, $title)
    {
        return $this->call('setChatTitle', [
            'chat_id' => $chatId,
            'title' => $title
        ]);
    }

    public function setChatDescription($chatId, $description = null)
    {
        return $this->call('setChatDescription', [
            'chat_id' => $chatId,
            'title' => $description
        ]);
    }

    public function pinChatMessage($chatId, $messageId, $disableNotification = false)
    {
        return $this->call('pinChatMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification
        ]);
    }

    public function unpinChatMessage($chatId, $messageId = null)
    {
        return $this->call('unpinChatMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function getChat($chatId): ChatFullInfo
    {
        return ChatFullInfo::fromResponse($this->call('getChat', [
            'chat_id' => $chatId
        ]));
    }

    public function getChatMember($chatId, $userId)
    {
        return ChatMember::fromResponse($this->call('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId
        ]));
    }

    public function leaveChat($chatId)
    {
        return $this->call('leaveChat', [
            'chat_id' => $chatId
        ]);
    }

    public function getChatMembersCount($chatId)
    {
        @trigger_error(sprintf('Method "%s::%s" is deprecated. Use "getChatMemberCount"', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        return $this->call('getChatMembersCount', [
            'chat_id' => $chatId
        ]);
    }

    public function getChatMemberCount($chatId)
    {
        return $this->call('getChatMemberCount', [
            'chat_id' => $chatId
        ]);
    }

    public function getChatAdministrators($chatId): array
    {
        return ArrayOfChatMemberEntity::fromResponse(
            $this->call(
                'getChatAdministrators',
                [
                    'chat_id' => $chatId
                ]
            )
        );
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

    public function answerWebAppQuery($webAppQueryId, $result): SentWebAppMessage
    {
        return SentWebAppMessage::fromResponse($this->call('answerWebAppQuery', [
            'web_app_query_id' => $webAppQueryId,
            'result' => $result->toJson(),
        ]));
    }

    public function setProxy($proxyString = '', $socks5 = false): static
    {
        @trigger_error(sprintf('Method "%s:%s" is deprecated. Manage options on HttpClient instance', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        if (method_exists($this->httpClient, 'setProxy')) {
            $this->httpClient->setProxy($proxyString, $socks5);
        }

        return $this;
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

    public function getUserChatBoosts($chatId, $userId): UserChatBoosts
    {
        return UserChatBoosts::fromResponse($this->call('getUserChatBoosts', [
            'chat_id' => $chatId,
            'user_id' => $userId
        ]));
    }

    public function setCurlOption($option, $value): void
    {
        @trigger_error(sprintf('Method "%s:%s" is deprecated. Manage options on http client instance', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        if (method_exists($this->httpClient, 'setOption')) {
            $this->httpClient->setOption($option, $value);
        }
    }

    public function unsetCurlOption($option): void
    {
        @trigger_error(sprintf('Method "%s:%s" is deprecated. Manage options on http client instance', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        if (method_exists($this->httpClient, 'unsetOption')) {
            $this->httpClient->unsetOption($option);
        }
    }

    public function resetCurlOptions(): void
    {
        @trigger_error(sprintf('Method "%s:%s" is deprecated. Manage options on http client instance', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        if (method_exists($this->httpClient, 'resetOptions')) {
            $this->httpClient->resetOptions();
        }
    }
}
