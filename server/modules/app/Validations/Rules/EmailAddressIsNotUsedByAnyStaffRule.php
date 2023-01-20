<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Staff\Staff;
use Domain\Staff\StaffStatus;
use UseCase\Staff\IdentifyStaffByEmailUseCase;

/**
 * 指定したE-mail アドレスが有効などのスタッフにも使われていないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait EmailAddressIsNotUsedByAnyStaffRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateEmailAddressIsNotUsedByAnyStaff(string $attribute, mixed $value, array $parameters): bool
    {
        if (!is_string($value)) {
            return true;
        }
        // staff id が未指定の時は突合しない（存在しない ID として -1 を使用）
        $staffId = empty($parameters) ? -1 : (int)$parameters[0];
        // TODO 下記は暫定対応。LookupStaffByEmailUseCase だと対応できないケースがあるため修正が必要。
        $useCase = app(IdentifyStaffByEmailUseCase::class);
        assert($useCase instanceof IdentifyStaffByEmailUseCase);
        return $useCase
            ->handle($this->context, $value)
            ->filter(fn (Staff $x) => $x->id !== $staffId && $x->status !== StaffStatus::retired())
            ->isEmpty();
    }
}
