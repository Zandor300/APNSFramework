<?php

use APNSFramework\APNS;
use APNSFramework\APNSLiveActivityEvent;
use APNSFramework\APNSLiveActivityNotification;
use APNSFramework\APNSToken;
use APNSFramework\APNSTokenEnvironment;
use APNSFramework\Exception\APNSDeviceTokenInactive;
use APNSFramework\Exception\APNSException;
use APNSFramework\Exception\APNSTransportException;

require_once "../vendor/autoload.php";

try {
    // Create APNS object
    $apns = new APNS("teamId", "bundleId", "authKeyPath", "authKeyId");

    // Start a new Live Activity. The token used below has to be the ActivityKit push-to-start token.
    $notification = new APNSLiveActivityNotification(APNSLiveActivityEvent::start);

    // The content state has to match the ContentState of your ActivityAttributes implementation in your app.
    $notification->setContentState([
        "periodStartTimestamp" => 1770003600.0,
        "periodEndTimestamp" => 1770007200.0,
        "averagePriceEuroPerKWh" => -0.08,
    ]);

    // The attributes type and attributes have to match your ActivityAttributes implementation in your app.
    // These are only allowed on the start event.
    $notification->setAttributesType("MyLiveActivityAttributes");
    $notification->setAttributes(["installationName" => "Home"]);

    // An alert is required on the start event.
    $notification->setAlert("Cheapest period", "Your cheapest period starts soon.");

    // Create token object. This is the ActivityKit push-to-start token, not the device token.
    $tokenString = "MY_ACTIVITYKIT_PUSH_TO_START_TOKEN";
    $environment = APNSTokenEnvironment::development;
    //$environment = APNSTokenEnvironment::production;
    $token = new APNSToken($tokenString, $environment);

    // Send the notification
    $apns->sendNotification($notification, $token);

    echo "Live Activity start sent." . PHP_EOL;

    // Update the Live Activity. This requires the update token of the running activity.
    $update = new APNSLiveActivityNotification(APNSLiveActivityEvent::update);
    $update->setContentState([
        "periodStartTimestamp" => 1770003600.0,
        "periodEndTimestamp" => 1770007200.0,
        "averagePriceEuroPerKWh" => -0.12,
    ]);
    $update->setStaleDate(1770007200);

    $updateToken = new APNSToken("MY_ACTIVITYKIT_UPDATE_TOKEN", $environment);
    $apns->sendNotification($update, $updateToken);

    echo "Live Activity update sent." . PHP_EOL;

    // End the Live Activity. An end event can't have an alert or attributes.
    $end = new APNSLiveActivityNotification(APNSLiveActivityEvent::end);
    $end->setContentState([
        "periodStartTimestamp" => 1770003600.0,
        "periodEndTimestamp" => 1770007200.0,
        "averagePriceEuroPerKWh" => -0.12,
    ]);
    $end->setDismissalDate(time());

    $apns->sendNotification($end, $updateToken);

    echo "Live Activity end sent." . PHP_EOL;
} catch (APNSTransportException $e) {
    // No response was received, so it's unknown whether Apple accepted the notification.
    // Never retry a start event after this, it could start a second Live Activity on the device.
    echo "Transport error: " . $e->getMessage() . PHP_EOL;
} catch (APNSDeviceTokenInactive $e) {
    // The ActivityKit token that was passed in is no longer valid and should be invalidated.
    echo "Token inactive: " . $e->getToken() . PHP_EOL;
} catch (APNSException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Status code: " . $e->getStatusCode() . PHP_EOL;
    echo "Reason: " . ($e->getReason() !== null ? $e->getReason() : "unknown") . PHP_EOL;
    // Handle exception
}
