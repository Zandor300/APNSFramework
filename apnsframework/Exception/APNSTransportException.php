<?php

namespace APNSFramework\Exception;

/**
 * Class APNSTransportException
 * This exception is thrown when the request to APNs failed before a response was received, for example on a connection
 * or TLS error. Unlike the other exceptions this means it is unknown whether Apple accepted the notification or not.
 *
 * Because of that, a Live Activity `start` push should never be retried after this exception. Retrying can result in a
 * second remotely started Live Activity on the device.
 * @package APNSFramework
 */
class APNSTransportException extends APNSException {
}
