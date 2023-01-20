<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use App\Mails\StaffAttendanceConfirmationMailBuilder;
use Domain\Calling\FirstCallingEvent;
use Illuminate\Mail\Mailer;

/**
 * 出勤通知1回目イベントリスナー.
 */
final class FirstCallingEventListener
{
    private StaffAttendanceConfirmationMailBuilder $mailBuilder;
    private Mailer $mailer;

    /**
     * Constructor.
     * @param \App\Mails\StaffAttendanceConfirmationMailBuilder $mailBuilder
     */
    public function __construct(StaffAttendanceConfirmationMailBuilder $mailBuilder)
    {
        $this->mailBuilder = $mailBuilder;
        $this->mailer = app('mailer');
    }

    /**
     * handle.
     *
     * @param \Domain\Calling\FirstCallingEvent $event
     */
    public function handle(FirstCallingEvent $event): void
    {
        $mail = $this->mailBuilder
            ->calling($event->calling())
            ->staff($event->staff())
            ->url($event->url())
            ->to($event->staff()->email)
            ->organizationCode($event->context()->organization->code)
            ->build();
        $this->mailer->send($mail);
    }
}
