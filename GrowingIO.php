<?php

namespace com\growingio;

// @codingStandardsIgnoreLine
trait JsonSerializableTrait
{
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $val) {
            if (!is_null($val)) {
                $data[$key] = $val;
            }
        }
        return $data;
    }
}

// @codingStandardsIgnoreLine
trait TimeStampTrait
{
    private function currentMillisecond()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
}

// @codingStandardsIgnoreLine
class GrowingIO
{
    private $accountID;
    private $host;
    private $dataSourceId;
    private $options;
    private $consumer;

    private static $instance = null;

    private function validateAccountID($accountID)
    {
        if (is_null($accountID)) {
            throw new Exception('accountID is null');
        }
        if (strlen($accountID) <> 16 && strlen($accountID) <> 32) {
            printf('WARNING: AccountId length error\n');
        }
    }

    use TimeStampTrait;

    /**
     * Instantiates a new GrowingIO instance.
     * @param $accountID 项目 ID，见数据源配置
     * @param $host 数据收集服务域名，请参考运维手册或联系技术支持获取
     * @param $dataSourceId 数据源 ID，见数据源配置
     * @param array $options 额外参数，目前支持 debug 模式，此模式仅打印日志，不发送数据
     * @throws Exception 初始化参数不合法
     */
    private function __construct($accountID, $host, $dataSourceId, $options = array())
    {
        $this->validateAccountID($accountID);
        $this->accountID = $accountID;
        $this->host = $host;
        $this->dataSourceId = $dataSourceId;
        $this->options = array_merge(
            $options,
            array('accountId' => $accountID, 'host' => $host, 'dataSourceId' => $dataSourceId)
        );
        if (isset($options['debug']) && $options['debug'] === true) {
            $this->consumer = new DebugConsumer($this->options);
        } else {
            $this->consumer = new SimpleConsumer($this->options);
        }
    }

    /**
     * Returns a singleton instance of GrowingIO
     * @param $accountID 项目 ID，见数据源配置
     * @param $host 数据收集服务域名，请参考运维手册或联系技术支持获取
     * @param $dataSourceId 数据源 ID，见数据源配置
     * @param array $options 额外参数，目前支持 debug 模式
     * @return GrowingIO
     * @throws Exception 初始化参数不合法
     */
    public static function getInstance($accountID, $host, $dataSourceId, $options = array())
    {
        if (empty(self::$instance)) {
            self::$instance = new GrowingIO($accountID, $host, $dataSourceId, $options);
        }
        return self::$instance;
    }

    /**
     * track a custom event
     * @param $loginUserId loginUser's ID
     * @param $eventKey the key of customEvent, registered in GrowingIO
     * @param array $properties the properties of this event, registered in GrowingIO
     * @param $id 物品模型id
     * @param $key 物品模型key
     * @return void
     */
    public function track($loginUserId, $eventKey, $properties = array(), $id = null, $key = null)
    {
        $event = new CustomEvent();
        $event->dataSourceId($this->dataSourceId);
        $event->eventTime($this->currentMillisecond());
        $event->eventKey($eventKey);
        $event->loginUserId($loginUserId);

        if (!empty($properties)) {
            $event->eventProperties($properties);
        }

        if (!empty($id) && !empty($key)) {
            $event->resourceItem(array('id' => $id, 'key' => $key));
        }

        $this->consumer->consume($event);
    }

    public function setUserAttributes($logUserId, $properties)
    {
        $user = new UserProps();
        $user->dataSourceId($this->dataSourceId);
        $user->eventTime($this->currentMillisecond());
        $user->loginUserId($logUserId);
        $user->userProperties($properties);
        $this->consumer->consume($user);
    }

    public function setItemAttributes($itemId, $itemKey, $properties = array())
    {
        $item = new ItemProps();
        $item->dataSourceId($this->dataSourceId);
        $item->id($itemId);
        $item->key($itemKey);
        if (!empty($properties)) {
            $item->itemProps($properties);
        }
        $item->projectKey($this->accountID);
        $this->consumer->consume($item);
    }
}

// @codingStandardsIgnoreLine
class CustomEvent implements \JsonSerializable
{
    private $timestamp;
    private $eventName;
    private $userId;
    private $attributes;
    private $eventType;
    private $dataSourceId;
    private $resourceItem;

