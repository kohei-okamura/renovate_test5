<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportItem;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\ProvisionReport\GetDwsProvisionReportUseCase;

/**
 * 対象の利用者の障害福祉サービス受給者証に有効な訪問系サービス事業者記入欄が存在するか検証する
 *
 * @mixin \App\Validations\CustomValidator
 */
trait HasActiveCertificationAgreementsRule
{
    /**
     * 以下を検証する.
     *
     * ・ 予実に「居宅：***」の実績が含まれる場合：
     *   - 「事業所」が一致する。
     *   - 「サービス内容」が「組み合わせ一覧（居宅介護）」の通りである。
     *   -「契約日」が該当する実績の日付の最小値以前である。
     *   - 「当該契約支給量によるサービス提供終了日」が未設定である or 該当する実績の日付の最大値以降である。
     * ・ 予実に「重度訪問介護」の実績が含まれる場合：
     *   - 「事業所」が一致する。
     *   - 「サービス内容」が「サービス内容一覧（重度訪問介護）」のいずれかである。
     *   - 「契約日」が該当する実績の日付の最小値以前である。
     *   - 「当該契約支給量によるサービス提供終了日」が未設定である or 該当する実績の日付の最大値以降である。
     * ・予実の「重度訪問介護」の実績のいずれかに「移動介護時間数」が設定されている場合：
     *   - 「事業所」が一致する。
     *   - 「サービス内容」が「重度訪問介護（移動加算）」である。
     *   - 「契約日」が該当する実績の日付の最小値以前である。
     *   - 「当該契約支給量によるサービス提供終了日」が未設定である or 該当する実績の日付の最大値以降である。
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateHasActiveCertificationAgreements(
        string $attribute,
        mixed $value,
        array $parameters
    ): bool {
        $this->requireParameterCount(3, $parameters, 'has_active_certification_grant');
        $officeId = (int)$parameters[0];
        $userId = (int)$value;
        $providedIn = Carbon::parse($parameters[1]);
        $permission = Permission::from((string)$parameters[2]);

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

        $agreementsOption = $identifyCertificationUseCase->handle($this->context, $userId, $providedIn)
            ->map(
                fn (DwsCertification $x): Seq => Seq::fromArray($x->agreements)
                    ->filter(fn (DwsCertificationAgreement $x): bool => $x->officeId === $officeId)
            );

        $agreementExists = function (Seq $agreements, DwsCertificationAgreementType|array $type, Seq $results): bool {
            $types = is_array($type) ? $type : [$type];
            return $agreements->exists(function (DwsCertificationAgreement $agreement) use ($types, $results): bool {
                return in_array($agreement->dwsCertificationAgreementType, $types, true)
                    && $results->forAll(function (DwsProvisionReportItem $result) use ($agreement): bool {
                        return $agreement->agreedOn->lte($result->schedule->date)
                            && ($agreement->expiredOn === null || $agreement->expiredOn->gte($result->schedule->date));
                    });
            });
        };

        $resultExists = function (Seq $results, DwsProjectServiceCategory $category, bool $isMoving = false): bool {
            return $isMoving
                ? $results->exists(function (DwsProvisionReportItem $x) use ($category): bool {
                    return $x->category === $category && $x->movingDurationMinutes > 0;
                })
                : $results->exists(function (DwsProvisionReportItem $x) use ($category): bool {
                    return $x->category === $category;
                });
        };

        return $agreementsOption
            ->map(function (Seq $agreements) use ($provisionReport, $resultExists, $agreementExists): bool {
                $results = Seq::fromArray($provisionReport->results);

                // 居宅介護
                $validatePhysicalCare = !$resultExists($results, DwsProjectServiceCategory::physicalCare())
                    || $agreementExists(
                        $agreements,
                        DwsCertificationAgreementType::physicalCare(),
                        $results->filter(function (DwsProvisionReportItem $result): bool {
                            return $result->category === DwsProjectServiceCategory::physicalCare();
                        })
                    );
                $validateHousework = !$resultExists($results, DwsProjectServiceCategory::housework())
                    || $agreementExists(
                        $agreements,
                        DwsCertificationAgreementType::housework(),
                        $results->filter(function (DwsProvisionReportItem $result): bool {
                            return $result->category === DwsProjectServiceCategory::housework();
                        })
                    );
                $validateAccompanyWithPhysicalCare =
                    !$resultExists($results, DwsProjectServiceCategory::accompanyWithPhysicalCare())
                    || $agreementExists(
                        $agreements,
                        DwsCertificationAgreementType::accompanyWithPhysicalCare(),
                        $results->filter(function (DwsProvisionReportItem $result): bool {
                            return $result->category === DwsProjectServiceCategory::accompanyWithPhysicalCare();
                        })
                    );
                $validateAccompany = !$resultExists($results, DwsProjectServiceCategory::accompany())
                    || $agreementExists(
                        $agreements,
                        DwsCertificationAgreementType::accompany(),
                        $results->filter(function (DwsProvisionReportItem $result): bool {
                            return $result->category === DwsProjectServiceCategory::accompany();
                        })
                    );

                // 重度訪問介護
                $validateVisitingCareForPwsd =
                    !$resultExists($results, DwsProjectServiceCategory::visitingCareForPwsd())
                    || $agreementExists(
                        $agreements,
                        [
                            DwsCertificationAgreementType::visitingCareForPwsd1(),
                            DwsCertificationAgreementType::visitingCareForPwsd2(),
                            DwsCertificationAgreementType::visitingCareForPwsd3(),
                        ],
                        $results->filter(function (DwsProvisionReportItem $result): bool {
                            return $result->category === DwsProjectServiceCategory::visitingCareForPwsd();
                        })
                    );

                // 重度訪問介護（移動加算）
                $validateOutingSupportForPwsd =
                    !$resultExists($results, DwsProjectServiceCategory::visitingCareForPwsd(), true)
                    || $agreementExists(
                        $agreements,
                        DwsCertificationAgreementType::outingSupportForPwsd(),
                        $results->filter(function (DwsProvisionReportItem $result): bool {
                            return $result->category === DwsProjectServiceCategory::visitingCareForPwsd();
                        })
                    );
                return $validatePhysicalCare
                    && $validateHousework
                    && $validateAccompanyWithPhysicalCare
                    && $validateAccompany
                    && $validateVisitingCareForPwsd
                    && $validateOutingSupportForPwsd;
            })
            ->getOrElseValue(false);
    }
}
