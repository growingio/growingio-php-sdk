<?php

include_once "GrowingIO.php";

// 请在您调试前，将accountID修改为您的项目AccountID
// 所有自定义事件需要提前在GrowingIO产品中进行定义
// 所有自定义事件的属性也需要提前在GrowingIO产品中进行定义

$accountID = "YOUR_ACCOUNT_ID"; // 项目 ID，见数据源配置
$host = "http://YOUR_COLLECTOR_DOMAIN"; // 数据收集服务域名，请参考运维手册或联系技术支持获取
$dataSourceId = "YOUR_DATASOURCE_ID"; // 数据源 ID，见数据源配置
$props = array("debug"=>false); // debug 模式，此模式仅打印日志，不发送数据

$gio = GrowingIO::getInstance($accountID, $host, $dataSourceId, $props);

function currentMillisecond() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

$start = currentMillisecond();
printf($start."\n");
$gio->track("testUserId","testEvent", array("eventKey2"=>"v1", "eventKey2"=>"v2"));
$gio->setUserAttributes("testUserId", array("userKey1"=>"v1", "userKey2"=>"v2"));
$stop = currentMillisecond();
printf($stop."\n");
printf(($stop-$start)."\n");
?>