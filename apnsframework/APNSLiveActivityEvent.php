<?php

namespace APNSFramework;

/**
 * The possible values for the `event` key of a Live Activity payload.
 * See https://developer.apple.com/documentation/ActivityKit/starting-and-updating-live-activities-with-activitykit-push-notifications
 * Class APNSLiveActivityEvent
 * @package APNSFramework
 */
class APNSLiveActivityEvent {

    /**
     * Remotely start a new Live Activity. Requires a push-to-start token.
     */
    public const start = "start";

    /**
     * Update an already running Live Activity. Requires the update token of that activity.
     */
    public const update = "update";

    /**
     * End an already running Live Activity. Requires the update token of that activity.
     */
    public const end = "end";

}
