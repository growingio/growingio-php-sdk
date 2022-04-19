<?php

namespace com\growingio\test;

use com\growingio\GrowingIO;
use PHPUnit\Framework\TestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * 执行测试文件要求本地php版本 >= 7.1
 *
 * Class UnitTest
 *
 * @package com\growingio\test
 */
class UnitTest extends TestCase
{
    use ArraySubsetAsserts;

    private static $gio;

    public static function setUpBeforeClass(): void
    {
        $accountID = '1234567887654321'; // 项目 ID，见数据源配置
        $host = 'https://localhost.com'; // 数据收集服务域名，请参考运维手册或联系技术支持获取
        $dataSourceId = '12345678'; // 数据源 ID，见数据源配置
        $props = array('debug' => true, 'idMappingEnabled' => true); // debug 模式，此模式仅打印日志，不发送数据

        self::$gio = GrowingIO::getInstance($accountID, $host, $dataSourceId, $props);
    }

    public function testTrack()
    {
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['eventName' => 'eventKey',
                    'userId' => 'userId',
                    'eventType' => 'CUSTOM',
                    'dataSourceId' => '12345678'],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->track('userId', 'eventKey');
    }

    public function testTrackCustomEvent()
    {
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['eventName' => 'eventKey',
                    'userKey' => 'userKey',
                    'userId' => 'userId',
                    'eventType' => 'CUSTOM',
                    'dataSourceId' => '12345678',
                    'timestamp' => '1648524854000',
                    'sendTime' => '1648524854000',
                    'deviceId' => 'anonymousId'],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->trackCustomEvent(
            self::$gio->getCustomEventFactory()
                ->setEventKey('eventKey')
                ->setAnonymousId('anonymousId')
                ->setLoginUserId('userId')
                ->setEventTime(1648524854000)
                ->setLoginUserKey('userKey')
                ->create()
        );
    }

    public function testTrackProperties()
    {
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['eventName' => 'eventKey',
                    'userId' => 'userId',
                    'eventType' => 'CUSTOM',
                    'dataSourceId' => '12345678',
                    'attributes' => array('userKey1' => 'v1', 'userKey2' => 'v2')],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->track('userId', 'eventKey', array('userKey1' => 'v1', 'userKey2' => 'v2'));
    }

    public function testTrackResourceItem()
    {
        $this->setOutputCallback(
            function ($msg) {
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
                return $msg;
            }
        );
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
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['userId' => 'userId',
                    'eventType' => 'LOGIN_USER_ATTRIBUTES',
                    'dataSourceId' => '12345678',
                    'attributes' => array('userKey1' => 'v1', 'userKey2' => 'v2')],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->setUserAttributes('userId', array('userKey1' => 'v1', 'userKey2' => 'v2'));
    }

    public function testSetUserAttributesEvent()
    {
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['userId' => 'userId',
                    'eventType' => 'LOGIN_USER_ATTRIBUTES',
                    'dataSourceId' => '12345678',
                    'timestamp' => '1648524854000',
                    'sendTime' => '1648524854000',
                    'deviceId' => 'anonymousId',
                    'attributes' => array('userKey1' => 'v1', 'userKey2' => 'v2')],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->setUserAttributesEvent(
            self::$gio->getUserAttributesFactory()
                ->setEventTime(1648524854000)
                ->setLoginUserKey('userKey')
                ->setAnonymousId('anonymousId')
                ->setLoginUserId('userId')
                ->setProperties(array('userKey1' => 'v1', 'userKey2' => 'v2'))
                ->create()
        );
    }

    public function testSetItemAttributes()
    {
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['id' => 'itemId',
                    'key' => 'itemKey',
                    'dataSourceId' => '12345678',
                    'projectKey' => '1234567887654321',
                    'attributes' => array('userKey1' => 'v1', 'userKey2' => 'v2', 'price' => 0)],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->setItemAttributes('itemId', 'itemKey', array('userKey1' => 'v1', 'userKey2' => 'v2', 'price' => 0));
    }

