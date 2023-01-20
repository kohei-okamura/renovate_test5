<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Mails;

use Domain\Staff\Invitation;
use Domain\Staff\Staff;
use Laravel\Lumen\Routing\UrlGenerator;
use ScalikePHP\Option;

/**
 * 招待登録メール.
 */
class CreateInvitationMailBuilder extends AbstractMailBuilder
{
    private UrlGenerator $url;
    private Invitation $invitation;
    private Option $staff;

    /**
     * Constructor.
     *
     * @param \Laravel\Lumen\Routing\UrlGenerator $url
     */
    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    /**
     * Set the Invitation.
     *
     * @param \Domain\Staff\Invitation $invitation
     * @return \App\Mails\CreateInvitationMailBuilder
     */
    public function invitation(Invitation $invitation): self
    {
        $this->invitation = $invitation;
        return $this;
    }

    /**
     * Set the Staff.
     *
     * @param \Domain\Staff\Staff[]|\ScalikePHP\Option $staff
     * @return \App\Mails\CreateInvitationMailBuilder
     */
    public function staff(Option $staff): self
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * 件名.
     *
     * @return string
     */
    protected function subject(): string
    {
        return 'careid アカウントへ招待されました';
    }

    /**
     * メールテンプレート名.
     *
     * @return string
     */
    protected function view(): string
    {
        return 'emails.staff.invitation';
    }

    /**
     * View に渡すパラメータ.
     *
     * @return array
     */
    protected function params(): array
    {
        return [
            'staffName' => $this->staff
                ->map(fn (Staff $x): string => $x->name->displayName)
                ->getOrElseValue(''),
            'url' => $this->url->to("invitations/{$this->invitation->token}"),
            'expiredAt' => $this->invitation->expiredAt->format('n/j H:i'),
        ];
    }
}
