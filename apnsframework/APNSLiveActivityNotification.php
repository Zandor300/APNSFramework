<?php

namespace APNSFramework;

use APNSFramework\Exception\APNSException;

/**
 * Apple Push Notification Service Live Activity Notification
 *
 * Represents an ActivityKit Live Activity push that should be sent. Supports the `start`, `update` and `end` events.
 * See https://developer.apple.com/documentation/ActivityKit/starting-and-updating-live-activities-with-activitykit-push-notifications
 *
 * Note that the APNSToken used to send this notification is not the device token but the ActivityKit push-to-start
 * token (for the `start` event) or the update token of the running activity (for the `update` and `end` events).
 *
 * Class APNSLiveActivityNotification
 * @package APNSFramework
 */
class APNSLiveActivityNotification implements APNSNotificationInterface {

    /**
     * The topic suffix that is appended to the bundle id for Live Activity pushes.
     */
    public const topicSuffix = ".push-type.liveactivity";

    /**
     * The event of this Live Activity push. One of the APNSLiveActivityEvent constants.
     * @var string
     */
    private $event;

    /**
     * The date the Live Activity push was created, as a UNIX timestamp. ActivityKit uses this to discard pushes that
     * arrive out of order. Defaults to the moment this object was created.
     * @var int
     */
    private $timestamp;

    /**
     * The updated content state of the Live Activity. This has to match the ContentState of your ActivityAttributes
     * implementation in your app. Required for every event.
     * @var array dictionary
     */
    private $contentState = array();

    /**
     * The name of your ActivityAttributes implementation in your app. Only allowed on the `start` event, where it is
     * required.
     * @var string|null
     */
    private $attributesType = null;

    /**
     * The static attributes of the Live Activity to start. This has to match your ActivityAttributes implementation in
     * your app. Only allowed on the `start` event.
     * @var array dictionary
     */
    private $attributes = array();

    /**
     * The title of the alert that is shown when the Live Activity starts or updates on a device where the activity
     * isn't visible. Required for the `start` event, optional for the `update` event and not allowed on the `end` event.
     * @var string|null
     */
    private $alertTitle = null;

    /**
     * The body of the alert. See $alertTitle.
     * @var string|null
     */
    private $alertBody = null;

    /**
     * The sound that is played with the alert. Null means the alert is silent. See $alertTitle.
     * @var string|null
     */
    private $alertSound = null;

    /**
     * The timestamp after which the system considers the Live Activity out of date. Optional for every event.
     * @var int|null
     */
    private $staleDate = null;

    /**
     * The timestamp at which the system removes the ended Live Activity from the Lock Screen and the Dynamic Island.
     * Only allowed on the `end` event.
     * @var int|null
     */
    private $dismissalDate = null;

    /**
     * The relevance score, a number between 0 and 1, that the system uses to sort the Live Activities of your app in
     * the Dynamic Island. Set to null to let iOS decide.
     * @var float|null
     */
    private $relevanceScore = null;

    /**
     * Whether the `input-push-token` key should be added to the payload. When set, ActivityKit sends the update token
     * of the started Live Activity back to your server. Requires iOS 18 or later, so this is opt-in to stay compatible
     * with iOS 17.2 remote starts.
     * @var bool
     */
    private $inputPushToken = false;

    /**
     * The priority of the notification. Use 10 to send the notification immediately, 5 to send it based on power
     * considerations on the user's device.
     * @var int
     */
    private $priority = 10;

    /**
     * APNSLiveActivityNotification constructor.
     * @param string $event The event of this push. One of the APNSLiveActivityEvent constants.
     */
    public function __construct(string $event) {
        $this->event = $event;
        $this->timestamp = time();
    }

