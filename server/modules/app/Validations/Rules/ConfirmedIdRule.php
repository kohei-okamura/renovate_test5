<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use UseCase\Shift\LookupAttendanceUseCase;
use UseCase\Shift\LookupShiftUseCase;

/**
 * 入力値が確定済みであることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait ConfirmedIdRule
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
    protected function validateConfirmedId(string $attribute, $value, array $parameters): bool
    {
        $seq = Seq::fromArray(is_array($value) ? $value : [$value]);
        if ($seq->exists(fn ($x): bool => !is_numeric($x))) {
            return false;
        }
        $this->requireParameterCount(2, $parameters, 'confirmed_id');
        $permission = Permission::from((string)$parameters[1]);
        $className = $parameters[0];
        $useCase = app($className);
        if (!($useCase instanceof LookupShiftUseCase || $useCase instanceof LookupAttendanceUseCase)) {
            throw new InvalidArgumentException("{$className} is not instance of LookupShiftUseCase or LookupAttendanceUseCase");
        }
        $entityCount = $useCase->handle($this->context, $permission, ...$seq->map(fn ($x): int => (int)$x)->toArray())
            ->filter(fn (Shift $x): bool => $x->isConfirmed === true)->count();
        return $seq->count() === $entityCount;
    }
}
