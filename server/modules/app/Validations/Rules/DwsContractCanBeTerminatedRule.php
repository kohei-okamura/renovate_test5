<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Illuminate\Support\Arr;
use UseCase\ProvisionReport\FindDwsProvisionReportUseCase;

/**
 * 障害の契約が契約終了へ更新可能であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsContractCanBeTerminatedRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateDwsContractCanBeTerminated(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(3, $parameters, 'dws_contract_can_be_terminated');
        $userId = Arr::get($this->data, $parameters[0], -1);
        $officeId = Arr::get($this->data, $parameters[1], -1);
        $permission = Permission::from($parameters[2]);
        $terminatedOnString = $value;

        // 解約日が不正である場合はこのバリデーションではエラーとしない
        if (empty($terminatedOnString) || !$this->validateDate($attribute, $terminatedOnString)) {
            return true;
        }

        $terminatedOn = Carbon::parse($terminatedOnString);

        $findDwsProvisionReportUseCase = app(FindDwsProvisionReportUseCase::class);
        assert($findDwsProvisionReportUseCase instanceof FindDwsProvisionReportUseCase);

        return $findDwsProvisionReportUseCase
            ->handle(
                $this->context,
                $permission,
                compact('officeId', 'userId'),
                ['all' => true]
            )
            ->list
            ->filter(fn (DwsProvisionReport $x): bool => $x->providedIn->gt($terminatedOn))
            ->isEmpty();
    }
}
