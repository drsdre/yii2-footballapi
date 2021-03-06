Yii2-xmlsoccer
=================

Yii2 client for [Football API](http://football-api.com) API

Full API Documentation here: [http://football-api.com/documentation/](http://football-api.com/documentation/)

Requirements:
=================

PHP5 with CURL. SimpleXML extensions only needed if XML is chosen as output.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist drsdre/yii2-footballapi "*"
```

or add

```json
"drsdre/yii2-footballapi": "*"
```

to the `require` section of your `composer.json` file.

Usage
-----

You can either setup the client as an application component:

```php
'components' => [
    'footballapiApi' => [
        'class' => '\FootballAPI\Client',
        'api_key' => 'xxx',
    ]
    ...
]
```

or use the client directly in your code:

```php
$client = new \FootballAPI\Client([
    'api_key' => 'xxx',
]);
```

Configuration
-----

The output format of the the API calls can be changed by adding a output type parameter:

```php
'components' => [
    'footballApi' => [
        'class' => '\FootballAPI\Client',
        'api_key' => 'xxx',
        'output_type' => 'PHP',
    ]
    ...
]
```

Default value: JSON. Options include: XML, JSON, PHPARRAY, PHPOBJECT, LINE, CONSOLE, VAR.


Optionally a cache component can be added for example to keep the client returning data during a time-out:

```php
'components' => [
    'footballApiCache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'footballApi' => [
        'class' => '\FootballAPI\Client',
        'api_key' => 'xxx',
        'cache' => 'footballApiCache',
    ]
    ...
]
```

To facilitate a check to detect data changes, a content hash can be generated by setting the parameter 'generate_hash' 
to true (currently only supported in XML, PHPARRAY & PHPOBJECT output type). The output will then include two new attributes:

*  contentHash: MD5 hash
*  sourceURL: URL used to retrieve the data


If you need to have the API be executed via a specific network adapter it's possible the specify the outgoing IP:

```php
$client = new \FootballAPI\Client([
    'api_key' => 'xxx',
    'service_ip' => '192.168.1.1',
]);
```

How to use API:
=================

Go to [Sign up for Free](http://football-api.com/account/membership-checkout/?level=4) and retrieve the API key for 
access to the football-api.com API.

Methods Available
-------------------

Go to [http://football-api.com/documentation/](http://football-api.com/documentation/) for more info about methods and 
parameters including online testing.

Examples:
==================

List competitions
--------------------------------
	try {
		$client = new \FootballAPI\Client([
            'api_key' => 'xxx',
        ]);
		$competitions=json_decode($soccer->competitions());
		echo "Competitions List:<br>";
		foreach($competitions as $competition){
			echo "<b>".$competition->name."</b> ".$competition->region."<br>";
		}
	}
	catch(Exception $e) {
		echo "FootballAPI Exception: ".$e->getMessage();
	}

If your server has multiple IP's available, you can set any IP for request:
---------------------------------------------
	try {
		$client = new \FootballAPI\Client([
            'api_key' => 'xxx',
        ]);
		$soccer->setRequestIp("ip_for_request");
		$result=json_decode($soccer->standings(["comp_id"=>1064]));
		var_dump($result);
	}
	catch(Exception $e) {
		echo "FootballAPI: ".$e->getMessage();
	}


That's all!
-----------