<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Context\Context;
use Domain\Event\Event;
use ScalikePHP\Option;

/**
 * 招待作成イベント.
 *
 * @see \App\Listeners\CreateInvitationEventListener
 */
final class CreateInvitationEvent extends Event
{
    private Invitation $invitation;
    private Option $staff;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Invitation $invitation
     * @param \Domain\Staff\Staff[]|\ScalikePHP\Option $staff
     */
    public function __construct(Context $context, Invitation $invitation, Option $staff)
    {
        parent::__construct($context);
        $this->invitation = $invitation;
        $this->staff = $staff;
    }

    /**
     * 招待の取得.
     *
     * @return \Domain\Staff\Invitation
     */
    public function invitation(): Invitation
    {
        return $this->invitation;
    }

    /**
     * スタッフの取得.
     *
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Option
     */
    public function staff(): Option
    {
        return $this->staff;
    }
}
