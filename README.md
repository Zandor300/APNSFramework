# APNSFramework

PHP framework for easy interaction with the Apple Push Notification Service.

## Usage

### Creating APNS object.
```php
use APNSFramework;

$teamId = "";
$bundleId = "";
$authKeyUrl = "";
$authKeyId = "";

$apns = new APNS($teamId, $bundleId, $authKeyUrl, $authKeyId);
```
