<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Illuminate\Support\Arr;
use UseCase\Contract\IdentifyContractUseCase;

/**
 * 対象の利用者との介護保険サービス契約に初回サービス提供日が設定されているか検証する
 *
 * @mixin \App\Validations\CustomValidator
 */
trait StartOfLtcsContractPeriodFilledRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     */
    protected function validateStartOfLtcsContractPeriodFilled(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'start_of_contract_period_filled');
        $officeId = (int)$parameters[0];
        $permission = Permission::from((string)$parameters[1]);
        $status = (int)Arr::get($this->data, 'status');

        // 状態を確定する場合以外はバリデーションしない
        if ($status !== LtcsProvisionReportStatus::fixed()->value()) {
            return true;
        }

        // 存在しない事業所の場合、ここではエラーとしない
        if (!$this->validateOfficeExists($attribute, $officeId, [$permission])) {
            return true;
        }

        // 存在しない利用者の場合、ここではエラーとしない
        if (!$this->validateUserExists($attribute, $value, [$permission])) {
            return true;
        }

        $identifyContractUseCase = app(IdentifyContractUseCase::class);
        assert($identifyContractUseCase instanceof IdentifyContractUseCase);

        /** @var \Domain\Contract\Contract[]|\ScalikePHP\Option $contractOption */
        $contractOption = $identifyContractUseCase->handle(
            $this->context,
            $permission,
            $officeId,
            (int)$value,
            ServiceSegment::longTermCare(),
            Carbon::now(),
        );

        // 契約が存在しない場合、ここではエラーとしない
        if ($contractOption->isEmpty()) {
            return true;
        }

        return $contractOption->exists(
            fn (Contract $x): bool => $x->ltcsPeriod !== null && $x->ltcsPeriod->start !== null
        );
    }
}
