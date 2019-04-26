<?php

use APNSFramework;

require_once "vendor/autoload.php";

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

    echo "Notification sent.";
} catch (APNSException $e) {
    echo "Error: " . $e->getMessage();
    // Handle exception
}
