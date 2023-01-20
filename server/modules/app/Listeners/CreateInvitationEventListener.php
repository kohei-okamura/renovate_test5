<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Listeners;

use App\Mails\CreateInvitationMailBuilder;
use Domain\Staff\CreateInvitationEvent;
use Illuminate\Mail\Mailer;

/**
 * 招待作成イベントリスナー.
 */
final class CreateInvitationEventListener
{
    private Mailer $mailer;
    private CreateInvitationMailBuilder $mailBuilder;

    /**
     * Constructor.
     *
     * @param \App\Mails\CreateInvitationMailBuilder $mailBuilder
     */
    public function __construct(CreateInvitationMailBuilder $mailBuilder)
    {
        $this->mailer = app('mailer');
        $this->mailBuilder = $mailBuilder;
    }

    /**
     * Handle the event.
     *
     * @param \Domain\Staff\CreateInvitationEvent $event
     */
    public function handle(CreateInvitationEvent $event): void
    {
        $invitation = $event->invitation();
        $mail = $this->mailBuilder
            ->invitation($invitation)
            ->staff($event->staff())
            ->to($invitation->email)
            ->organizationCode($event->context()->organization->code)
            ->build();
        $this->mailer->send($mail);
    }
}
