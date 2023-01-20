<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase;

/**
 * 入力値の自費サービスが存在しているかつ入力された事業所と紐付いていることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait OwnExpenseProgramBelongsToOfficeRule
{
    protected function validateOwnExpenseProgramBelongsToOffice(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'own_expense_program_belongs_to_office');
        $officeId = (int)Arr::get($this->data, $parameters[0], $parameters[0]);
        $permission = Permission::from((string)$parameters[1]);

        if (empty($officeId) || empty($value)) {
            return true;
        }

        $useCase = app(LookupOwnExpenseProgramUseCase::class);
        assert($useCase instanceof LookupOwnExpenseProgramUseCase);
        $ownExpenseProgram = $useCase->handle($this->context, $permission, $value);
        if ($ownExpenseProgram->isEmpty()) {
            return true;
        }
        return $ownExpenseProgram->exists(fn (OwnExpenseProgram $x) => $x->officeId === null || $officeId === $x->officeId);
    }
}
