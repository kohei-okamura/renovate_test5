<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace App\Validations\Rules;

use Domain\Staff\Invitation;
use UseCase\Staff\LookupInvitationUseCase;

/**
 * 招待 E-mail アドレスが有効などのスタッフにも使われていないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait InvitationEmailAddressIsNotUsedByAnyStaffRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param string $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateInvitationEmailAddressIsNotUsedByAnyStaff(string $attribute, string $value, array $parameters): bool
    {
        $lookupInvitationUseCase = app(LookupInvitationUseCase::class);
        assert($lookupInvitationUseCase instanceof LookupInvitationUseCase);

        $invitation = $lookupInvitationUseCase->handle($this->context, $value)->headOption();
        return $invitation
            ->map(fn (Invitation $x): bool => $this->validateEmailAddressIsNotUsedByAnyStaff($attribute, $x->email, []))
            ->getOrElseValue(true); // 招待が存在しない場合このバリデーションではエラーとしない
    }
}
