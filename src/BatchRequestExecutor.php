<?php

namespace TelegramBot\Api;

use CurlMultiHandle;

class BatchRequestExecutor
{
    private array $handles = [];
    private CurlMultiHandle $multiHandle;
    private string $token;

    public function __construct(?string $token = null)
    {
        $this->multiHandle = curl_multi_init();

        $this->token = $token;
    }

    public function add(string $method, array $params): int
    {
        $url = BotApi::URL_PREFIX . $this->token . '/' . $method;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        curl_multi_add_handle($this->multiHandle, $ch);

        $handleId = (int)$ch;
        $this->handles[$handleId] = [
            'handle' => $ch,
            'method' => $method,
            'params' => $params,
        ];

        return $handleId;
    }

    public function execute(): array
    {
        if (empty($this->handles)) {
            return [];
        }

        $running = 0;

        do {
            curl_multi_exec($this->multiHandle, $running);

            if ($running) {
                curl_multi_select($this->multiHandle, 0.1);
            }
        } while ($running > 0);

        $results = [];
        foreach ($this->handles as $handleId => $data) {
            $ch = $data['handle'];
            $response = curl_multi_getcontent($ch);

            if ($response === false) {
                $results[$handleId] = [
                    'ok' => false,
                    'error' => curl_error($ch)
                ];
            } else {
                $decoded = json_decode($response, true);
                $results[$handleId] = $decoded ?: [
                    'ok' => false,
                    'error' => 'Invalid JSON response'
                ];
            }

            curl_multi_remove_handle($this->multiHandle, $ch);
            curl_close($ch);
        }

        $this->handles = [];

        return $results;
    }

    public static function create(): self
    {
        return new self();
    }

    public function __destruct()
    {
        foreach ($this->handles as $data) {
            curl_multi_remove_handle($this->multiHandle, $data['handle']);
            curl_close($data['handle']);
        }

        if ($this->multiHandle) {
            curl_multi_close($this->multiHandle);
        }
    }
}
