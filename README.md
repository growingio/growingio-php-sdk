# GrowingIO PHP SDK

GrowingIO提供在Server端部署的SDK，从而可以方便的进行事件上报等操作 <https://docs.growingio.com/v3/developer-manual/sdkintegrated/server-sdk/php-sdk>

## 示例程序

```php
<?php
include_once "GrowingIO.php";

$accountID = "YOUR_ACCOUNT_ID"; // 项目 AI
$gio = GrowingIO::getInstance($accountID, array());

//上传事件行为消息到服务器
$gio->track("user1","eventTest", array("eventKey1"=>"eventValue1"));
?>
```

详细代码样例参考 [Demo](Demo.php)