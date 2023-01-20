<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations;

use Closure;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use UseCase\Office\GetOfficeListUseCase;
use UseCase\Office\LookupDwsAreaGradeUseCase;
use UseCase\Office\LookupLtcsAreaGradeUseCase;
use UseCase\Office\LookupOfficeGroupUseCase;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase;
use UseCase\Project\LookupDwsProjectServiceMenuUseCase;
use UseCase\Project\LookupLtcsProjectServiceMenuUseCase;
use UseCase\Role\LookupRoleUseCase;
use UseCase\Shift\LookupAttendanceUseCase;
use UseCase\Shift\LookupShiftUseCase;
use UseCase\Staff\LookupInvitationUseCase;
use UseCase\Staff\LookupStaffUseCase;
use UseCase\User\LookupUserUseCase;
use UseCase\UserBilling\LookupUserBillingUseCase;
use UseCase\UserBilling\LookupWithdrawalTransactionUseCase;

/**
 * Entity存在チェック用カスタムバリデータ.
 *
 * CustomValidatorからのみuseする
 */
trait EntityExistsValidator
{
    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsAreaGradeExists(string $attribute, $value, array $parameters): bool
    {
        return $this->entityExists($value, function (Seq $seq): bool {
            $useCase = app(LookupDwsAreaGradeUseCase::class);
            assert($useCase instanceof LookupDwsAreaGradeUseCase);
            return $useCase->handle($this->context, ...$seq->toArray())->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsProjectServiceMenuExists(string $attribute, $value, array $parameters): bool
    {
        return $this->entityExists($value, function (Seq $seq): bool {
            $useCase = app(LookupDwsProjectServiceMenuUseCase::class);
            assert($useCase instanceof LookupDwsProjectServiceMenuUseCase);
            return $useCase->handle($this->context, ...$seq->toArray())->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateInvitationExists(string $attribute, $value, array $parameters): bool
    {
        return $this->entityExists($value, function (Seq $seq): bool {
            $useCase = app(LookupInvitationUseCase::class);
            assert($useCase instanceof LookupInvitationUseCase);
            return $useCase->handle($this->context, ...$seq->toArray())->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsAreaGradeExists(string $attribute, $value, array $parameters): bool
    {
        return $this->entityExists($value, function (Seq $seq): bool {
            $useCase = app(LookupLtcsAreaGradeUseCase::class);
            assert($useCase instanceof LookupLtcsAreaGradeUseCase);
            return $useCase->handle($this->context, ...$seq->toArray())->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsProjectServiceMenuExists(string $attribute, $value, array $parameters): bool
    {
        return $this->entityExists($value, function (Seq $seq): bool {
            $useCase = app(LookupLtcsProjectServiceMenuUseCase::class);
            assert($useCase instanceof LookupLtcsProjectServiceMenuUseCase);
            return $useCase->handle($this->context, ...$seq->toArray())->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateOfficeExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'office_exists');
        return $this->entityExists($value, function (Seq $seq) use ($parameters): bool {
            $useCase = app(LookupOfficeUseCase::class);
            assert($useCase instanceof LookupOfficeUseCase);
            $xs = $useCase->handle($this->context, [Permission::from((string)$parameters[0])], ...$seq->toArray());
            return $seq->size() === $xs->size();
        });
    }

    /**
     * 入力値が権限に関係なく存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateOfficeExistsIgnorePermissions(string $attribute, $value, array $parameters): bool
    {
        return $this->entityExists($value, function (Seq $seq): bool {
            /** @var \UseCase\Office\GetOfficeListUseCase $useCase */
            $useCase = app(GetOfficeListUseCase::class);
            return $useCase->handle($this->context, ...$seq->toArray())->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateOfficeGroupExists(string $attribute, $value, array $parameters): bool
    {
        return $this->entityExists($value, function (Seq $seq): bool {
            $useCase = app(LookupOfficeGroupUseCase::class);
            assert($useCase instanceof LookupOfficeGroupUseCase);
            return $useCase->handle($this->context, ...$seq->toArray())->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateOwnExpenseProgramExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'own_expense_program_exists');
        $permission = Permission::from((string)$parameters[0]);
        return $this->entityExists($value, function (Seq $seq) use ($permission): bool {
            /** @var \UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase $useCase */
            $useCase = app(LookupOwnExpenseProgramUseCase::class);
            $entityCount = $useCase->handle($this->context, $permission, ...$seq->toArray())->count();
            return $entityCount === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateShiftExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'shift_exists');
        $permission = Permission::from((string)$parameters[0]);
        return $this->entityExists($value, function (Seq $seq) use ($permission): bool {
            $useCase = app(LookupShiftUseCase::class);
            assert($useCase instanceof LookupShiftUseCase);
            $entityCount = $useCase->handle($this->context, $permission, ...$seq->toArray())->count();
            return $entityCount === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateAttendanceExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'attendance_exists');
        $permission = Permission::from((string)$parameters[0]);
        return $this->entityExists($value, function (Seq $seq) use ($permission): bool {
            /** @var \UseCase\Shift\LookupAttendanceUseCase $useCase */
            $useCase = app(LookupAttendanceUseCase::class);
            $entityCount = $useCase->handle($this->context, $permission, ...$seq->toArray())->count();
            return $entityCount === $seq->count();
        });
    }

    /**
     * 入力値が存在するロールか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateRoleExists(string $attribute, $value, array $parameters): bool
    {
        return $this->entityExists($value, function (Seq $seq): bool {
            /** @var \UseCase\Role\LookupRoleUseCase $useCase */
            $useCase = app(LookupRoleUseCase::class);
            $entityCount = $useCase->handle($this->context, ...$seq->toArray())->count();
            return $entityCount === $seq->count();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateStaffExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'staff_exists');
        return $this->entityExists($value, function (Seq $seq) use ($parameters): bool {
            $useCase = app(LookupStaffUseCase::class);
            assert($useCase instanceof LookupStaffUseCase);
            $xs = $useCase->handle($this->context, Permission::from((string)$parameters[0]), ...$seq->toArray());
            return $seq->size() === $xs->size();
        });
    }

    /**
     * 入力値が存在するエンティティか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'user_exists');
        $permission = Permission::from((string)$parameters[0]);
        return $this->entityExists($value, function (Seq $seq) use ($permission): bool {
            $useCase = app(LookupUserUseCase::class);
            assert($useCase instanceof LookupUserUseCase);
            return $useCase->handle($this->context, $permission, ...$seq->toArray())->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在する利用者請求か検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserBillingExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'user_billing_exists');
        $permission = Permission::from((string)$parameters[0]);
        return $this->entityExists($value, function (Seq $seq) use ($permission): bool {
            $useCase = app(LookupUserBillingUseCase::class);
            assert($useCase instanceof LookupUserBillingUseCase);
            return $useCase->handle($this->context, $permission, ...$seq)->count() === $seq->count();
        });
    }

    /**
     * 入力値が存在する口座振替データか検査する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateWithdrawalTransactionExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'withdrawal_transaction_exists');
        $permission = Permission::from((string)$parameters[0]);
        return $this->entityExists($value, function (Seq $seq) use ($permission): bool {
            $useCase = app(LookupWithdrawalTransactionUseCase::class);
            assert($useCase instanceof LookupWithdrawalTransactionUseCase);
            return $useCase->handle($this->context, $permission, ...$seq)->count() === $seq->count();
        });
    }

    /**
     * 存在するエンティティか検査する.
     *
     * @param mixed $value
     * @param Closure $f
     * @return bool
     */
    private function entityExists($value, Closure $f): bool
    {
        $seq = Seq::fromArray(is_array($value) ? $value : [$value]);
        if ($seq->exists(fn ($x): bool => !is_numeric($x))) {
            return false;
        }
        return $f($seq->map(fn ($x): int => (int)$x));
    }
}
