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

This framework depends on a fork of the [firebase/php-jwt](https://github.com/firebase/php-jwt) package. I've modified it to allow me to specify a different algorithm in the token from what is actually used. firebase/php-jwt doesn't support **ES256** as an algorithm (required by APNS) but APNS does accept a token made using **RS256** as long as the `alg` header item is set to **ES256**.

The fork is still usable as firebase/php-jwt like nothing has been changed. The only thing I changed is how the `$head` variable of the `encrypt(..)` function is being merged with the header the library creates. This way we can change `alg`.

You can view the fork on GitHub: [zandor300/php-jwt-apns](https://github.com/Zandor300/php-jwt-apns)

**Note:** If you use firebase/php-jwt in your project, make sure to replace that with zandor300/php-jwt-apns!

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
