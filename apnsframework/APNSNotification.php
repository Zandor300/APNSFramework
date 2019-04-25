<?php

namespace APNSFramework;

require_once "APNSException.php";

/**
 * Apple Push Notification Service Notification
 *
 * Represents a notification that should be sent.
 *
 * Class APNSNotification
 */
class APNSNotification {

	/**
	 * The title of the notification. Apple Watch displays this string in the short look notification interface. Specify
	 * a string that is quickly understood by the user.
	 * @var string|null
	 */
	private $title = null;

	/**
	 * Additional information that explains the purpose of the notification.
	 * @var string|null
	 */
	private $subtitle = null;

	/**
	 * The content of the alert message.
	 * @var string|null
	 */
	private $body = null;

	/**
	 * The name of the launch image file to display. If the user chooses to launch your app, the contents of the
	 * specified image or storyboard file are displayed instead of your app's normal launch image.
	 * @var string|null
	 */
	private $launchImageName = null;

	/**
	 * The number that should appear at the app's icon in a badge.
	 * @var integer|null
	 */
	private $badge = null;

	/**
	 * The critical alert flag. Set to true to enable the critical alert.
	 * @var boolean
	 */
	private $isCritical = false;

	/**
	 * The sound that should be played with the notification. The specified sound file must be on the user’s device
	 * already, either in the app's bundle or in the Library/Sounds folder of the app’s container.
	 * See https://developer.apple.com/documentation/usernotifications/unnotificationsound
	 * @var string
	 */
	private $sound = "default";

	/**
	 * The volume for the critical alert’s sound. Set this to a value between 0.0 (silent) and 1.0 (full volume).
	 * Only used if $isCritical == true.
	 * @var float|null
	 */
	private $soundVolume = null;

	/**
	 * Identifies the notification’s type, and is used to add action buttons to the alert. To use this, make sure you've
	 * declared a corresponding UNNotificationCategory in your app.
	 * See https://developer.apple.com/documentation/usernotifications/declaring_your_actionable_notification_types
	 * @var string|null
	 */
	private $category = null;

	/**
	 * An app-specific identifier for grouping related notifications. This value corresponds to the threadIdentifier
	 * property in the UNNotificationContent object.
	 * @var string|null
	 */
	private $threadId = null;

	/**
	 * Any additional data you would want to pass with the notification.
	 * These can be retrieved through the userInfo variable in the UNNotificationContent.
	 * See https://developer.apple.com/documentation/usernotifications/unnotificationcontent/1649869-userinfo
	 * @var array dictionary
	 */
	private $data = array();

	/**
	 * The notification service app extension flag. If the value is true, the system passes the notification to your
	 * notification service app extension before delivery. Use your extension to modify the notification’s content.
	 * See https://developer.apple.com/documentation/usernotifications/modifying_content_in_newly_delivered_notifications
	 * @var boolean
	 */
	private $isMutable = false;

	public function generateJSONPayload(): string {
		$payload = array();

		$payload['aps'] = array();

		if($this->body != null) {
			$payload['aps']['alert'] = array();
			$payload['aps']['alert']['body'] = $this->body;
			if($this->title != null) {
				$payload['aps']['alert']['title'] = $this->title;
			}
			if($this->subtitle != null) {
				$payload['aps']['alert']['subtitle'] = $this->subtitle;
			}
			if($this->launchImageName != null) {
				$payload['aps']['alert']['launch-image'] = $this->launchImageName;
			}
		}
		if($this->badge != null) {
			$payload['aps']['badge'] = $this->badge;
		}
		if(!$this->isCritical) {
			$payload['aps']['sound'] = $this->sound;
		} else {
			$payload['aps']['sound'] = array();
			$payload['aps']['sound']['critical'] = 1;
			$payload['aps']['sound']['name'] = $this->sound;
			if($this->soundVolume != null) {
				$payload['aps']['sound']['volume'] = $this->soundVolume;
			}
		}
		if($this->threadId != null) {
			$payload['aps']['thread-id'] = $this->threadId;
		}
		if($this->category != null) {
			$payload['aps']['category'] = $this->category;
		}
		if($this->isMutable) {
			$payload['aps']['mutable-content'] = 1;
		}

		foreach($this->data as $key => $value) {
			$payload[$key] = $value;
		}

		return json_encode($payload);
	}

