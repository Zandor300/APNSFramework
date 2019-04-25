<?php

namespace APNSFramework;

/**
 * Apple Push Notification Service Token
 * Class APNSToken
 */
class APNSToken {

	/**
	 * The actual token.
	 * @var string
	 */
	private $token;

	/**
	 * The environment the token is valid for.
	 * @var integer
	 */
	private $environment;

	/**
	 * APNToken constructor.
	 * @param $token string The actual token.
	 * @param $environment integer The environment the token is valid for.
	 */
	function __construct($token, $environment) {
		$this->token = $token;
		$this->environment = $environment;
	}

	/**
	 * @return string
	 */
	public function getToken(): string {
		return $this->token;
	}

	/**
	 * @return int
	 */
	public function getEnvironment(): int {
		return $this->environment;
	}

}
