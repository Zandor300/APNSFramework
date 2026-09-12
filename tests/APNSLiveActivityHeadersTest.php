<?php

namespace APNSFramework\Tests;

use APNSFramework\APNS;
use APNSFramework\APNSLiveActivityEvent;
use APNSFramework\APNSLiveActivityNotification;
use PHPUnit\Framework\TestCase;

final class APNSLiveActivityHeadersTest extends TestCase {

    public function testLiveActivityEventsUseTheirOwnTopicAndPushType(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");

        foreach ([APNSLiveActivityEvent::start, APNSLiveActivityEvent::update, APNSLiveActivityEvent::end] as $event) {
            $notification = new APNSLiveActivityNotification($event);
            $headers = $apns->buildRequestHeaders($notification, "authorization-token");

            $this->assertContains("apns-topic: nl.gridsense.GridSenseRN.push-type.liveactivity", $headers);
            $this->assertContains("apns-push-type: liveactivity", $headers);
            $this->assertContains("apns-priority: 10", $headers);
            foreach ($headers as $header) {
                $this->assertStringStartsNotWith("apns-collapse-id:", $header);
            }
        }
    }

    public function testLiveActivitySupportsLowPriorityHeaders(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");
        $notification = new APNSLiveActivityNotification(APNSLiveActivityEvent::update);
        $notification->setPriority(5);

        $headers = $apns->buildRequestHeaders($notification, "authorization-token");

        $this->assertContains("apns-priority: 5", $headers);
        $this->assertContains("apns-push-type: liveactivity", $headers);
    }

}
