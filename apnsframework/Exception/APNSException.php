<?php

namespace APNSFramework\Exception;

use Exception;
use Throwable;

/**
 * Class APNSException
 * Exception used by APNSFramework for all exceptions. Other exceptions in the framework inherit this one.
 * @package APNSFramework
 */
class APNSException extends Exception {

    /**
     * The HTTP status code that APNs responded with. 0 when there was no HTTP response at all.
     * @var int
     */
    private $statusCode = 0;

    /**
     * The `reason` field of the APNs response body. Null when APNs didn't return a reason.
     * See https://developer.apple.com/documentation/usernotifications/handling-notification-responses-from-apns
     * @var string|null
     */
    private $reason = null;

    /**
     * The token the request was sent to. Note that this can be an ActivityKit token instead of a device token.
     * @var string|null
     */
    private $token = null;

    /**
     * APNSException constructor.
     * @param string $message The message of the exception.
     * @param int $statusCode The HTTP status code that APNs responded with. 0 when there was no HTTP response.
     * @param string|null $reason The `reason` field of the APNs response body.
     * @param string|null $token The token the request was sent to.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string $message = "", int $statusCode = 0, ?string $reason = null, ?string $token = null, ?Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->reason = $reason;
        $this->token = $token;
    }

    /**
     * The HTTP status code that APNs responded with. 0 when there was no HTTP response at all.
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * The `reason` field of the APNs response body, for example "BadDeviceToken". Null when APNs didn't return a
     * reason or when there was no HTTP response at all.
     * See https://developer.apple.com/documentation/usernotifications/handling-notification-responses-from-apns
     * @return string|null
     */
    public function getReason(): ?string {
        return $this->reason;
    }

    /**
     * The token the request was sent to. Note that this can be an ActivityKit push-to-start or update token instead of
     * a device token. Not included in the message of the exception so it doesn't end up in error reporting.
     * @return string|null
     */
    public function getToken(): ?string {
        return $this->token;
    }

}
