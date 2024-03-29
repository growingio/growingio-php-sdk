# GrowingIO PHP SDK

GrowingIO提供在Server端部署的PHP SDK,从而可以方便的进行事件上报等操作

### 集成 & 安装

php sdk已经发布在[Packagist](https://packagist.org/packages/growingio/php-sdk), 可以通过[Composer](https://getcomposer.org)进行安装

```composer
"growingio/php-sdk": "1.0.3"
```

```php
<?php
use com\growingio\GrowingIO;
//Load Composer's autoloader
require 'vendor/autoload.php';
```

如果没有使用Composer, 可以直接下载源代码到php配置中指定的include_path目录中, 并手动加载类文件

```php
<?php
use com\growingio\GrowingIO;

include_once 'path/src/GrowingIO.php'; // path为对应路径
```

### 初始化配置

###### 初始化参数

|参数|必选|类型|默认值|说明|
|:----|:----|:----|:----|-----|
|accountID|true|string| |项目 ID,见数据源配置|
|host|true|string| |数据收集服务域名,请参考运维手册或联系技术支持获取|
|dataSourceId|true|string| |数据源 ID,见数据源配置|
|props|false|array|array()|初始化配置额外参数|

###### 初始化配置额外参数

|参数|必选|类型|默认值|说明|
|:----|:----|:----|:----|-----|
|debug|false|boolean|false|debug 模式, 此模式仅打印日志, 不发送数据|
|idMappingEnabled|false|boolean|false|是否支持设置用户类型, false, 不发送userKey. true, 发送userKey|

###### 示例

```php
$accountID = '1234567887654321';
$host = 'https://localhost.com';
$dataSourceId = '12345678';
$props = array('debug' => true);

$gio = GrowingIO::getInstance($accountID, $host, $dataSourceId, $props);
```

### 数据采集API

**1\. 采集自定义事件**

###### 接口功能

> 发送一个自定义事件。在添加所需要发送的事件代码之前,需要在事件管理用户界面配置事件以及事件级变量

###### 请求参数

|参数|必选|类型|默认值|说明|
|:----|:----|:----|:----|-----|
|eventKey|true| string | |事件名, 事件标识符|
|loginUserId|false| string |  |登录用户id，与匿名用户id不能同时为空|
|anonymousId|false| string|  |匿名用户id，与登录用户id不能同时为空|
|evnetTime|false|int|当前时间的时间戳|事件发生时间。如需要开启"自定义event_time上报"的功能开关，请联系技术支持|
|loginUserKey|false|string| |登录用户类型|
|properties|false|array|array()|事件发生时，所伴随的维度信息，1.0.3版本支持value为array|

###### 示例

```php
$gio->trackCustomEvent($gio->getCustomEventFactory()
    ->setEventKey('event_name')
    ->setLoginUserId('loginUserId')
    ->setAnonymousId('anonymousId')
    ->setEventTime(1648524854000)
    ->setLoginUserKey('loginUserKey')
    ->setProperties(array('attrKey1' => 'attrValue1',
        'attrKey2' => 'attrValue2',
        'array' => array('1', '2', '3')))
    ->create()
);
```

**2\. 设置登录用户变量**

###### 接口功能

> 以登录用户的身份定义用户属性变量,用于用户信息相关分析

###### 请求参数

|参数|必选|类型|默认值|说明|
|:----|:----|:----|:----|-----|
|loginUserId|false| string|  |登录用户id，与匿名用户id不能同时为空|
|anonymousId|false| string|  |匿名用户id，与登录用户id不能同时为空|
|properties|true|array| |用户属性信息，1.0.3版本支持value为array|
|loginUserKey|false|string| |登录用户类型|

###### 示例

```php
$gio->setUserAttributesEvent($gio->getUserAttributesFactory('loginUserId')
    ->setLoginUserId('loginUserId')
    ->setAnonymousId('anonymousId')
    ->setProperties(array('gender' => 'male',
        'age' => '18',
        'goods' => array('book', 'bag', 'lipstick')))
    ->setLoginUserKey('loginUserKey')
    ->create());
```

**3\. 设置物品模型**

###### 接口功能

> 上传物品模型

###### 请求参数

|参数|必选|类型|默认值|说明|
|:----|:----|:----|:----|-----|
|itemId|true|string| |物品模型id|
|itemKey|true|string| |物品模型key|
|properties|false|array|array()|物品模型属性信息|

###### 示例

```php
$gio->setItemAttributes(
    '1001',
    'product',
    array('color' => 'red')
);
```

### 集成示例

```php
<?php
use com\growingio\GrowingIO;

include_once 'path/src/GrowingIO.php'; // path为对应路径

// 请在您调试前,将accountID修改为您的项目AccountID
// 所有自定义事件需要提前在GrowingIO产品中进行定义
// 所有自定义事件的属性也需要提前在GrowingIO产品中进行定义
$accountID = '1234567887654321';
$host = 'https://localhost.com';
$dataSourceId = '12345678';
$props = array('debug' => true);
$gio = GrowingIO::getInstance($accountID, $host, $dataSourceId, $props);

// 采集自定义事件
$gio->trackCustomEvent($gio->getCustomEventFactory()
    ->setEventKey('event_name')
    ->setLoginUserId('loginUserId')
    ->setAnonymousId('anonymousId')
    ->setEventTime(1648524854000)
    ->setLoginUserKey('loginUserKey')
    ->setProperties(array('attrKey1' => 'attrValue1',
        'attrKey2' => 'attrValue2',
        'array' => array('1', '2', '3')))
    ->create()
);

// 设置登录用户变量
$gio->setUserAttributesEvent($gio->getUserAttributesFactory('loginUserId')
    ->setLoginUserId('loginUserId')
    ->setAnonymousId('anonymousId')
    ->setProperties(array('gender' => 'male',
        'age' => '18',
        'goods' => array('book', 'bag', 'lipstick')))
    ->setLoginUserKey('loginUserKey')
    ->create());

// 设置物品模型
$gio->setItemAttributes(
    '1001',
    'product',
    array('color' => 'red')
);
```