	/**
	 * The title of the notification. Apple Watch displays this string in the short look notification interface. Specify
	 * a string that is quickly understood by the user.
	 * @return string|null The title. Null means it won't be used and won't show up in the alert.
	 */
	public function getTitle(): ?string {
		return $this->title;
	}

	/**
	 * Set the title of the notification. Apple Watch displays this string in the short look notification interface.
	 * Specify a string that is quickly understood by the user.
	 * Requires the body of the notification to be set!
	 * @param string|null $title The title. Null means it won't be used and won't show up in the alert.
	 */
	public function setTitle(?string $title): void {
		$this->title = $title;
	}

	/**
	 * Additional information that explains the purpose of the notification.
	 * @return string|null The subtitle. Null means it won't be used and won't show up in the alert.
	 */
	public function getSubtitle(): ?string {
		return $this->subtitle;
	}

	/**
	 * Additional information that explains the purpose of the notification.
	 * Requires the body of the notification to be set!
	 * @param string|null $subtitle The subtitle. Null means it won't be used and won't show up in the alert.
	 */
	public function setSubtitle(?string $subtitle): void {
		$this->subtitle = $subtitle;
	}

	/**
	 * The content of the alert message.
	 * @return string|null The alert message content. Null means the notification won't display to the user.
	 */
	public function getBody(): ?string {
		return $this->body;
	}

	/**
	 * Set the content of the alert message.
	 * When this is null, the notification won't have an alert that shows up on the users screen. You can use this for
	 * example for just sending a badge update.
	 * @param string|null $body The alert message content. When set to null, the notification won't display to the user.
	 */
	public function setBody(?string $body): void {
		$this->body = $body;
	}

	/**
	 * The name of the launch image file to display. If the user chooses to launch your app, the contents of the
	 * specified image or storyboard file are displayed instead of your app's normal launch image.
	 * @return string|null The nameof the launch image file to display. Null means this won't be specified when sending this notification.
	 */
	public function getLaunchImageName(): ?string {
		return $this->launchImageName;
	}

	/**
	 * Set the name of the launch image file to display. If the user chooses to launch your app, the contents of the
	 * specified image or storyboard file are displayed instead of your app's normal launch image.
	 * @param string|null $launchImageName The nameof the launch image file to display. Null means this won't be specified when sending this notification.
	 */
	public function setLaunchImageName(?string $launchImageName): void {
		$this->launchImageName = $launchImageName;
	}

	/**
	 * The number that should appear at the app's icon in a badge.
	 * @return int|null
	 */
	public function getBadge(): ?int {
		return $this->badge;
	}

	/**
	 * Set the number that should appear at the app's icon in a badge.
	 * @param int|null $badge
	 */
	public function setBadge(?int $badge): void {
		$this->badge = $badge;
	}

	/**
	 * Whether this notification is a critical notification.
	 * @return bool
	 */
	public function isCritical(): bool {
		return $this->isCritical;
	}

	/**
	 * Set the critical alert flag. Set to true to make this notification a critical one.
	 * Requires you to have access to the critical alerts entitlement for you app.
	 * See https://developer.apple.com/contact/request/notifications-critical-alerts-entitlement/
	 * Available since iOS 12.
	 * @param bool $isCritical
	 */
	public function setCritical(bool $isCritical): void {
		$this->isCritical = $isCritical;
	}

	/**
	 * The sound that should be played with the notification. The specified sound file must be on the user’s device
	 * already, either in the app's bundle or in the Library/Sounds folder of the app’s container.
	 * @return string
	 */
	public function getSound(): string {
		return $this->sound;
	}

