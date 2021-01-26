<?php
/**
 * Created by PhpStorm.
 * User: tianyi
 * Date: 2018-12-28
 * Time: 13:34
 */

include_once "GrowingIO.php";

// 请在您调试前，将accountID修改为您的项目AccountID
// 所有自定义事件需要提前在GrowingIO产品中进行定义
// 所有自定义事件的属性也需要提前在GrowingIO产品中进行定义

$accountID = "YOUR_ACCOUNT_ID";

//$gio = GrowingIO::getInstance($accountID, array("debug"=>true));
$gio = GrowingIO::getInstance($accountID, array());

function currentMillisecond() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

$start = currentMillisecond();
printf($start."\n");
for ($i=0;$i<10;$i++) {
$gio->track("user1","eventTest", array("eventKey1"=>"eventValue1"));
}
$stop = currentMillisecond();
printf($stop."\n");
printf(($stop-$start)."\n");
?>