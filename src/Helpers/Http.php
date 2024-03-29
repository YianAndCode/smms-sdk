<?php

namespace SmMs\Helpers;

class Http
{
    private $client;

    private $headers = [
        'User-Agent: SmMs Sdk Client'
    ];

    public function __construct()
    {
        $this->client = curl_init();
    }

    public function get($url, $data = null)
    {
        return $this->request('GET', $url, $data);
    }

    public function post($url, $data = null)
    {
        return $this->request('POST', $url, $data);
    }

    public function request($method, $url, $data = null)
    {
        switch (strtolower($method)) {
            case 'get':
                if (! is_null($data)) {
                    $url = rtrim($url, '?') . '?' . http_build_query($data);
                }
                break;

            case 'post':
                // TODO: 
                break;

            default:
                throw new \Exception('Unsupport method');
        }

        curl_setopt($this->client, CURLOPT_URL, $url);
        curl_setopt($this->client, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->client, CURLOPT_HEADER, true);
		curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->client, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->client, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($this->client);

        $http_code = curl_getinfo($this->client, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($this->client, CURLINFO_HEADER_SIZE);

        $headers_str = substr($result, 0, $header_size);
        $headers = [];
        foreach (explode("\r\n", $headers_str) as $item) {
            $item = explode(":", $item);
            if (count($item) < 2) {
                continue;
            }
            $key = $item[0];
            unset($item[0]);
            $key = str_replace('-', '_', $key);
            $headers[$key] = implode(":", $item);
        }

        $body = substr($result, $header_size);

        $response = [
            'code'    => $http_code,
            'headers' => $headers,
            'body'    => $body,
        ];
        
        return $response;
    }

    public function __destruct()
    {
        curl_close($this->client);
    }
}
