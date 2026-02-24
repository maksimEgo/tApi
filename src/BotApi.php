<?php

namespace TelegramBot\Api;

use TelegramBot\Api\BotApi\ChatTrait;
use TelegramBot\Api\BotApi\ForumTrait;
use TelegramBot\Api\BotApi\LocationTrait;
use TelegramBot\Api\BotApi\MediaTrait;
use TelegramBot\Api\BotApi\MessageTrait;
use TelegramBot\Api\BotApi\PaymentTrait;
use TelegramBot\Api\BotApi\QueryTrait;
use TelegramBot\Api\BotApi\StickerTrait;
use TelegramBot\Api\BotApi\WebhookTrait;
use TelegramBot\Api\Http\HttpClientInterface;
use TelegramBot\Api\Http\PersistentCurlHttpClient;

class BotApi
{
    use MessageTrait;
    use MediaTrait;
    use ChatTrait;
    use StickerTrait;
    use LocationTrait;
    use PaymentTrait;
    use ForumTrait;
    use QueryTrait;
    use WebhookTrait;

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

    public function setProxy($proxyString = '', $socks5 = false): static
    {
        @trigger_error(sprintf('Method "%s:%s" is deprecated. Manage options on HttpClient instance', __CLASS__, __METHOD__), \E_USER_DEPRECATED);

        if (method_exists($this->httpClient, 'setProxy')) {
            $this->httpClient->setProxy($proxyString, $socks5);
        }

        return $this;
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
