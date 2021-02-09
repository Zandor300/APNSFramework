<?php

namespace APNSFramework;

// See https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/sending_notification_requests_to_apns
class APNSTokenEnvironment {

    public const development = "https://api.sandbox.push.apple.com/3/device";
    public const production = "https://api.push.apple.com/3/device";

}
