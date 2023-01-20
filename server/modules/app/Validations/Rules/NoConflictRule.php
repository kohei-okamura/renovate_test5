<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\Shift\LookupShiftUseCase;

/**
 * 入力値の「勤務シフトID」がダブルブッキングにならないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NoConflictRule
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
    protected function validateNoConflict(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'no_conflict');
        $shiftIds = Arr::get($this->data, $parameters[0]);
        if (!is_int($value)) {
            return true;
        }
        $shiftId = $value;

        /** @var \UseCase\Shift\LookupShiftUseCase $useCase */
        $useCase = app(LookupShiftUseCase::class);

        $permission = Permission::from((string)$parameters[1]);

        $shift = $useCase->handle($this->context, $permission, $shiftId)->headOption()->orNull();
        if ($shift === null) {
            return true;
        }
        assert($shift instanceof Shift);

        return Seq::fromArray($shift->assignees)->forAll(
            fn ($assignee) => !$this->isInConflictOfShift(
                $shiftId,
                $assignee->staffId,
                $shift->schedule->start,
                $shift->schedule->end,
                $shiftIds
            )
        );
    }
}
