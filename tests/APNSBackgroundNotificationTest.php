<?php

namespace APNSFramework\Tests;

use APNSFramework\APNS;
use APNSFramework\APNSNotification;
use APNSFramework\Exception\APNSException;
use PHPUnit\Framework\TestCase;

final class APNSBackgroundNotificationTest extends TestCase {

    private function createBackgroundNotification(): APNSNotification {
        $notification = new APNSNotification();
        $notification->setContentAvailable(true);
        $notification->setSound(null);
        $notification->setPriority(5);
        $notification->setCollapseId("energy-prices:42:2026-07-27");
        $notification->addData("type", "api-cache.invalidate");
        return $notification;
    }

    public function testBackgroundNotificationPayloadOnlyContainsContentAvailable(): void {
        $notification = $this->createBackgroundNotification();

        $payload = json_decode($notification->generateJSONPayload(), true);

        $this->assertSame(["content-available" => 1], $payload['aps']);
        $this->assertArrayNotHasKey("alert", $payload['aps']);
        $this->assertArrayNotHasKey("sound", $payload['aps']);
        $this->assertArrayNotHasKey("badge", $payload['aps']);
        $this->assertSame("api-cache.invalidate", $payload['type']);
    }

    public function testBackgroundNotificationHeaders(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");

        $headers = $apns->buildRequestHeaders($this->createBackgroundNotification(), "authorization-token");

        $this->assertContains("apns-topic: nl.gridsense.GridSenseRN", $headers);
        $this->assertContains("apns-push-type: background", $headers);
        $this->assertContains("apns-priority: 5", $headers);
        $this->assertContains("apns-collapse-id: energy-prices:42:2026-07-27", $headers);
    }

    public function testCollapseIdHeaderIsOmittedWhenNotSet(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");

        $notification = new APNSNotification();
        $notification->setBody("Visible notification");

        $headers = $apns->buildRequestHeaders($notification, "authorization-token");

        foreach ($headers as $header) {
            $this->assertStringStartsNotWith("apns-collapse-id:", $header);
        }
        $this->assertContains("apns-push-type: alert", $headers);
        $this->assertContains("apns-priority: 10", $headers);
    }

    public function testVisibleNotificationKeepsDefaultSound(): void {
        $notification = new APNSNotification();
        $notification->setTitle("Title");
        $notification->setBody("Body");

        $payload = json_decode($notification->generateJSONPayload(), true);

        $this->assertSame("default", $payload['aps']['sound']);
        $this->assertSame("Title", $payload['aps']['alert']['title']);
        $this->assertSame("Body", $payload['aps']['alert']['body']);
    }

    public function testCriticalNotificationKeepsCriticalSound(): void {
        $notification = new APNSNotification();
        $notification->setBody("Body");
        $notification->setCritical(true);
        $notification->setSound("alarm.caf");
        $notification->setSoundVolume(1.0);

        $payload = json_decode($notification->generateJSONPayload(), true);

        $this->assertSame(1, $payload['aps']['sound']['critical']);
        $this->assertSame("alarm.caf", $payload['aps']['sound']['name']);
        $this->assertEquals(1.0, $payload['aps']['sound']['volume']);
    }

    public function testCollapseIdIsNullByDefault(): void {
        $this->assertNull((new APNSNotification())->getCollapseId());
    }

    public function testCollapseIdOfExactly64BytesIsAllowed(): void {
        $notification = new APNSNotification();
        $collapseId = str_repeat("a", 64);

        $notification->setCollapseId($collapseId);

        $this->assertSame($collapseId, $notification->getCollapseId());
    }

    public function testCollapseIdLongerThan64BytesThrows(): void {
        $notification = new APNSNotification();

        $this->expectException(APNSException::class);
        $notification->setCollapseId(str_repeat("a", 65));
    }

    public function testMultiByteCollapseIdIsValidatedInBytes(): void {
        $notification = new APNSNotification();

        // 33 three-byte characters are 99 bytes, which exceeds the limit even though it is 33 characters.
        $this->expectException(APNSException::class);
        $notification->setCollapseId(str_repeat("€", 33));
    }

    public function testEmptyCollapseIdThrows(): void {
        $notification = new APNSNotification();

        $this->expectException(APNSException::class);
        $notification->setCollapseId("");
    }

    /**
     * @dataProvider collapseIdsWithControlCharacters
     */
    public function testCollapseIdWithControlCharactersThrows(string $collapseId): void {
        $notification = new APNSNotification();

        $this->expectException(APNSException::class);
        $notification->setCollapseId($collapseId);
    }

    public static function collapseIdsWithControlCharacters(): array {
        return [
            "carriage return and line feed" => ["energy-prices\r\nx-injected: 1"],
            "line feed" => ["energy-prices\n"],
            "carriage return" => ["energy-prices\r"],
            "null byte" => ["energy-prices\0"],
            "tab" => ["energy-prices\t42"],
            "delete" => ["energy-prices\x7F"],
        ];
    }

    public function testCollapseIdCannotInjectAnAdditionalHeader(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");
        $notification = $this->createBackgroundNotification();

        try {
            $notification->setCollapseId("energy-prices\r\napns-topic: nl.attacker.App");
            $this->fail("Expected the collapse id to be rejected.");
        } catch (APNSException $exception) {
            // Expected.
        }

        $headers = $apns->buildRequestHeaders($notification, "authorization-token");
        foreach ($headers as $header) {
            $this->assertStringNotContainsString("nl.attacker.App", $header);
        }
    }

    public function testBadgeOnlyNotificationIsAnAlertPush(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");
        $notification = new APNSNotification();
        $notification->setBadge(3);

        $headers = $apns->buildRequestHeaders($notification, "authorization-token");

        // No body, but the notification still updates something the user sees.
        $this->assertContains("apns-push-type: alert", $headers);
        $this->assertContains("apns-priority: 10", $headers);
    }

    public function testContentAvailableNotificationWithABadgeIsAnAlertPush(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");
        $notification = new APNSNotification();
        $notification->setContentAvailable(true);
        $notification->setSound(null);
        $notification->setBadge(1);

        $headers = $apns->buildRequestHeaders($notification, "authorization-token");

        $this->assertContains("apns-push-type: alert", $headers);
    }

    public function testContentAvailableNotificationKeepingTheDefaultSoundIsAnAlertPush(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");
        $notification = new APNSNotification();
        $notification->setContentAvailable(true);

        $headers = $apns->buildRequestHeaders($notification, "authorization-token");

        // The sound defaults to "default", so this payload is not a background update.
        $this->assertContains("apns-push-type: alert", $headers);
    }

    public function testSilentNotificationOnTheDefaultPriorityIsSentAtPriorityFive(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");
        $notification = new APNSNotification();
        $notification->setContentAvailable(true);
        $notification->setSound(null);

        $headers = $apns->buildRequestHeaders($notification, "authorization-token");

        // APNs rejects priority 10 together with content-available, and 10 is the default.
        $this->assertContains("apns-push-type: background", $headers);
        $this->assertContains("apns-priority: 5", $headers);
    }

    public function testNotificationWithoutContentAvailableIsAnAlertPush(): void {
        $apns = new APNS("TEAMID1234", "nl.gridsense.GridSenseRN", "/dev/null", "KEYID12345");
        $notification = new APNSNotification();
        $notification->setSound(null);
        $notification->addData("type", "something");

        $headers = $apns->buildRequestHeaders($notification, "authorization-token");

        $this->assertContains("apns-push-type: alert", $headers);
    }

}
