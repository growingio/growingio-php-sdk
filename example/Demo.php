<?php

use com\growingio\GrowingIO;

require '../vendor/autoload.php';

// 请在您调试前，将accountID修改为您的项目AccountID
// 所有自定义事件需要提前在GrowingIO产品中进行定义
// 所有自定义事件的属性也需要提前在GrowingIO产品中进行定义

$accountID = '1234567887654321'; // 项目 ID，见数据源配置
$host = 'https://localhost.com'; // 数据收集服务域名，请参考运维手册或联系技术支持获取
$dataSourceId = '12345678'; // 数据源 ID，见数据源配置
$props = array('debug' => true, 'idMappingEnabled' => true); // debug 模式，此模式仅打印日志，不发送数据

$gio = GrowingIO::getInstance($accountID, $host, $dataSourceId, $props);

function currentMillisecond()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

$start = currentMillisecond();
printf($start . PHP_EOL);
$gio->setUserAttributes('phpUserId', array('userKey1' => 'v1', 'userKey2' => 'v2'));
$gio->setUserAttributesEvent($gio->getUserAttributesFactory('pUserId')
    ->setLoginUserKey('pUserKey')
    ->setProperties(array('userKey1' => 'v1', 'userKey2' => 'v2'))
    ->create());
$gio->setUserAttributesEvent($gio->getUserAttributesFactory('pUserId')->create());
$gio->track('phpUserId', 'phpEvent');
$gio->track(
    'phpUserId',
    'phpEvent',
    array('userKey1' => 'v1', 'userKey2' => 'v2')
);
$gio->trackCustomEvent($gio->getCustomEventFactory('loginUserId', 'pEvent')
    ->setLoginUserKey('loginUserKey')
    ->setProperties(array('userKey1' => 'v1', 'userKey2' => 'v2'))
    ->create()
);
$gio->trackCustomEvent($gio->getCustomEventFactory('loginUserId', 'pEvent')->create());
$gio->setItemAttributes('1', 'phpKey', array('phpAttrKey' => 'phpAttrValue', 'phpColor' => 'red'));
$stop = currentMillisecond();
printf($stop . PHP_EOL);
printf(($stop - $start) . PHP_EOL);
