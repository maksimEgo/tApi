<?php

namespace TelegramBot\Api\Http;

use TelegramBot\Api\HttpException;
use TelegramBot\Api\InvalidJsonException;

class CurlHttpClient extends AbstractHttpClient
{
    /**
     * Default http status code
     */
    const DEFAULT_STATUS_CODE = 200;

    /**
     * Not Modified http status code
     */
    const NOT_MODIFIED_STATUS_CODE = 304;

    /**
     * CURL object
     *
     * @var \CurlHandle
     */
    private $curl;

    /**
     * @var array
     */
    private $options;

    public function __construct(array $options = [])
    {
        $this->curl = curl_init();
        $this->options = $options;
    }

    public function __destruct()
    {
        if ($this->curl instanceof \CurlHandle) {
            curl_close($this->curl);
        }
    }

    /**
     * @inheritDoc
     */
    protected function doRequest($url, ?array $data = null)
    {
        $options = $this->options + [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ];

        if ($data) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        return self::jsonValidate($this->execute($options));
    }

    /**
     * @inheritDoc
     */
    protected function doDownload($url)
    {
        $options = [
            CURLOPT_HEADER => false,
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
        ];

        return $this->execute($options);
    }

    /**
     * @param array $options
     * @return string
     * @throws HttpException
     */
    private function execute(array $options)
    {
        curl_setopt_array($this->curl, $options);

        /** @var string|false $result */
        $result = curl_exec($this->curl);
        if ($result === false) {
            throw new HttpException(curl_error($this->curl), curl_errno($this->curl));
        }

        self::curlValidate($this->curl, $result);

        return $result;
    }

    /**
     * @param string $jsonString
     * @return array
     * @throws InvalidJsonException
     */
    private static function jsonValidate($jsonString)
    {
        /** @var array $json */
        $json = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidJsonException(json_last_error_msg(), json_last_error());
        }

        return $json;
    }

    /**
     * @param \CurlHandle $curl
     * @param string|null $response
     * @return void
     * @throws HttpException
     */
    private static function curlValidate($curl, $response = null)
    {
        $json = json_decode((string) $response, true) ?: [];

        if (($httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE))
            && !in_array($httpCode, [self::DEFAULT_STATUS_CODE, self::NOT_MODIFIED_STATUS_CODE])
        ) {
            $errorDescription = array_key_exists('description', $json) ? $json['description'] : 'HTTP Error ' . $httpCode;
            $errorParameters = array_key_exists('parameters', $json) ? $json['parameters'] : [];

            throw new HttpException($errorDescription, $httpCode, null, $errorParameters);
        }
    }

    /**
     * Enable proxy for curl requests. Empty string will disable proxy.
     *
     * @param string $proxyString
     * @param bool $socks5
     * @return void
     */
    public function setProxy($proxyString = '', $socks5 = false)
    {
        if (empty($proxyString)) {
            unset($this->options[CURLOPT_PROXY], $this->options[CURLOPT_HTTPPROXYTUNNEL], $this->options[CURLOPT_PROXYTYPE]);

            return;
        }

        $this->options[CURLOPT_PROXY] = $proxyString;
        $this->options[CURLOPT_HTTPPROXYTUNNEL] = true;

        if ($socks5) {
            $this->options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
        }
    }

    /**
     * @param string $option
     * @param string|int|bool $value
     * @return void
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * @param string $option
     * @return void
     */
    public function unsetOption($option)
    {
        unset($this->options[$option]);
    }

    /**
     * @return void
     */
    public function resetOptions()
    {
        $this->options = [];
    }
}
