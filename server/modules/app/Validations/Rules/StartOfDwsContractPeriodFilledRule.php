<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Support\Arr;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Contract\IdentifyContractUseCase;
use UseCase\ProvisionReport\GetDwsProvisionReportUseCase;

/**
 * 対象の利用者との障害福祉サービス契約に初回サービス提供日が設定されているか検証する
 *
 * @mixin \App\Validations\CustomValidator
 */
trait StartOfDwsContractPeriodFilledRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     */
    protected function validateStartOfDwsContractPeriodFilled(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'start_of_contract_period_filled');
        $officeId = (int)$parameters[0];
        $userId = (int)$value;
        $providedIn = $parameters[1];
        $permission = Permission::from((string)$parameters[2]);
        $status = (int)Arr::get($this->data, 'status');

        // 状態を確定する場合以外はバリデーションしない
        if ($status !== DwsProvisionReportStatus::fixed()->value()) {
            return true;
        }

        // 存在しない事業所の場合、ここではエラーとしない
        if (!$this->validateOfficeExists($attribute, $officeId, [$permission])) {
            return true;
        }

        // 存在しない利用者の場合、ここではエラーとしない
        if (!$this->validateUserExists($attribute, $userId, [$permission])) {
            return true;
        }

        $identifyContractUseCase = app(IdentifyContractUseCase::class);
        assert($identifyContractUseCase instanceof IdentifyContractUseCase);

        /** @var \Domain\Contract\Contract[]|\ScalikePHP\Option $contractOption */
        $contractOption = $identifyContractUseCase->handle(
            $this->context,
            $permission,
            $officeId,
            $userId,
            ServiceSegment::disabilitiesWelfare(),
            Carbon::now()
        );

        // 契約が存在しない場合、ここではエラーとしない
        if ($contractOption->isEmpty()) {
            return true;
        }

        /** @var \UseCase\ProvisionReport\GetDwsProvisionReportUseCase $getDwsProvisionReportUseCase */
        $getDwsProvisionReportUseCase = app(GetDwsProvisionReportUseCase::class);

        $report = $getDwsProvisionReportUseCase
            ->handle(
                $this->context,
                $permission,
                $officeId,
                $userId,
                Carbon::parse($providedIn),
            );
        $reportItems = Seq::from(
            ...$report->map(fn (DwsProvisionReport $x): array => $x->plans)->toSeq()->flatten(),
            ...$report->map(fn (DwsProvisionReport $x): array => $x->results)->toSeq()->flatten(),
        );

        $homeHelpServiceExists = $reportItems->exists(
            fn (DwsProvisionReportItem $x): bool => $x->category === DwsProjectServiceCategory::physicalCare()
                || $x->category === DwsProjectServiceCategory::housework()
                || $x->category === DwsProjectServiceCategory::accompanyWithPhysicalCare()
                || $x->category === DwsProjectServiceCategory::accompany()
        );
        $visitingCareForPwsdExists = $reportItems->exists(
            fn (DwsProvisionReportItem $x): bool => $x->category === DwsProjectServiceCategory::visitingCareForPwsd()
        );

        if ($homeHelpServiceExists && $visitingCareForPwsdExists) {
            return $contractOption->exists(function (Contract $x) {
                $homeHelpServicePeriod = Option::fromArray($x->dwsPeriods, DwsServiceDivisionCode::homeHelpService()->value());
                $visitingCareForPwsdPeriod = Option::fromArray($x->dwsPeriods, DwsServiceDivisionCode::visitingCareForPwsd()->value());
                return $homeHelpServicePeriod->exists(fn (ContractPeriod $x) => $x->start !== null)
                    && $visitingCareForPwsdPeriod->exists(fn (ContractPeriod $x) => $x->start !== null);
            });
        } elseif ($homeHelpServiceExists) {
            return $contractOption->exists(function (Contract $x) {
                $homeHelpServicePeriod = Option::fromArray($x->dwsPeriods, DwsServiceDivisionCode::homeHelpService()->value());
                return $homeHelpServicePeriod->exists(fn (ContractPeriod $x) => $x->start !== null);
            });
        } elseif ($visitingCareForPwsdExists) {
            return $contractOption->exists(function (Contract $x) {
                $visitingCareForPwsdPeriod = Option::fromArray($x->dwsPeriods, DwsServiceDivisionCode::visitingCareForPwsd()->value());
                return $visitingCareForPwsdPeriod->exists(fn (ContractPeriod $x) => $x->start !== null);
            });
        } else {
            return true;
        }
    }
}