    public function __construct()
    {
        $this->timestamp = time() * 1000;
        $this->eventType = 'CUSTOM';
    }

    public function dataSourceId($dataSourceId)
    {
        $this->dataSourceId = $dataSourceId;
    }

    public function eventTime($time)
    {
        $this->timestamp = $time;
    }

    public function eventKey($eventKey)
    {
        $this->eventName = $eventKey;
    }

    public function loginUserId($loginUserId)
    {
        $this->userId = $loginUserId;
    }

    public function eventProperties($properties)
    {
        $this->attributes = $properties;
    }

    public function resourceItem($resourceItem)
    {
        $this->resourceItem = $resourceItem;
    }

    use JsonSerializableTrait;
}


// @codingStandardsIgnoreLine
class UserProps implements \JsonSerializable
{
    private $userId;
    private $attributes;
    private $eventType;
    private $dataSourceId;
    private $timestamp;

    public function __construct()
    {
        $this->timestamp = time() * 1000;
        $this->eventType = 'LOGIN_USER_ATTRIBUTES';
    }

    public function dataSourceId($dataSourceId)
    {
        $this->dataSourceId = $dataSourceId;
    }

    public function eventTime($time)
    {
        $this->timestamp = $time;
    }

    public function loginUserId($loginUserId)
    {
        $this->userId = $loginUserId;
    }

    public function userProperties($properties)
    {
        $this->attributes = $properties;
    }

    use JsonSerializableTrait;
}

// @codingStandardsIgnoreLine
class ItemProps implements \JsonSerializable
{
    private $id;
    private $key;
    private $dataSourceId;
    private $projectKey;
    private $attributes;

    public function __construct()
    {
    }

    public function dataSourceId($dataSourceId)
    {
        $this->dataSourceId = $dataSourceId;
    }

    public function id($id)
    {
        $this->id = $id;
    }

    public function key($key)
    {
        $this->key = $key;
    }

    public function itemProps($properties)
    {
        $this->attributes = $properties;
    }

    public function projectKey($projectKey)
    {
        $this->projectKey = $projectKey;
    }

    use JsonSerializableTrait;
}

// @codingStandardsIgnoreLine
class JSonUploader
{
    private $accountId;
    private $host;
    private $port;
    private $channels;

    public function __construct($options)
    {
        $this->accountId = $options['accountId'];
        $this->host = $options['host'];
        if (substr($this->host, 0, 5) === 'https') {
            $this->port = 443;
        } else {
            $this->port = 80;
        }
        $this->channels = array(
            'default' => "{$this->host}/v3/projects/$this->accountId/collect",
            'item' => "{$this->host}/projects/$this->accountId/collect/item"
        );
    }

    use TimeStampTrait;

    public function uploadEvents($events = array(), $ch = 'default')
    {
        $curl = curl_init();
        $data = json_encode($events);

        $host = isset($this->channels[$ch]) ? $this->channels[$ch] : $this->channels['default'];
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->port,
            CURLOPT_URL => ("$host?stm=" . $this->currentMillisecond()),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/json'
            ),
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $response = curl_exec($curl);
        if (false === $response) {
            $curl_error = curl_error($curl);
            $curl_errno = curl_errno($curl);
            curl_close($curl);
            printf("errno:[$curl_errno] error:[$curl_error]\n");
            return false;
        } else {
            curl_close($curl);
        }
    }
}

// @codingStandardsIgnoreLine
abstract class Consumer
{
    abstract public function consume($event);
}

// @codingStandardsIgnoreLine
class SimpleConsumer extends Consumer
{
    private $uploader;

    public function __construct($options)
    {
        $this->uploader = new JSonUploader($options);
    }

    public function consume($event)
    {
        $this->uploader->uploadEvents(array($event), $this->getChannel($event));
    }

    private function getChannel($event)
    {
        if ($event instanceof ItemProps) {
            return 'item';
        } else {
            return 'default';
        }
    }
}

// @codingStandardsIgnoreLine
class DebugConsumer extends Consumer
{
    public function __construct($options)
    {
    }

    public function consume($event)
    {
        printf(json_encode($event) . "\n");
    }
}
