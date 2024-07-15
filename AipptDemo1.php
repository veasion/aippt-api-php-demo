<?php

require 'Api.php';

// 流式生成 PPT

// 官网 https://docmee.cn
// 开放平台 https://docmee.cn/open-platform/api

// 填写你的API-KEY
$apiKey = "YOUR API KEY";

// 第三方用户ID（数据隔离）
$uid = "test";
$subject = "AI未来的发展";

// 创建 api token (有效期2小时，建议缓存到redis，同一个 uid 创建时之前的 token 会在10秒内失效)
$apiToken = Api::createApiToken($apiKey, $uid, null);
echo "api token: " . $apiToken . "\n";

// 生成大纲
echo "\n\n========== 正在生成大纲 ==========\n";
$outline = Api::generateOutline($apiToken, $subject, null, null);

// 生成大纲内容
echo "\n\n========== 正在生成大纲内容 ==========\n";
$markdown = Api::generateContent($apiToken, $outline, null, null);

// 随机一个模板
echo "\n\n========== 随机选择模板 ==========\n";
$templateId = Api::randomOneTemplateId($apiToken);
echo "templateId: " . $templateId . "\n";

// 生成PPT
echo "\n\n========== 正在生成PPT ==========\n";
$pptInfo = Api::generatePptx($apiToken, $templateId, $markdown, false);
$pptId = $pptInfo["id"];
echo "\n" . "pptId: " . $pptId . "\n";
echo "ppt主题：" . $pptInfo["subject"] . "\n";
echo "ppt封面：" . $pptInfo["coverUrl"] . "?token=" . $apiToken . "\n";

// 下载PPT
echo "\n\n========== 正在下载PPT ==========\n";
$url = Api::downloadPptx($apiToken, $pptId);
echo "ppt链接：" . $url . "\n";
$savePath = getcwd() . "/" . $pptId . ".pptx";
HttpUtils::download($url, $savePath);
echo "ppt下载完成，保存路径：" . $savePath;
