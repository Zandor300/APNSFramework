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

## Live Activities

Live Activities are pushed with an `APNSLiveActivityNotification` instead of an `APNSNotification`. Both implement
`APNSNotificationInterface`, so they're sent through the same `sendNotification` method. The framework automatically
sends the `apns-push-type: liveactivity` header and appends `.push-type.liveactivity` to your bundle id for the
`apns-topic` header.

See [Apple's documentation](https://developer.apple.com/documentation/ActivityKit/starting-and-updating-live-activities-with-activitykit-push-notifications)
for the contract these pushes have to follow.

### Tokens

The token you send a Live Activity push to is **not** the device token. It's the ActivityKit push-to-start token (for
the `start` event) or the update token of the running Live Activity (for the `update` and `end` events). Your app gets
those from `Activity.pushToStartTokenUpdates` and `Activity.pushTokenUpdates`.

Both are hex strings, so you use the regular `APNSToken` object for them:

```php
$token = new APNSToken($pushToStartToken, APNSTokenEnvironment::production);
```

### Starting a Live Activity

The `start` event requires an attributes type, attributes and an alert. The content state and attributes have to match
the `ContentState` and the `ActivityAttributes` implementation in your app.

```php
$notification = new APNSLiveActivityNotification(APNSLiveActivityEvent::start);

$notification->setContentState([
    "periodStartTimestamp" => 1770003600.0,
    "periodEndTimestamp" => 1770007200.0,
]);

$notification->setAttributesType("MyLiveActivityAttributes");
$notification->setAttributes(["installationName" => "Home"]);

$notification->setAlert("Cheapest period", "Your cheapest period starts soon.");

$apns->sendNotification($notification, $pushToStartToken);
```

Optionally you can set `setStaleDate()`, `setRelevanceScore()` and `setPriority()`.

If you no longer need to support remote starts on iOS 17.2 up to iOS 18, you can call `setInputPushToken(true)` to have
ActivityKit send the update token of the started Live Activity back to your app. This is off by default because devices
below iOS 18 reject a payload containing that key.

### Updating a Live Activity

The `update` event requires the content state. An alert is optional and is shown when the Live Activity isn't visible
on the device. Attributes and an attributes type are not allowed.

```php
$notification = new APNSLiveActivityNotification(APNSLiveActivityEvent::update);

$notification->setContentState([
    "periodStartTimestamp" => 1770003600.0,
    "periodEndTimestamp" => 1770007200.0,
]);

$apns->sendNotification($notification, $updateToken);
```

### Ending a Live Activity

The `end` event requires the content state that should be displayed after the activity ended. An alert is not allowed.
Use `setDismissalDate()` to control when the system removes the activity from the Lock Screen and the Dynamic Island.

```php
$notification = new APNSLiveActivityNotification(APNSLiveActivityEvent::end);

$notification->setContentState([
    "periodStartTimestamp" => 1770003600.0,
    "periodEndTimestamp" => 1770007200.0,
]);
$notification->setDismissalDate(time());

$apns->sendNotification($notification, $updateToken);
```

## Handling errors

All exceptions inherit `APNSException`, which exposes the APNs response:

- `getStatusCode()`: The HTTP status code APNs responded with. `0` when there was no HTTP response at all.
- `getReason()`: The `reason` field of the APNs response body, for example `"BadDeviceToken"`. `null` when APNs didn't
  return a reason. See [Apple's documentation](https://developer.apple.com/documentation/usernotifications/handling-notification-responses-from-apns)
  for the possible values.
- `getToken()`: The token the request was sent to. Not included in the message of the exception, so it doesn't end up
  in your error reporting.

Two subclasses let you handle the common cases without inspecting the status code:

- `APNSTransportException`: The request failed before a response was received, for example on a connection error. It is
  **unknown** whether Apple accepted the notification. Never retry a Live Activity `start` after this, since that can
  result in a second remotely started Live Activity on the device.
- `APNSDeviceTokenInactive`: The token the notification was sent to is no longer registered (HTTP 410) and can be
  removed from your database. For a Live Activity push this is the ActivityKit token that was passed in, so only that
  token should be invalidated and not necessarily the device token.

```php
try {
    $apns->sendNotification($notification, $token);
} catch (APNSTransportException $e) {
    // Uncertain whether the notification was delivered. Don't retry a Live Activity start.
} catch (APNSDeviceTokenInactive $e) {
    // Invalidate the token that was sent to.
} catch (APNSException $e) {
    switch ($e->getReason()) {
        case "BadDeviceToken":
        case "DeviceTokenNotForTopic":
        case "Unregistered":
        case "TopicDisallowed":
        case "BadTopic":
            // Invalidate the token that was sent to.
            break;
        case "InvalidProviderToken":
        case "ExpiredProviderToken":
        case "MissingProviderToken":
            // Configuration error, report this.
            break;
        default:
            if (in_array($e->getStatusCode(), [429, 500, 503])) {
                // Retryable.
            }
            break;
    }
}
```

## Troubleshooting

### Certificate errors

You might need to set the root certificate for the APNS request using `setRootCertificatePath` on the `APNS` object.
You can download this certificate using the link in the Apple Developer documentation:
[`Establish a Trusted Connection to APNs`](https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server#2943333) > `AAA Certificate Services root certificate`

```php
$apns->setRootCertificatePath(__DIR__ . "/AAACertificateServices.crt");
```