    /**
     * Generate the JSON payload of this Live Activity push.
     * @return string
     * @throws APNSException Throws when the notification isn't valid for the configured event.
     */
    public function generateJSONPayload(): string {
        $this->validate();

        $aps = array();
        $aps['timestamp'] = $this->timestamp;
        $aps['event'] = $this->event;
        $aps['content-state'] = (object) $this->contentState;

        if ($this->attributesType !== null) {
            $aps['attributes-type'] = $this->attributesType;
        }
        if ($this->event === APNSLiveActivityEvent::start) {
            $aps['attributes'] = (object) $this->attributes;
        }
        if ($this->alertTitle !== null || $this->alertBody !== null) {
            $alert = array();
            if ($this->alertTitle !== null) {
                $alert['title'] = $this->alertTitle;
            }
            if ($this->alertBody !== null) {
                $alert['body'] = $this->alertBody;
            }
            if ($this->alertSound !== null) {
                $alert['sound'] = $this->alertSound;
            }
            $aps['alert'] = $alert;
        }
        if ($this->staleDate !== null) {
            $aps['stale-date'] = $this->staleDate;
        }
        if ($this->dismissalDate !== null) {
            $aps['dismissal-date'] = $this->dismissalDate;
        }
        if ($this->relevanceScore !== null) {
            $aps['relevance-score'] = $this->relevanceScore;
        }
        if ($this->inputPushToken) {
            $aps['input-push-token'] = 1;
        }

        return json_encode(array("aps" => $aps));
    }

    /**
     * Validate the current configuration against the rules Apple defines for each event.
     * @throws APNSException Throws when the notification isn't valid for the configured event.
     */
    private function validate(): void {
        $hasAlert = $this->alertTitle !== null || $this->alertBody !== null || $this->alertSound !== null;

        switch ($this->event) {
            case APNSLiveActivityEvent::start:
                if ($this->attributesType === null) {
                    throw new APNSException("An attributes type is required for the start event. Use setAttributesType() to set it.");
                }
                if ($this->alertTitle === null || $this->alertBody === null) {
                    throw new APNSException("An alert title and body are required for the start event. Use setAlert() to set them.");
                }
                if ($this->dismissalDate !== null) {
                    throw new APNSException("A dismissal date can only be set on the end event.");
                }
                break;

            case APNSLiveActivityEvent::update:
                if ($this->attributesType !== null || !empty($this->attributes)) {
                    throw new APNSException("Attributes and an attributes type can only be set on the start event.");
                }
                if ($this->dismissalDate !== null) {
                    throw new APNSException("A dismissal date can only be set on the end event.");
                }
                break;

            case APNSLiveActivityEvent::end:
                if ($this->attributesType !== null || !empty($this->attributes)) {
                    throw new APNSException("Attributes and an attributes type can only be set on the start event.");
                }
                if ($hasAlert) {
                    throw new APNSException("An alert can't be set on the end event.");
                }
                break;

            default:
                throw new APNSException("Invalid Live Activity event provided: {$this->event}");
        }
    }

    /**
     * The event of this Live Activity push. One of the APNSLiveActivityEvent constants.
     * @return string
     */
    public function getEvent(): string {
        return $this->event;
    }

    /**
     * The date the Live Activity push was created, as a UNIX timestamp.
     * @return int
     */
    public function getTimestamp(): int {
        return $this->timestamp;
    }

    /**
     * The date the Live Activity push was created, as a UNIX timestamp. ActivityKit uses this to discard pushes that
     * arrive out of order. Defaults to the moment this object was created.
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void {
        $this->timestamp = $timestamp;
    }

    /**
     * The updated content state of the Live Activity.
     * @return array
     */
    public function getContentState(): array {
        return $this->contentState;
    }

    /**
     * The updated content state of the Live Activity. This has to match the ContentState of your ActivityAttributes
     * implementation in your app. Required for every event.
     * @param array $contentState
     */
    public function setContentState(array $contentState): void {
        $this->contentState = $contentState;
    }

    /**
     * The name of your ActivityAttributes implementation in your app.
     * @return string|null
     */
    public function getAttributesType(): ?string {
        return $this->attributesType;
    }

    /**
     * The name of your ActivityAttributes implementation in your app. Only allowed on the `start` event, where it is
     * required.
     * @param string|null $attributesType
     */
    public function setAttributesType(?string $attributesType): void {
        $this->attributesType = $attributesType;
    }

    /**
     * The static attributes of the Live Activity to start.
     * @return array
     */
    public function getAttributes(): array {
        return $this->attributes;
    }

    /**
     * The static attributes of the Live Activity to start. This has to match your ActivityAttributes implementation in
     * your app. Only allowed on the `start` event.
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void {
        $this->attributes = $attributes;
    }

    /**
     * The title of the alert that is shown when the Live Activity starts or updates.
     * @return string|null
     */
    public function getAlertTitle(): ?string {
        return $this->alertTitle;
    }

    /**
     * The body of the alert that is shown when the Live Activity starts or updates.
     * @return string|null
     */
    public function getAlertBody(): ?string {
        return $this->alertBody;
    }

