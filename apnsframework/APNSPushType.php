<?php

namespace APNSFramework;

/**
 * The possible values for the `apns-push-type` header.
 * See https://developer.apple.com/documentation/usernotifications/sending-notification-requests-to-apns
 * Class APNSPushType
 * @package APNSFramework
 */
class APNSPushType {

    public const alert = "alert";
    public const background = "background";
    public const location = "location";
    public const voip = "voip";
    public const complication = "complication";
    public const fileprovider = "fileprovider";
    public const mdm = "mdm";
    public const liveactivity = "liveactivity";
    public const pushtotalk = "pushtotalk";

}
