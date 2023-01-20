<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\ProvisionReport\GetDwsProvisionReportUseCase;

/**
 * 対象の利用者の障害福祉サービス受給者証：支給量が有効であるか検証する
 *
 * @mixin \App\Validations\CustomValidator
 */
trait HasActiveCertificationGrantRule
{
    /**
     * 状態を確定にする場合に以下の3つを検証する.
     *
     * 1. 当該サービス提供年月における受給者証が存在すること。
     * 2. 予実の実績に「居宅介護」が含まれる場合に、支給量（介護給付費の支給決定内容）のいずれかが下記を満たすこと。
     *  -「サービス種別」が「居宅介護」である。
     *  -「認定の有効期間」の開始〜終了の間にサービス提供年月の末日が含まれる。
     * 3. 予実の実績に「重度訪問介護」が含まれる場合に、支給量（介護給付費の支給決定内容）のいずれかが下記を満たすこと。
     *  -「サービス種別」が「重度訪問介護」である。
     *  -「認定の有効期間」の開始〜終了の間にサービス提供年月の末日が含まれる。
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateHasActiveCertificationGrant(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(3, $parameters, 'has_active_certification_grant');
        $officeId = (int)$parameters[0];
        $userId = (int)$value;
        $providedIn = Carbon::parse($parameters[1]);
        $permission = Permission::from((string)$parameters[2]);
        $status = (int)Arr::get($this->data, 'status');

        // 状態を確定する場合以外はバリデーションしない
        if ($status !== DwsProvisionReportStatus::fixed()->value()) {
            return true;
        }

        $getProvisionReportUseCase = app(GetDwsProvisionReportUseCase::class);
        assert($getProvisionReportUseCase instanceof GetDwsProvisionReportUseCase);

        $provisionReportOption = $getProvisionReportUseCase->handle(
            $this->context,
            $permission,
            $officeId,
            $userId,
            $providedIn
        );
        if ($provisionReportOption->isEmpty()) {
            return true;
        }
        /** @var \Domain\ProvisionReport\DwsProvisionReport $provisionReport */
        $provisionReport = $provisionReportOption->get();

        $identifyCertificationUseCase = app(IdentifyDwsCertificationUseCase::class);
        assert($identifyCertificationUseCase instanceof IdentifyDwsCertificationUseCase);

        $certificationOption = $identifyCertificationUseCase->handle($this->context, $userId, $providedIn);

        $hasHomeHelpServiceResult = fn (DwsProvisionReport $x): bool => Seq::fromArray($x->results)
            ->exists(fn (DwsProvisionReportItem $y) => $y->isHomeHelpService());
        $hasVisitingCareForPwsdResult = fn (DwsProvisionReport $x): bool => Seq::fromArray($x->results)
            ->exists(fn (DwsProvisionReportItem $y) => $y->isVisitingCareForPwsd());
        $isActiveHomeHelpServiceGrant = fn (DwsCertificationGrant $x): bool => $x->dwsCertificationServiceType->isHomeHelpService()
            && $x->activatedOn->lte($providedIn->lastOfMonth())
            && $x->deactivatedOn->gte($providedIn->lastOfMonth());
        $isActiveVisitingCareForPwsdGrant = fn (DwsCertificationGrant $x): bool => $x->dwsCertificationServiceType->isVisitingCareForPwsd()
            && $x->activatedOn->lte($providedIn->lastOfMonth())
            && $x->deactivatedOn->gte($providedIn->lastOfMonth());

        return $certificationOption
            ->map(function (DwsCertification $certification) use (
                $hasHomeHelpServiceResult,
                $hasVisitingCareForPwsdResult,
                $provisionReport,
                $isActiveHomeHelpServiceGrant,
                $isActiveVisitingCareForPwsdGrant
            ): bool {
                $isValidHomeHelpServiceGrant = !$hasHomeHelpServiceResult($provisionReport)
                    || Seq::fromArray($certification->grants)
                        ->exists(fn (DwsCertificationGrant $x): bool => $isActiveHomeHelpServiceGrant($x));
                $isValidVisitingCareForPwsdGrant = !$hasVisitingCareForPwsdResult($provisionReport)
                    || Seq::fromArray($certification->grants)
                        ->exists(fn (DwsCertificationGrant $x): bool => $isActiveVisitingCareForPwsdGrant($x));

                return $isValidHomeHelpServiceGrant && $isValidVisitingCareForPwsdGrant;
            })
            ->getOrElseValue(false); // 受給者証が存在しない場合
    }
}