	/**
	 * Set the sound that should be played with the notification. The specified sound file must be on the user’s device
	 * already, either in the app's bundle or in the Library/Sounds folder of the app’s container.
	 * @param string $sound
	 */
	public function setSound(string $sound): void {
		$this->sound = $sound;
	}

	/**
	 * Returns the volume for the critical alert's sound.
	 * @return float|null Value from 0.0 to 1.0. Null means this won't be specified when sending the notification.
	 */
	public function getSoundVolume(): ?float {
		return $this->soundVolume;
	}

	/**
	 * The volume for the critical alert’s sound. Set this to a value between 0.0 (silent) and 1.0 (full volume).
	 * Only used if the notification is critical
	 * Available since iOS 12.
	 * @param float|null $soundVolume The volume from 0.0 to 1.0. Use null to not specify this when sending the notification.
	 * @throws APNSException Trows when the current notification isn't critical when this is executed.
	 */
	public function setSoundVolume(?float $soundVolume): void {
		if(!$this->isCritical) {
			throw new APNSException("Can't set sound volume on non-critical notifications. If this should be a critical notification, execute setCritical(true) before this.");
		}
		$this->soundVolume = $soundVolume;
	}

	/**
	 * Identifies the notification’s type, and is used to add action buttons to the alert. To use this, make sure you've
	 * declared a corresponding UNNotificationCategory in your app.
	 * See https://developer.apple.com/documentation/usernotifications/declaring_your_actionable_notification_types
	 * @return string|null
	 */
	public function getCategory(): ?string {
		return $this->category;
	}

	/**
	 * Identifies the notification’s type, and is used to add action buttons to the alert. To use this, make sure you've
	 * declared a corresponding UNNotificationCategory in your app.
	 * See https://developer.apple.com/documentation/usernotifications/declaring_your_actionable_notification_types
	 * @param string|null $category
	 */
	public function setCategory(?string $category): void {
		$this->category = $category;
	}

	/**
	 * An app-specific identifier for grouping related notifications. This value corresponds to the threadIdentifier
	 * property in the UNNotificationContent object.
	 * @return string|null
	 */
	public function getThreadId(): ?string {
		return $this->threadId;
	}

	/**
	 * An app-specific identifier for grouping related notifications. This value corresponds to the threadIdentifier
	 * property in the UNNotificationContent object.
	 * @param string|null $threadId
	 */
	public function setThreadId(?string $threadId): void {
		$this->threadId = $threadId;
	}

	/**
	 * Key-value pairs of additional data that will be sent with the notification.
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * Add any additional data you would want to pass with the notification.
	 * These can be retrieved through the userInfo variable in the UNNotificationContent.
	 * See https://developer.apple.com/documentation/usernotifications/unnotificationcontent/1649869-userinfo
	 * @param string $key The key that will be used when passing the additional data.
	 * @param string $value The value that will be passed with the notification.
	 * @throws APNSException Throws when an invalid or reserved $key is used.
	 */
	public function addData(string $key, string $value): void {
		if(strtolower($key) == "aps") {
			throw new APNSException("$key can't be used as a key for the additional data. This key is used for the notification data itself. Please use a different key.");
		}
		$this->data[$key] = $value;
	}

	/**
	 * The notification service app extension flag. If the value is true, the system passes the notification to your
	 * notification service app extension before delivery. Use your extension to modify the notification’s content.
	 * See https://developer.apple.com/documentation/usernotifications/modifying_content_in_newly_delivered_notifications
	 * @return bool
	 */
	public function isMutable(): bool {
		return $this->isMutable;
	}

	/**
	 * The notification service app extension flag. If the value is true, the system passes the notification to your
	 * notification service app extension before delivery. Use your extension to modify the notification’s content.
	 * See https://developer.apple.com/documentation/usernotifications/modifying_content_in_newly_delivered_notifications
	 * @param bool $isMutable
	 */
	public function setMutable(bool $isMutable): void {
		$this->isMutable = $isMutable;
	}

}