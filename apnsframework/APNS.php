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
	private $authKeyUrl;

	/**
	 * @var string
	 */
	private $authKeyId;

	public function __construct($teamId, $bundleId, $authKeyUrl, $authKeyId) {
		$this->teamId = $teamId;
		$this->bundleId = $bundleId;
		$this->authKeyUrl = $authKeyUrl;
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
	 */
	public function getAuthKeyUrl(): string {
		return $this->authKeyUrl;
	}

	/**
	 * @return string
	 */
	public function getAuthKeyId(): string {
		return $this->authKeyId;
	}

	/**
	 * Send the $notification to $token.
	 * @param APNSNotification $notification The notification to send.
	 * @param APNSToken $token The token to send the notification to.
	 * @throws APNSException
	 */
	public function sendNotification(APNSNotification $notification, APNSToken $token): void {
		$authKey = file_get_contents($this->authKeyUrl);
		if($authKey == false) {
			throw new APNSException("Can't read auth key. Failed to read file.");
		}

		$authorization = JWT::encode(['iss' => $this->teamId, 'iat' => time()], $authKey, 'ES256', $this->authKeyId);

		// Prepare the header.
		$header = array();
		$header[] = "content-type: application/json";
		$header[] = "authorization: bearer {$authorization}";
		$header[] = "apns-topic: {$this->bundleId}";

		// Create the curl request.
		$ch = curl_init($token->getTokenUrl());
		curl_setopt($ch, CURLOPT_POSTFIELDS, $notification->generateJSONPayload());
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		// Execute the curl request.
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$chError = curl_error($ch);
		if (!empty($chError)) {
			throw new APNSException("curl error: $chError");
		}

		curl_close($ch);
	}


}
