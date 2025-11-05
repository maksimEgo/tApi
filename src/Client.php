<?php

namespace TelegramBot\Api;

use TelegramBot\Api\Http\HttpClientInterface;

class Client
{
    public BotApi $api;

    public function __construct(string $token, ?HttpClientInterface $httpClient = null, ?string $endpoint = null)
    {
        $this->api = new BotApi($token, $httpClient, $endpoint);
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->api, $name)) {
            return call_user_func_array([$this->api, $name], $arguments);
        }

        throw new \BadMethodCallException("Method {$name} does not exist in BotApi");
    }

    public function getApi(): BotApi
    {
        return $this->api;
    }
}
