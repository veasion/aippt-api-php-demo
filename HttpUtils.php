<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpUtils
{

    /**
     * 流式请求
     */
    public static function postSse($url, $headers, $body, $consumer)
    {
        if (!$headers) {
            $headers = [];
        }
        $headers['Content-Type'] = 'application/json';
        $sseClient = new SseClient();
        $response = $sseClient->request("POST", $url, $headers, $body);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeader('Content-Type')[0];
        if ($statusCode != 200 || strpos($contentType, 'application/json') !== false) {
            $text = null;
            try {
                $text = $response->getBody()->getContents();
                $response->getBody()->close();
            } catch (Exception $e) {
            }
            return [
                'statusCode' => $statusCode,
                'contentType' => $contentType,
                'text' => $text
            ];
        }
        $eventStream = $sseClient->eventStream();
        foreach ($eventStream as $event) {
            $data = $event->getData();
            if ($data === '' || trim($data) === '[DONE]') {
                continue;
            }
            call_user_func($consumer, $data);
        }
        return [
            'statusCode' => $statusCode,
            'contentType' => $contentType,
            'text' => null
        ];
    }

    public static function postJson($url, $headers, $body)
    {
        if (!$headers) {
            $headers = [];
        }
        $headers['Content-Type'] = 'application/json';
        $client = new Client();
        try {
            $response = $client->post($url, [
                'verify' => false,
                'headers' => $headers,
                'body' => $body
            ]);
            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeader('Content-Type')[0];
            $text = $response->getBody()->getContents();
            return [
                'statusCode' => $statusCode,
                'contentType' => $contentType,
                'text' => $text
            ];
        } catch (GuzzleException $e) {
            echo 'Request failed: ' . $e->getMessage();
            return [
                'statusCode' => 500,
                'contentType' => "",
                'text' => null
            ];
        }
    }

    public static function download($url, $savePath)
    {
        try {
            $client = new Client();
            $client->request('GET', $url, [
                'verify' => false,
                'sink' => $savePath
            ]);
            return true;
        } catch (GuzzleException $e) {
            echo "Download failed: " . $e->getMessage();
            return false;
        }
    }
}