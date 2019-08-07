<?php

namespace APNSFramework;

require_once "APNSTokenEnvironment.php";

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
	 * @var string
	 */
	private $environment;

	/**
	 * APNToken constructor.
	 * @param $token string The actual token.
	 * @param $environment string The environment the token is valid for.
	 * @throws APNSException Throws when parameters are invalid.
	 */
	function __construct(string $token, string $environment) {
		if (!preg_match("~^[a-f0-9]{64,}$~i", $token)) {
			throw new APNSException("Invalid token specified.");
		}
		if($environment != APNSTokenEnvironment::development && $environment != APNSTokenEnvironment::production) {
			throw new APNSException("Invalid environment specified. Environment given: $environment");
		}
		$this->token = $token;
		$this->environment = $environment;
	}

	/**
	 * The actual token.
	 * @return string
	 */
	public function getToken(): string {
		return $this->token;
	}

	/**
	 * The environment the token is valid for.
	 * @return string
	 */
	public function getEnvironment(): string {
		return $this->environment;
	}

	public function getTokenUrl(): string {
		return $this->environment . "/" . $this->token;
	}

}
