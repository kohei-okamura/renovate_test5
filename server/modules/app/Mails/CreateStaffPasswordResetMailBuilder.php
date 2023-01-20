<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Mails;

use Domain\Staff\StaffPasswordReset;
use Laravel\Lumen\Routing\UrlGenerator;

/**
 * スタッフパスワード再設定登録メール.
 */
class CreateStaffPasswordResetMailBuilder extends AbstractMailBuilder
{
    private UrlGenerator $url;
    private StaffPasswordReset $passwordReset;

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
     * Set the StaffPasswordReset.
     *
     * @param \Domain\Staff\StaffPasswordReset $passwordReset
     * @return \App\Mails\CreateStaffPasswordResetMailBuilder
     */
    public function passwordReset(StaffPasswordReset $passwordReset)
    {
        $this->passwordReset = $passwordReset;
        return $this;
    }

    /**
     * 件名.
     *
     * @return string
     */
    protected function subject(): string
    {
        return 'careid アカウントのパスワード再設定を受け付けました';
    }

    /**
     * メールテンプレート名.
     *
     * @return string
     */
    protected function view(): string
    {
        return 'emails.staff.password.reset';
    }

    /**
     * View に渡すパラメータ.
     *
     * @return array
     */
    protected function params(): array
    {
        return [
            'name' => $this->passwordReset->name,
            'url' => $this->url->to("password-resets/{$this->passwordReset->token}"),
            'expiredAt' => $this->passwordReset->expiredAt->format('n/j H:i'),
        ];
    }
}
