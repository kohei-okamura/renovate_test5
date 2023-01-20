<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use Domain\Calling\SecondCallingEvent;
use Domain\Calling\StaffAttendanceSmsMessage;
use Domain\Sms\SmsGateway;

/**
 * 出勤確認通知2回目イベントリスナー.
 */
final class SecondCallingEventListener
{
    private SmsGateway $sender;

    /**
     * constructor.
     *
     * @param \Domain\Sms\SmsGateway $sender
     */
    public function __construct(SmsGateway $sender)
    {
        $this->sender = $sender;
    }

    /**
     * handle.
     *
     * @param \Domain\Calling\SecondCallingEvent $event
     */
    public function handle(SecondCallingEvent $event): void
    {
        $message = StaffAttendanceSmsMessage::create([
            'url' => $event->url(),
            'shift' => $event->shift(),
            'minutes' => $event->minutes(),
        ]);
        $this->sender->send($message, $event->staff()->tel);
    }
}