    public function testTrackCustomEventWithListAttrs()
    {
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['eventType' => 'CUSTOM',
                    'attributes' => array(
                        'list_attribute_normal' => '1||2||3',
                        'list_attribute_contains_null' => '||1||',
                        'list_attribute_empty_string' => '',
                        'list_attribute_empty_string_list' => '||||',
                        '' => '',
                        '列表属性中文' => '中文||English||にほんご',
                        'list_attribute_length' => implode('||', range(0, 100))
                    )],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->trackCustomEvent(
            self::$gio->getCustomEventFactory('userId', "list_attribute_test_event")
                ->setProperties(
                    array(
                    'list_attribute_normal' => array('1', '2', '3'),
                    'list_attribute_contains_null' => array('', '1', null),
                    'list_attribute_empty' => array(),
                    'list_attribute_empty_string' => array(''),
                    'list_attribute_empty_string_list' => array('', '', ''),
                    '' => array(''),
                    '列表属性中文' => array('中文', 'English', 'にほんご'),
                    'list_attribute_length' => range(0, 100)
                    )
                )
                ->create()
        );
    }

    public function testSetUserAttributesEventWithListAttrs()
    {
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['eventType' => 'LOGIN_USER_ATTRIBUTES',
                    'attributes' => array(
                        'list_attribute_normal' => '1||2||3',
                        'list_attribute_contains_null' => '||1||',
                        'list_attribute_empty_string' => '',
                        'list_attribute_empty_string_list' => '||||',
                        '' => '',
                        '列表属性中文' => '中文||English||にほんご',
                        'list_attribute_length' => implode('||', range(0, 100))
                    )],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->setUserAttributesEvent(
            self::$gio->getUserAttributesFactory('userId')
                ->setProperties(
                    array(
                    'list_attribute_normal' => array('1', '2', '3'),
                    'list_attribute_contains_null' => array('', '1', null),
                    'list_attribute_empty' => array(),
                    'list_attribute_empty_string' => array(''),
                    'list_attribute_empty_string_list' => array('', '', ''),
                    '' => array(''),
                    '列表属性中文' => array('中文', 'English', 'にほんご'),
                    'list_attribute_length' => range(0, 100)
                    )
                )
                ->create()
        );
    }

    public function testEmptyString()
    {
        $this->setOutputCallback(
            function ($msg) {
                $data = json_decode($msg, true);
                $this->assertArraySubset(
                    ['eventName' => 'eventKey',
                        'userId' => '0'],
                    $data
                );
                return $msg;
            }
        );
        self::$gio->track('0', 'eventKey');
    }

    public function testcheckCustomEvent()
    {
        $customEvent = self::$gio->getCustomEventFactory()
            ->setEventKey('event_name')
            ->setLoginUserId('user_id')
            ->create();
        $this->assertFalse($customEvent->isIllegal());

        $customEvent = self::$gio->getCustomEventFactory()
            ->setEventKey('event_name')
            ->setAnonymousId('anonymous_id')
            ->create();
        $this->assertFalse($customEvent->isIllegal());

        $customEvent = self::$gio->getCustomEventFactory()
            ->setEventKey('event_name')
            ->create();
        $this->assertTrue($customEvent->isIllegal());

        $customEvent = self::$gio->getCustomEventFactory()
            ->setLoginUserId('user_id')
            ->create();
        $this->assertTrue($customEvent->isIllegal());

        $customEvent = self::$gio->getCustomEventFactory()
            ->setAnonymousId('anonymous_id')
            ->create();
        $this->assertTrue($customEvent->isIllegal());
    }

    public function testCheckUserEvent()
    {
        $userProps = self::$gio->getUserAttributesFactory()
            ->setProperties(array("attr_key" => 'attr_value'))
            ->setLoginUserId("user_id")
            ->create();
        $this->assertFalse($userProps->isIllegal());

        $userProps = self::$gio->getUserAttributesFactory()
            ->setProperties(array("attr_key" => 'attr_value'))
            ->setAnonymousId("anonymous_id")
            ->create();
        $this->assertFalse($userProps->isIllegal());

        $userProps = self::$gio->getUserAttributesFactory()
            ->setAnonymousId("anonymous_id")
            ->create();
        $this->assertTrue($userProps->isIllegal());

        $userProps = self::$gio->getUserAttributesFactory()
            ->setLoginUserId("user_id")
            ->create();
        $this->assertTrue($userProps->isIllegal());

        $userProps = self::$gio->getUserAttributesFactory()
            ->setProperties(array("attr_key" => 'attr_value'))
            ->create();
        $this->assertTrue($userProps->isIllegal());
    }
}
