<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use App\Mails\UpdateShiftMailBuilder;
use Domain\Shift\UpdateShiftEvent;
use Illuminate\Mail\Mailer;

/**
 * 確定済み勤務シフト更新時イベントリスナー.
 */
final class UpdateShiftEventListener
{
    private UpdateShiftMailBuilder $mailBuilder;
    private Mailer $mailer;

    /**
     * Constructor.
     *
     * @param \App\Mails\UpdateShiftMailBuilder $mailBuilder
     */
    public function __construct(UpdateShiftMailBuilder $mailBuilder)
    {
        $this->mailBuilder = $mailBuilder;
        $this->mailer = app('mailer');
    }

    /**
     * handle.
     *
     * @param \Domain\Shift\UpdateShiftEvent $event
     */
    public function handle(UpdateShiftEvent $event): void
    {
        $staff = $event->staff();

        $mail = $this->mailBuilder
            ->originalShift($event->originalShift())
            ->updatedShift($event->updatedShift())
            ->originalUser($event->originalUser())
            ->updatedUser($event->updatedUser())
            ->staff($staff)
            ->to($staff->email)
            ->organizationCode($event->context()->organization->code)
            ->build();
        $this->mailer->send($mail);
    }
}
