<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use App\Mails\CancelShiftMailBuilder;
use Domain\Shift\CancelShiftEvent;
use Illuminate\Mail\Mailer;

/**
 * 勤務シフトキャンセルイベントリスナー.
 */
final class CancelShiftEventListener
{
    private CancelShiftMailBuilder $mailBuilder;
    private Mailer $mailer;

    /**
     * Constructor.
     * @param \App\Mails\CancelShiftMailBuilder $mailBuilder
     */
    public function __construct(CancelShiftMailBuilder $mailBuilder)
    {
        $this->mailBuilder = $mailBuilder;
        $this->mailer = app('mailer');
    }

    /**
     * handle.
     *
     * @param \Domain\Shift\CancelShiftEvent $event
     */
    public function handle(CancelShiftEvent $event): void
    {
        $staff = $event->staff();

        $mail = $this->mailBuilder
            ->shift($event->shift())
            ->staff($staff)
            ->user($event->user())
            ->to($staff->email)
            ->organizationCode($event->context()->organization->code)
            ->build();
        $this->mailer->send($mail);
    }
}
