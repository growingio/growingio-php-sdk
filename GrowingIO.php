<?php

class GrowingIO {

    private $_accountID;
    private $_host;
    private $_dataSourceId;
    private $_options;
    private $_consumer;

    private static $_instance = null;

    function validateAccountID($accountID)
    {
        if($accountID == null) throw new Exception("accountID is null");
        if(strlen($accountID) <> 16 && strlen($accountID) <> 32) {
           printf("WARNING: AccountId length error\n");
        }
    }

    private function currentMillisecond() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * Instantiates a new GrowingIO instance.
     * @param $accountID
     * @param $host
     * @param $dataSourceId
     * @param array $options
     */
    private function __construct($accountID, $host, $dataSourceId, $options = array())
    {
        $this->validateAccountID($accountID);
        $this->_accountID = $accountID;
        $this->_host = $host;
        $this->_dataSourceId = $dataSourceId;
        $this->_options = array_merge($options, array("accountId"=>$accountID, "host"=>$host, "dataSourceId"=>$dataSourceId));
        if(isset($options['debug']) && $options['debug'] === true) {
            $this->_consumer = new DebugConsumer($this->_options);
        } else {
            $this->_consumer = new SimpleConsumer($this->_options);
        }
    }

    /**
     * Returns a singleton instance of GrowingIO
     * @param $accountID 项目 ID，见数据源配置
     * @param $host 数据收集服务域名，请参考运维手册或联系技术支持获取
     * @param $dataSourceId 数据源 ID，见数据源配置
     * @param array $options 额外参数，目前支持 debug 模式
     * @return GrowingIO
     */
    public static function getInstance($accountID, $host, $dataSourceId, $options = array())
    {
        if(self::$_instance == null) {
            self::$_instance = new GrowingIO($accountID, $host, $dataSourceId, $options);
        }
        return self::$_instance;
    }

    /**
     * track a custom event
     * @param $loginUserId loginUser's ID
     * @param $eventKey the key of customEvent registered in GrowingIO
     * @param array $properties the properties of this event, registered in GrowingIO
     * @return void
     */
    public function track($loginUserId, $eventKey, $properties)
    {
        $event = new CustomEvent();
        $event->dataSourceId($this->_dataSourceId);
        $event->eventTime($this->currentMillisecond());
        $event->eventKey($eventKey);
        $event->loginUserId($loginUserId);
        $event->eventProperties($properties);
        $this->_consumer->consume($event);
    }

    public function setUserAttributes($logUserId, $properties)
    {
        $user = new UserProps();
        $user->dataSourceId($this->_dataSourceId);
        $user->eventTime($this->currentMillisecond());
        $user->loginUserId($logUserId);
        $user->userProperties($properties);
        $this->_consumer->consume($user);
    }
}

class CustomEvent implements JsonSerializable
{
    private $timestamp;
    private $eventName;
    private $userId;
    private $attributes;
    private $eventType;
    private $dataSourceId;

    public function __construct() {
        $this->timestamp = time()*1000;
        $this->eventType = "CUSTOM";
    }

    public function dataSourceId($dataSourceId) {
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

    public function jsonSerialize() {
        $data = [];
        foreach ($this as $key=>$val){
            if ($val !== null) $data[$key] = $val;
        }
        return $data;
    }
}


class UserProps implements JsonSerializable
{
    private $userId;
    private $attributes;
    private $eventType;
    private $dataSourceId;

    public function __construct() {
        $this->timestamp = time()*1000;
        $this->eventType = "LOGIN_USER_ATTRIBUTES";
    }

    public function dataSourceId($dataSourceId) {
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

    public function jsonSerialize() {
        $data = [];
        foreach ($this as $key=>$val){
            if ($val !== null) $data[$key] = $val;
        }
        return $data;
    }
}

class JSonUploader
{
    private $accountId;
    private $host;
    private $port;
    private $dataSourceId;
    private $curl;
    public function __construct($options)
    {
        $this->accountId = $options["accountId"];
        $this->host = $options["host"];
        $this->dataSourceId = $options["dataSourceId"];
        if (substr( $this->host, 0, 5 ) === "https") {
            $this->port = 443;
        } else {
            $this->port = 80;
        }
        $this->curl = "{$this->host}/v3/projects/$this->accountId/collect";
    }

    protected function currentMillisecond() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    public function uploadEvents($events = array())
    {
        $curl= curl_init();
        $data = json_encode($events);
        printf("$this->curl\n");
        printf("$data\n");

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->port,
            CURLOPT_URL => "$this->curl?stm=".$this->currentMillisecond(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $response = curl_exec($curl);
        printf("response code:$response\n");
        if (false === $response) {
            $curl_error = curl_error($curl);
            $curl_errno = curl_errno($curl);
            curl_close($curl);
            printf("errno:[".$curl_errno."] error:[".$curl_error."]\n");
            return false;
        } else {
            curl_close($curl);
        }
    }
}

abstract class Consumer
{
    public abstract function consume($event);
}

class SimpleConsumer extends Consumer
{
    private $uploader;

    public function __construct($options)
    {
        $this->uploader = new JSonUploader($options);
    }

    public function consume($event)
    {
        $this->uploader->uploadEvents(array($event));
    }
}

class DebugConsumer extends Consumer
{
    public function __construct($options)
    {

    }

    public function consume($event)
    {
        printf(json_encode($event)."\n");
    }
}
?>