<?php

namespace APNSFramework;

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

    public function __construct($teamId, $bundleId, $authKeyPath, $authKeyId) {
        $this->teamId = $teamId;
        $this->bundleId = $bundleId;
        $this->authKeyPath = $authKeyPath;
        $this->authKeyId = $authKeyId;
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
     * You can use this to specify a root certificate if it's not whitelisted on the system.
     * The HTTP/2 APNs provider API root certificate is available here: https://developer.apple.com/library/archive/technotes/tn2265/_index.html#//apple_ref/doc/uid/DTS40010376-CH1-TNTAG31
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
     * Send the $notification to $token.
     * @param APNSNotification $notification The notification to send.
     * @param APNSToken $token The token to send the notification to.
     * @throws APNSException If an error occurred. See the message for more info.
     * @throws APNSDeviceTokenInactive If the APNSToken is inactive and can be removed.
     */
    public function sendNotification(APNSNotification $notification, APNSToken $token): void {
        $authKey = file_get_contents($this->authKeyPath);
        if ($authKey == false) {
            throw new APNSException("Can't read auth key. Failed to read file.");
        }

        $authorization = JWT::encode(['iss' => $this->teamId, 'iat' => time()], $authKey, 'ES256', $this->authKeyId);

        // Prepare the header.
        $header = array();
        $header[] = "content-type: application/json";
        $header[] = "authorization: bearer {$authorization}";
        $header[] = "apns-topic: {$this->bundleId}";
        $header[] = "apns-push-type: " . ($notification->getBody() != null ? "alert" : "background");
        $header[] = "apns-priority: " . $notification->getPriority();

        // Create the curl request.
        $ch = curl_init($token->getTokenUrl());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $notification->generateJSONPayload());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->rootCertificatePath != null) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->rootCertificatePath);
        }

        // Execute the curl request.
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $chError = curl_error($ch);
        curl_close($ch);
        if (!empty($chError)) {
            throw new APNSException("curl error: $chError");
        }

        if ($httpcode == 400 || $httpcode == 403 || $httpcode == 404 || $httpcode == 405 || $httpcode == 413 || $httpcode == 429 || $httpcode == 500 || $httpcode == 503) {
            $output = json_decode($response, true);
            throw new APNSException("APNs error: " . $output['reason'] . PHP_EOL . "See the following link for instructions: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/handling_notification_responses_from_apns");
        } else if ($httpcode == 410) {
            throw new APNSDeviceTokenInactive("The device token is inactive. It can be removed from the database until registered again. (" . $token->getToken() . ")");
        } else if ($httpcode != 200) {
            throw new APNSException("APNs error: Unhandled http status code $httpcode." . PHP_EOL . "See the following link for instructions: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/handling_notification_responses_from_apns");
        }
    }

}
