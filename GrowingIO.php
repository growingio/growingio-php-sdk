<?php
/**
 * Created by PhpStorm.
 * User: tianyi
 * Version: 1.0.3
 */

class GrowingIO {

    private $_accountID;
    private $_options;
    private $_consumer;

    private static $_instance = null;

    function validateAccountID($accountID)
    {
        if($accountID == null) throw new Exception("accountID is null");
        if(strlen($accountID) <> 16 && strlen($accountID) <> 32)
            throw new Exception("accountID length error");
    }

    private function currentMillisecond() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * Instantiates a new GrowingIO instance.
     * @param $accountID
     * @param array $options
     */
    private function __construct($accountID, $options = array())
    {
        $this->validateAccountID($accountID);
        $this->_accountID = $accountID;
        $this->_options = array_merge($options, array("accountId"=>$accountID));
        if(array_key_exists("debug", $this->_options)) {
            $this->_consumer = new DebugConsumer($this->_options);
        } else {
            $this->_consumer = new SimpleConsumer($this->_options);
        }
    }

    /**
     * Returns a singleton instance of GrowingIO
     * @param $accountID
     * @param array $options
     * @return GrowingIO
     */
    public static function getInstance($accountID, $options = array())
    {
        if(self::$_instance == null) {
            self::$_instance = new GrowingIO($accountID, $options);
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
        $event->eventTime($this->currentMillisecond());
        $event->eventKey($eventKey);
        $event->loginUserId($loginUserId);
        $event->eventProperties($properties);
        $this->_consumer->consume($event);
    }
}

class CustomEvent implements JsonSerializable
{
    private $tm;
    private $n;
    private $cs1;
    private $var;
    private $t;

    public function __construct() {
        $this->tm = time()*1000;
        $this->t = "cstm";
    }

    public function eventTime($time)
    {
        $this->tm = $time;
    }

    public function eventKey($eventKey)
    {
        $this->n = $eventKey;
    }

    public function loginUserId($loginUserId)
    {
        $this->cs1 = $loginUserId;
    }

    public function EventProperties($properties)
    {
        $this->var = $properties;
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
    private $curl;
    public function __construct($options)
    {
        $this->accountId = $options["accountId"];

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
//        printf("request url: https://api.growingio.com/v3/{$this->accountId}/s2s/cstm\n");

        curl_setopt_array($curl, array(
            CURLOPT_PORT => "443",
            CURLOPT_URL => "https://api.growingio.com/v3/{$this->accountId}/s2s/cstm?stm=".$this->currentMillisecond(),
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