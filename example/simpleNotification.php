<?php

use APNSFramework\APNS;
use APNSFramework\APNSException;
use APNSFramework\APNSNotification;
use APNSFramework\APNSToken;
use APNSFramework\APNSTokenEnvironment;

require_once "../vendor/autoload.php";

try {
    // Create APNS object
    $apns = new APNS("teamId", "bundleId", "authKeyUrl", "authKeyId");

    // Create notification
    $notification = new APNSNotification();

    $notification->setTitle("Example Notification");
    $notification->setBody("This is an example notification!");

    $notification->setBadge(2);

    // Create token object
    $tokenString = "MY_DEVICE_APNS_TOKEN";
    $environment = APNSTokenEnvironment::development;
    //$environment = APNSTokenEnvironment::production;
    $token = new APNSToken($tokenString, $environment);

    // Send the notification
    $apns->sendNotification($notification, $token);

    echo "Notification sent." . PHP_EOL;
} catch (APNSException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    // Handle exception
}
