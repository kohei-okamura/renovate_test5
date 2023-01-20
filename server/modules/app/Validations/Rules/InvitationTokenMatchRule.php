<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Staff\Invitation;
use Illuminate\Support\Arr;
use UseCase\Staff\LookupInvitationUseCase;

/**
 * 指定した招待Entityとトークンが一致するか検査する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait InvitationTokenMatchRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateInvitationTokenMatch(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'invitation_token_match');
        $invitationId = Arr::get($this->data, $parameters[0]);
        $useCase = app(LookupInvitationUseCase::class);
        assert($useCase instanceof LookupInvitationUseCase);
        $invitationSeq = $useCase->handle($this->context, (int)$invitationId);
        return $invitationSeq->nonEmpty() && $invitationSeq->forAll(fn (Invitation $x): bool => $x->token === $value);
    }
}
