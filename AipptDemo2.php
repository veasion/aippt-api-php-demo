<?php

require 'Api.php';

// 直接生成 PPT

// 官网 https://docmee.cn
// 开放平台 https://docmee.cn/open-platform/api

// 填写你的API-KEY
$apiKey = "{{ YOUR API KEY }}";

// 第三方用户ID（数据隔离）
$userId = "test";
$subject = "AI未来的发展";

// 创建 apiToken (有效期2小时，建议缓存到redis)
$apiToken = Api::createApiToken($apiKey, $userId);
echo "apiToken: " . $apiToken . "\n";

// 直接生成PPT
echo "\n\n========== 正在生成PPT ==========\n";
$pptInfo = Api::directGeneratePptx($apiToken, true, null, $subject);
$pptId = $pptInfo["id"];
$fileUrl = $pptInfo["fileUrl"];
echo "\n" . "pptId: " . $pptId . "\n";
echo "ppt主题：" . $pptInfo["subject"] . "\n";
echo "ppt封面：" . $pptInfo["coverUrl"] . "?token=" . $apiToken . "\n";
echo "ppt链接：" . $fileUrl . "\n";

// 下载PPT
echo "\n\n========== 正在下载PPT ==========\n";
$savePath = getcwd() . "/" . $pptId . ".pptx";
HttpUtils::download($fileUrl, $savePath);
echo "ppt下载完成，保存路径：" . $savePath;
