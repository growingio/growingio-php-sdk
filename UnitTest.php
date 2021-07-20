<?php

namespace com\growingio\test;

use com\growingio\GrowingIO;

class UnitTest extends \PHPUnit_Framework_TestCase
{
    private static $gio;

    public static function setUpBeforeClass()
    {
        $accountID = '1234567887654321'; // 项目 ID，见数据源配置
        $host = 'https://localhost.com'; // 数据收集服务域名，请参考运维手册或联系技术支持获取
        $dataSourceId = '12345678'; // 数据源 ID，见数据源配置
        $props = array('debug' => true); // debug 模式，此模式仅打印日志，不发送数据

        self::$gio = GrowingIO::getInstance($accountID, $host, $dataSourceId, $props);
    }

    public function testTrack()
    {
        $this->setOutputCallback(function ($msg) {
            $data = json_decode($msg, true);
            $this->assertArraySubset(
                ['eventName' => 'eventKey',
                    'userId' => 'userId',
                    'eventType' => 'CUSTOM',
                    'dataSourceId' => '12345678'],
                $data
            );
        });
        self::$gio->track('userId', 'eventKey');
    }

    public function testTrackProperties()
    {
        $this->setOutputCallback(function ($msg) {
            $data = json_decode($msg, true);
            $this->assertArraySubset(
                ['eventName' => 'eventKey',
                    'userId' => 'userId',
                    'eventType' => 'CUSTOM',
                    'dataSourceId' => '12345678',
                    'attributes' => array('userKey1' => 'v1', 'userKey2' => 'v2')],
                $data
            );
        });
        self::$gio->track('userId', 'eventKey', array('userKey1' => 'v1', 'userKey2' => 'v2'));
    }

    public function testTrackResourceItem()
    {
        $this->setOutputCallback(function ($msg) {
            $data = json_decode($msg, true);
            $this->assertArraySubset(
                ['eventName' => 'eventKey',
                    'userId' => 'userId',
                    'eventType' => 'CUSTOM',
                    'dataSourceId' => '12345678',
                    'attributes' => array('userKey1' => 'v1', 'userKey2' => 'v2'),
                    'resourceItem' => array('id' => 'itemId', 'key' => 'itemKey')],
                $data
            );
        });
        self::$gio->track(
            'userId',
            'eventKey',
            array('userKey1' => 'v1', 'userKey2' => 'v2'),
            'itemId',
            'itemKey'
        );
    }

    public function testSetUserAttributes()
    {
        $this->setOutputCallback(function ($msg) {
            $data = json_decode($msg, true);
            $this->assertArraySubset(
                ['userId' => 'userId',
                    'eventType' => 'LOGIN_USER_ATTRIBUTES',
                    'dataSourceId' => '12345678',
                    'attributes' => array('userKey1' => 'v1', 'userKey2' => 'v2')],
                $data
            );
        });
        self::$gio->setUserAttributes('userId', array('userKey1' => 'v1', 'userKey2' => 'v2'));
    }

    public function testSetItemAttributes()
    {
        $this->setOutputCallback(function ($msg) {
            $data = json_decode($msg, true);
            $this->assertArraySubset(
                ['id' => 'itemId',
                    'key' => 'itemKey',
                    'dataSourceId' => '12345678',
                    'projectKey' => '1234567887654321',
                    'attributes' => array('userKey1' => 'v1', 'userKey2' => 'v2', 'price' => 0)],
                $data
            );
        });
        self::$gio->setItemAttributes('itemId', 'itemKey', array('userKey1' => 'v1', 'userKey2' => 'v2', 'price' => 0));
    }
}
