# APNSFramework

[![Build](https://img.shields.io/gitlab/pipeline/Zandor300/apnsframework.svg?gitlab_url=https%3A%2F%2Fgit.zsinfo.nl)](https://git.zsinfo.nl/Zandor300/apnsframework/pipelines)
[![Version](https://img.shields.io/packagist/v/zandor300/apnsframework.svg)](https://packagist.org/packages/zandor300/apnsframework)
[![License](https://img.shields.io/packagist/l/zandor300/apnsframework.svg)](https://git.zsinfo.nl/Zandor300/apnsframework/blob/master/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/zandor300/apnsframework.svg)](https://packagist.org/packages/zandor300/apnsframework)
[![Downloads](https://img.shields.io/packagist/dt/zandor300/apnsframework.svg)](https://packagist.org/packages/zandor300/apnsframework)

PHP framework for easy interaction with the Apple Push Notification Service.

## Install

You can use composer to include this framework into your project. The project is available through [Packagist](https://packagist.org/packages/zandor300/apnsframework).

```shell
composer require zandor300/apnsframework
```

## Dependencies

This framework depends on the [firebase/php-jwt](https://github.com/firebase/php-jwt) package.

## Usage

### Creating APNS object.
```php
use APNSFramework;

$teamId = "";
$bundleId = "";
$authKeyPath = "";
$authKeyId = "";

try {
    $apns = new APNS($teamId, $bundleId, $authKeyPath, $authKeyId);
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
} catch (APNSDeviceTokenInactive $e) {
    // Remove device token from database since it's inactive.
} catch (APNSException $e) {
    echo "Error: " . $e->getMessage();
    // Handle exception
}
```