    /**
     * The sound that is played with the alert.
     * @return string|null
     */
    public function getAlertSound(): ?string {
        return $this->alertSound;
    }

    /**
     * Set the alert that is shown when the Live Activity starts or updates on a device where the activity isn't
     * visible. Required for the `start` event, optional for the `update` event and not allowed on the `end` event.
     * Pass null for all parameters to remove the alert.
     * @param string|null $title The title of the alert.
     * @param string|null $body The body of the alert.
     * @param string|null $sound The sound that is played with the alert. Null means the alert is silent.
     */
    public function setAlert(?string $title, ?string $body, ?string $sound = null): void {
        $this->alertTitle = $title;
        $this->alertBody = $body;
        $this->alertSound = $sound;
    }

    /**
     * The timestamp after which the system considers the Live Activity out of date.
     * @return int|null
     */
    public function getStaleDate(): ?int {
        return $this->staleDate;
    }

    /**
     * The timestamp after which the system considers the Live Activity out of date. Optional for every event.
     * @param int|null $staleDate
     */
    public function setStaleDate(?int $staleDate): void {
        $this->staleDate = $staleDate;
    }

    /**
     * The timestamp at which the system removes the ended Live Activity from the screen.
     * @return int|null
     */
    public function getDismissalDate(): ?int {
        return $this->dismissalDate;
    }

    /**
     * The timestamp at which the system removes the ended Live Activity from the Lock Screen and the Dynamic Island.
     * Only allowed on the `end` event. Use a date in the past to dismiss the activity immediately.
     * @param int|null $dismissalDate
     */
    public function setDismissalDate(?int $dismissalDate): void {
        $this->dismissalDate = $dismissalDate;
    }

    /**
     * The relevance score, a number between 0 and 1, that the system uses to sort the Live Activities of your app.
     * @return float|null
     */
    public function getRelevanceScore(): ?float {
        return $this->relevanceScore;
    }

    /**
     * The relevance score, a number between 0 and 1, that the system uses to sort the Live Activities of your app in
     * the Dynamic Island. Set to null to let iOS decide.
     * @param float|null $relevanceScore
     * @throws APNSException Throws if $relevanceScore < 0.0 or $relevanceScore > 1.0
     */
    public function setRelevanceScore(?float $relevanceScore): void {
        if ($relevanceScore !== null && ($relevanceScore < 0.0 || $relevanceScore > 1.0)) {
            throw new APNSException("Invalid relevance score provided. Needs to be between 0.0 and 1.0. ($relevanceScore was given)");
        }
        $this->relevanceScore = $relevanceScore;
    }

    /**
     * Whether the `input-push-token` key is added to the payload.
     * @return bool
     */
    public function isInputPushToken(): bool {
        return $this->inputPushToken;
    }

    /**
     * Add the `input-push-token` key to the payload. When set, ActivityKit sends the update token of the started Live
     * Activity to your server through the pushToStartTokenUpdates stream on the device.
     * Requires iOS 18 or later. Devices running iOS 17.2 up to iOS 18 will reject a payload containing this key, so
     * only enable this when you no longer need to support remote starts on those versions.
     * @param bool $inputPushToken
     */
    public function setInputPushToken(bool $inputPushToken): void {
        $this->inputPushToken = $inputPushToken;
    }

    /**
     * @inheritDoc
     */
    public function getPushType(): string {
        return APNSPushType::liveactivity;
    }

    /**
     * @inheritDoc
     */
    public function getTopicSuffix(): ?string {
        return self::topicSuffix;
    }

    /**
     * The priority of the notification.
     * Use 10 to send the notification immediately.
     * Use 5 to send the notification based on power considerations on the user's device.
     * Is 10 by default.
     * @return int
     */
    public function getPriority(): int {
        return $this->priority;
    }

    /**
     * The priority of the notification.
     * Specify 10 to send the notification immediately.
     * Specify 5 to send the notification based on power considerations on the user's device.
     * Only 10 or 5 are allowed. Other values will throw an exception.
     * @param int $priority
     * @throws APNSException Throws when priority is a value that is different from 5 or 10.
     */
    public function setPriority(int $priority): void {
        if ($priority != 5 && $priority != 10) {
            throw new APNSException("Only values 5 and 10 are allowed to be set as priority.");
        }
        $this->priority = $priority;
    }

}
