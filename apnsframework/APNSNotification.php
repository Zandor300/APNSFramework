<?php

namespace APNSFramework;

require_once "APNSToken.php";

/**
 * Apple Push Notification Service Notification
 *
 * Represents a notification that should be sent.
 *
 * Class APNSNotification
 */
class APNSNotification {

	/**
	 * @var APNSToken
	 */
	private $token;

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

}