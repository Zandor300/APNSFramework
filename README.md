# APNSFramework

PHP framework for easy interaction with the Apple Push Notification Service.

## Install

You can use composer to include this framework into your project. The project is available through [Packagist](https://packagist.org/packages/zandor300/apnsframework).

```shell
composer require zandor300/apnsframework
```

## Usage

### Creating APNS object.
```php
use APNSFramework;

$teamId = "";
$bundleId = "";
$authKeyUrl = "";
$authKeyId = "";

try {
    $apns = new APNS($teamId, $bundleId, $authKeyUrl, $authKeyId);
} catch (APNSException $e) {
    echo "Error: " . $e->getMessage();
    // Handle exception
}
```

### Creating a basic notification

```php
$notification = new APNSNotification();

$notification->setTitle("Example Notification");
$notification->setBody("This is an example notification!");

$notification->setBadge(2);
```

### Creating a token object

```php
try {
    $tokenString = "<FILL IN THE APNS TOKEN>";

    $environment = APNSTokenEnvironment::development;
    //$environment = APNSTokenEnvironment::production;

    $token = new APNSToken($tokenString, $environment);
} catch (APNSException $e) {
    echo "Error: " . $e->getMessage();
    // Handle exception
}
```

### Sending the notification

```php
try {
    $apns->sendNotification($notification, $token);
} catch (APNSException $e) {
    echo "Error: " . $e->getMessage();
    // Handle exception
}
```
