<?php

namespace APNSFramework;

use APNSFramework\Exception\APNSException;

/**
 * Apple Push Notification Service Notification Interface
 *
 * Implemented by every notification type that can be passed to APNS::sendNotification().
 *
 * Interface APNSNotificationInterface
 * @package APNSFramework
 */
interface APNSNotificationInterface {

    /**
     * The JSON payload that will be sent as the body of the APNs request.
     * @return string
     * @throws APNSException Throws when the notification is not valid and no payload can be generated.
     */
    public function generateJSONPayload(): string;

    /**
     * The value that will be sent in the `apns-push-type` header.
     * See APNSPushType for the possible values.
     * @return string
     */
    public function getPushType(): string;

    /**
     * The suffix that will be appended to the bundle id to form the `apns-topic` header.
     * Return null to use the plain bundle id.
     * @return string|null
     */
    public function getTopicSuffix(): ?string;

    /**
     * The value that will be sent in the `apns-priority` header.
     * @return int
     */
    public function getPriority(): int;

}
