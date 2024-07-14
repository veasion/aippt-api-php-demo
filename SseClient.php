<?php

require 'vendor/autoload.php';

class SseClient
{
    const END_OF_MESSAGE = "/\r\n\r\n|\n\n|\r\r/";
    /** @var GuzzleHttp\Client */
    private $client;
    /** @var GuzzleHttp\Psr7\Response */
    private $response;

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client([
            'headers' => [
                'Cache-Control' => 'no-cache',
            ],
        ]);
    }

    public function request($method, $url, $headers, $body)
    {
        if (!$method) {
            $method = 'GET';
        }
        if (!$headers) {
            $headers = [];
        }
        if (!array_key_exists('Content-Type', $headers)) {
            $headers['Content-Type'] = 'application/json';
        }
        $this->response = $this->client->request($method, $url, [
            'verify' => false,
            'stream' => true,
            'headers' => $headers,
            'body' => $body
        ]);
        return $this->response;
    }

    public function eventStream()
    {
        $buffer = '';
        $body = $this->response->getBody();
        try {
            while (true) {
                if ($body->eof()) {
                    break;
                }
                $buffer .= $body->read(1);
                if (preg_match(self::END_OF_MESSAGE, $buffer)) {
                    $parts = preg_split(self::END_OF_MESSAGE, $buffer, 2);
                    $rawMessage = $parts[0];
                    $remaining = $parts[1];
                    $buffer = $remaining;
                    $event = Event::parse($rawMessage);
                    yield $event;
                }
            }
        } finally {
            try {
                $body->close();
            } catch (Exception $e) {
            }
        }
    }
}