<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use App\Mails\CreateStaffPasswordResetMailBuilder;
use Domain\Staff\CreateStaffPasswordResetEvent;
use Illuminate\Mail\Mailer;

/**
 * スタッフパスワード再設定作成イベントリスナー.
 */
final class CreateStaffPasswordResetEventListener
{
    private Mailer $mailer;
    private CreateStaffPasswordResetMailBuilder $mailBuilder;

    /**
     * CreateStaffEventListener constructor.
     *
     * @param \App\Mails\CreateStaffPasswordResetMailBuilder $mailBuilder
     */
    public function __construct(CreateStaffPasswordResetMailBuilder $mailBuilder)
    {
        $this->mailer = app('mailer');
        $this->mailBuilder = $mailBuilder;
    }

    /**
     * Handle the event.
     *
     * @param \Domain\Staff\CreateStaffPasswordResetEvent $event
     */
    public function handle(CreateStaffPasswordResetEvent $event): void
    {
        $passwordReset = $event->passwordReset();
        $mail = $this->mailBuilder
            ->passwordReset($passwordReset)
            ->to($passwordReset->email)
            ->organizationCode($event->context()->organization->code)
            ->build();
        $this->mailer->send($mail);
    }
}
