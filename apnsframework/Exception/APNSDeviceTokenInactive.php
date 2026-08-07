<?php

namespace APNSFramework\Exception;

/**
 * Class APNSDeviceTokenInactive
 * This exception is thrown when the APNs token that the notification was sent to is no longer registered. This means
 * the token can be removed from the database. Once a new device or the same device comes back online, it should be
 * reregistered with your backend with the devices current token (which could be the same token or a different token).
 *
 * Note that the token that is no longer registered is the token the notification was sent to, which isn't necessarily
 * a device token. For a Live Activity send it is the ActivityKit push-to-start or update token that was passed in, and
 * only that token should be invalidated. Use getToken() to get the token the request was sent to.
 * @package APNSFramework
 */
class APNSDeviceTokenInactive extends APNSException {
}
