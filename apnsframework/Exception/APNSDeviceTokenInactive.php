<?php

namespace APNSFramework\Exception;

/**
 * Class APNSDeviceTokenInactive
 * This exception is thrown when the APNs device token is no longer registered. This means the code can be removed from
 * the database. Once a new device or the same device comes back online, it should be reregistered with your backend
 * with the devices current token (which could be the same token or a different token).
 * @package APNSFramework
 */
class APNSDeviceTokenInactive extends APNSException {
}
