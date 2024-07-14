<?php

require 'Event.php';
require 'SseClient.php';
require 'HttpUtils.php';

class Api
{

    const BASE_URL = "https://chatmee.cn/api";

    public static function createApiToken($apiKey, $userId)
    {
        $url = Api::BASE_URL . "/user/createApiToken";
        $headers = [
            "Api-Key" => $apiKey
        ];
        $body = json_encode([
            "uid" => $userId
        ]);
        $resp = HttpUtils::postJson($url, $headers, $body);
        if ($resp["statusCode"] != 200) {
            throw new RuntimeException("创建apiToken失败，httpStatus=" . $resp["statusCode"]);
        }
        $json = json_decode($resp["text"], true);
        if ($json["code"] != 0) {
            throw new RuntimeException("创建apiToken异常：" . $json["message"]);
        }
        return $json["data"]["token"];
    }

    public static function generateOutline($apiToken, $subject, $prompt = null, $dataUrl = null)
    {
        $url = Api::BASE_URL . "/ppt/generateOutline";
        $headers = [
            "token" => $apiToken
        ];
        $body = json_encode([
            "subject" => $subject,
            "prompt" => $prompt,
            "dataUrl" => $dataUrl
        ]);
        $sb = [];
        $resp = HttpUtils::postSse($url, $headers, $body, function ($data) use (&$sb) {
            $json = json_decode($data, true);
            if (array_key_exists("status", $json) && $json["status"] == -1) {
                throw new RuntimeException("生成大纲异常：" . $json["error"]);
            }
            if (array_key_exists("text", $json)) {
                $text = $json['text'];
                array_push($sb, $text);
                echo $text;
            }
        });
        if ($resp["statusCode"] != 200) {
            throw new RuntimeException("生成大纲失败，httpStatus=" . $resp["statusCode"]);
        }
        if (strpos($resp["contentType"], 'application/json') !== false) {
            $json = json_decode($resp["text"], true);
            throw new RuntimeException("生成大纲异常：" . $json["message"]);
        }
        return implode('', $sb);
    }

    public static function generateContent($apiToken, $outlineMarkdown, $prompt = null, $dataUrl = null)
    {
        $url = Api::BASE_URL . "/ppt/generateContent";
        $headers = [
            "token" => $apiToken
        ];
        $body = json_encode([
            "outlineMarkdown" => $outlineMarkdown,
            "prompt" => $prompt,
            "dataUrl" => $dataUrl
        ]);
        $sb = [];
        $resp = HttpUtils::postSse($url, $headers, $body, function ($data) use (&$sb) {
            $json = json_decode($data, true);
            if (array_key_exists("status", $json) && $json["status"] == -1) {
                throw new RuntimeException("生成大纲内容异常：" . $json["error"]);
            }
            if (array_key_exists("text", $json)) {
                $text = $json['text'];
                array_push($sb, $text);
                echo $text;
            }
        });
        if ($resp["statusCode"] != 200) {
            throw new RuntimeException("生成大纲内容失败，httpStatus=" . $resp["statusCode"]);
        }
        if (strpos($resp["contentType"], 'application/json') !== false) {
            $json = json_decode($resp["text"], true);
            throw new RuntimeException("生成大纲内容异常：" . $json["message"]);
        }
        return implode('', $sb);
    }

    public static function randomOneTemplateId($apiToken)
    {
        $url = Api::BASE_URL . "/ppt/randomTemplates";
        $headers = [
            "token" => $apiToken
        ];
        $body = json_encode([
            "size" => 1,
            "filters" => [
                "type" => 1
            ]
        ]);
        $resp = HttpUtils::postJson($url, $headers, $body);
        if ($resp["statusCode"] != 200) {
            throw new RuntimeException("创建模板失败，httpStatus=" . $resp["statusCode"]);
        }
        $json = json_decode($resp["text"], true);
        if ($json["code"] != 0) {
            throw new RuntimeException("创建模板异常：" . $json["message"]);
        }
        return $json["data"][0]["id"];
    }

    public static function generatePptx($apiToken, $templateId, $markdown, $pptxProperty = false)
    {
        $url = Api::BASE_URL . "/ppt/generatePptx";
        $headers = [
            "token" => $apiToken
        ];
        $body = json_encode([
            "templateId" => $templateId,
            "outlineContentMarkdown" => $markdown,
            "pptxProperty" => $pptxProperty
        ]);
        $resp = HttpUtils::postJson($url, $headers, $body);
        if ($resp["statusCode"] != 200) {
            throw new RuntimeException("生成PPT失败，httpStatus=" . $resp["statusCode"]);
        }
        $json = json_decode($resp["text"], true);
        if ($json["code"] != 0) {
            throw new RuntimeException("生成PPT异常：" . $json["message"]);
        }
        return $json["data"]["pptInfo"];
    }

    public static function downloadPptx($apiToken, $id)
    {
        $url = Api::BASE_URL . "/ppt/downloadPptx";
        $headers = [
            "token" => $apiToken
        ];
        $body = json_encode([
            "id" => $id
        ]);
        $resp = HttpUtils::postJson($url, $headers, $body);
        if ($resp["statusCode"] != 200) {
            throw new RuntimeException("下载PPT失败，httpStatus=" . $resp["statusCode"]);
        }
        $json = json_decode($resp["text"], true);
        if ($json["code"] != 0) {
            throw new RuntimeException("下载PPT异常：" . $json["message"]);
        }
        return $json["data"]["fileUrl"];
    }

    public static function directGeneratePptx($apiToken, $stream, $templateId, $subject, $prompt = null, $dataUrl = null, $pptxProperty = false)
    {
        $url = Api::BASE_URL . "/ppt/directGeneratePptx";
        $headers = [
            "token" => $apiToken
        ];
        $body = json_encode([
            "stream" => $stream,
            "templateId" => $templateId,
            "subject" => $subject,
            "prompt" => $prompt,
            "dataUrl" => $dataUrl,
            "pptxProperty" => $pptxProperty
        ]);
        if ($stream) {
            $pptInfo = [];
            $resp = HttpUtils::postSse($url, $headers, $body, function ($data) use (&$pptInfo) {
                $json = json_decode($data, true);
                if (array_key_exists("status", $json) && $json["status"] == -1) {
                    throw new RuntimeException("生成大纲内容异常：" . $json["error"]);
                }
                if (array_key_exists("status", $json) && $json["status"] == 4 && array_key_exists("result", $json)) {
                    array_push($pptInfo, $json["result"]);
                }
                if (array_key_exists("text", $json)) {
                    echo $json['text'];
                }
            });
            if ($resp["statusCode"] != 200) {
                throw new RuntimeException("生成PPT失败，httpStatus=" . $resp["statusCode"]);
            }
            if (strpos($resp["contentType"], 'application/json') !== false) {
                $json = json_decode($resp["text"], true);
                throw new RuntimeException("生成PPT异常：" . $json["message"]);
            }
            return $pptInfo[0];
        } else {
            $resp = HttpUtils::postJson($url, $headers, $body);
            if ($resp["statusCode"] != 200) {
                throw new RuntimeException("生成PPT失败，httpStatus=" . $resp["statusCode"]);
            }
            $json = json_decode($resp["text"], true);
            if ($json["code"] != 0) {
                throw new RuntimeException("生成PPT异常：" . $json["message"]);
            }
            return $json["data"]["pptInfo"];
        }
    }

}