<?php

namespace APNSFramework;

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


}
