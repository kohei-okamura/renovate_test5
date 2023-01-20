<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * サービス提供実績記録票：明細.
 *
 * @property-read null|int $serialNumber 提供通番
 * @property-read \Domain\Common\Carbon $providedOn 日付
 * @property-read \Domain\Billing\DwsGrantedServiceCode $serviceType サービス内容
 * @property-read \Domain\Billing\DwsBillingServiceReportProviderType $providerType ヘルパー資格
 * @property-read \Domain\Billing\DwsBillingServiceReportSituation $situation サービス提供の状況
 * @property-read null|\Domain\Billing\DwsBillingServiceReportDuration $plan 予定（計画）
 * @property-read null|\Domain\Billing\DwsBillingServiceReportDuration $result 実績
 * @property-read int $serviceCount サービスの提供回数
 * @property-read int $headcount 派遣人数
 * @property-read bool $isCoaching 同行支援
 * @property-read bool $isFirstTime 初回加算
 * @property-read bool $isEmergency 緊急時対応加算
 * @property-read bool $isWelfareSpecialistCooperation 福祉専門職員等連携加算
 * @property-read bool $isBehavioralDisorderSupportCooperation 行動障害支援連携加算
 * @property-read bool $isMovingCareSupport 移動介護緊急時支援加算
 * @property-read bool $isDriving 運転フラグ
 * @property-read bool $isPreviousMonth 前月からの継続サービス
 * @property-read string $note 備考
 */
final class DwsBillingServiceReportItem extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'serialNumber',
            'providedOn',
            'serviceType',
            'providerType',
            'situation',
            'plan',
            'result',
            'serviceCount',
            'headcount',
            'isCoaching',
            'isFirstTime',
            'isEmergency',
            'isWelfareSpecialistCooperation',
            'isBehavioralDisorderSupportCooperation',
            'isMovingCareSupport',
            'isDriving',
            'isPreviousMonth',
            'note',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'serialNumber' => true,
            'providedOn' => true,
            'serviceType' => true,
            'providerType' => true,
            'situation' => true,
            'plan' => true,
            'result' => true,
            'serviceCount' => true,
            'headcount' => true,
            'isCoaching' => true,
            'isFirstTime' => true,
            'isEmergency' => true,
            'isWelfareSpecialistCooperation' => true,
            'isBehavioralDisorderSupportCooperation' => true,
            'isMovingCareSupport' => true,
            'isDriving' => true,
            'isPreviousMonth' => true,
            'note' => true,
        ];
    }
}
