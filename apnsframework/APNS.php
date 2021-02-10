<?php

namespace APNSFramework;

use APNSFramework\Exception\APNSDeviceTokenInactive;
use APNSFramework\Exception\APNSException;
use Firebase\JWT\JWT;

/**
 * Apple Push Notification Service Manager
 * Class APNS
 */
class APNS {

    /**
     * @var string
     */
    private $teamId;

    /**
     * @var string
     */
    private $bundleId;

    /**
     * @var string
     */
    private $authKeyPath;

    /**
     * @var string
     */
    private $authKeyId;

    /**
     * @var string|null
     */
    private $rootCertificatePath = null;

    /**
     * @var resource|null
     */
    private $curlHandle = null;

    /**
     * @var string|null
     */
    private $apnsAuthorization = null;

    public function __construct($teamId, $bundleId, $authKeyPath, $authKeyId) {
        $this->teamId = $teamId;
        $this->bundleId = $bundleId;
        $this->authKeyPath = $authKeyPath;
        $this->authKeyId = $authKeyId;
    }

    public function __destruct() {
        if($this->curlHandle != null) {
            curl_close($this->curlHandle);
        }
    }

    /**
     * @return string
     */
    public function getTeamId(): string {
        return $this->teamId;
    }

    /**
     * @return string
     */
    public function getBundleId(): string {
        return $this->bundleId;
    }

    /**
     * @return string
     * @deprecated Use getAuthKeyPath() instead.
     */
    public function getAuthKeyUrl(): string {
        return $this->getAuthKeyPath();
    }

    /**
     * @return string
     */
    public function getAuthKeyPath(): string {
        return $this->authKeyPath;
    }

    /**
     * @return string
     */
    public function getAuthKeyId(): string {
        return $this->authKeyId;
    }

    /**
     * Setter for setting the path to the root certificate .pem file.
     * You can use this to specify a root certificate if it's not installed on the system already.
     * The HTTP/2 APNs provider API root certificate is available here:
     * https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server
     * Note: Might require an absolute path.
     * @param $rootCertificatePath string|null
     */
    public function setRootCertificatePath($rootCertificatePath) {
        $this->rootCertificatePath = $rootCertificatePath;
    }

    /**
     * @return string|null
     */
    public function getRootCertificatePath(): ?string {
        return $this->rootCertificatePath;
    }

    /**
     * @return string
     * @throws APNSException
     */
    private function getAPNSAuthorizationToken(): string {
        if($this->apnsAuthorization == null) {
            $authKey = file_get_contents($this->authKeyPath);
            if ($authKey == false) {
                throw new APNSException("Can't read auth key. Failed to read file.");
            }
            $this->apnsAuthorization = JWT::encode(["iss" => $this->teamId, "iat" => time()], $authKey, "ES256", $this->authKeyId);
        }
        return $this->apnsAuthorization;
    }

    /**
     * Send the $notification to $token.
     * @param APNSNotification $notification The notification to send.
     * @param APNSToken $token The token to send the notification to.
     * @throws APNSException If an error occurred. See the message for more info.
     * @throws APNSDeviceTokenInactive If the APNSToken is inactive and can be removed.
     */
    public function sendNotification(APNSNotification $notification, APNSToken $token): void {
        $authorization = $this->getAPNSAuthorizationToken();

        // Prepare the header.
        $header = array();
        $header[] = "content-type: application/json";
        $header[] = "authorization: bearer {$authorization}";
        $header[] = "apns-topic: {$this->bundleId}";
        $header[] = "apns-push-type: " . ($notification->getBody() != null ? "alert" : "background");
        $header[] = "apns-priority: " . $notification->getPriority();

        // Create the curl request.
        if($this->curlHandle == null) {
            $this->curlHandle = curl_init();
        }
        curl_setopt($this->curlHandle, CURLOPT_URL, $token->getTokenUrl());
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $notification->generateJSONPayload());
        curl_setopt($this->curlHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        if ($this->rootCertificatePath != null) {
            curl_setopt($this->curlHandle, CURLOPT_CAINFO, $this->rootCertificatePath);
        }

        // Execute the curl request.
        $response = curl_exec($this->curlHandle);
        $httpcode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        $chError = curl_error($this->curlHandle);
        if (!empty($chError)) {
            throw new APNSException("curl error: $chError");
        }

        if ($httpcode == 400 || $httpcode == 403 || $httpcode == 404 || $httpcode == 405 || $httpcode == 413 || $httpcode == 429 || $httpcode == 500 || $httpcode == 503) {
            $output = json_decode($response, true);
            throw new APNSException("APNs error: " . $output['reason'] . " (HTTP $httpcode)" . PHP_EOL . "See the following link for instructions: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/handling_notification_responses_from_apns");
        } else if ($httpcode == 410) {
            throw new APNSDeviceTokenInactive("The device token is inactive. It can be removed from the database until registered again. (" . $token->getToken() . ")");
        } else if ($httpcode != 200) {
            throw new APNSException("APNs error: Unhandled http status code $httpcode." . PHP_EOL . "See the following link for instructions: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/handling_notification_responses_from_apns");
        }
    }

}
