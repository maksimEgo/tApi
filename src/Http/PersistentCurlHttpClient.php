<?php

namespace TelegramBot\Api\Http;

class PersistentCurlHttpClient extends CurlHttpClient
{
    public function __construct(array $options = [])
    {
        parent::__construct($options + [
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 120,
                CURLOPT_TCP_KEEPINTVL => 60,
                CURLOPT_FRESH_CONNECT => false,
                CURLOPT_FORBID_REUSE => false,
                CURLOPT_MAXCONNECTS => 10,
                CURLOPT_DNS_CACHE_TIMEOUT => 300,
            ]);
    }
}
