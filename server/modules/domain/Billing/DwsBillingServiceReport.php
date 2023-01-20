<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * サービス提供実績記録票.
 *
 * @property-read int $id サービス提供実績記録票ID
 * @property-read int $dwsBillingId 請求ID
 * @property-read int $dwsBillingBundleId 請求単位ID
 * @property-read \Domain\Billing\DwsBillingUser $user 利用者（支給決定者）
 * @property-read \Domain\Billing\DwsBillingServiceReportFormat $format 様式種別番号
 * @property-read \Domain\Billing\DwsBillingServiceReportAggregate $plan 合計（計画時間数）
 * @property-read \Domain\Billing\DwsBillingServiceReportAggregate $result 合計（算定時間数）
 * @property-read int $emergencyCount 提供実績の合計2：緊急時対応加算（回）
 * @property-read int $firstTimeCount 提供実績の合計2：初回加算（回）
 * @property-read int $welfareSpecialistCooperationCount 提供実績の合計2：福祉専門職員等連携加算（回）
 * @property-read int $behavioralDisorderSupportCooperationCount 提供実績の合計2：行動障害支援連携加算（回）
 * @property-read int $movingCareSupportCount 提供実績の合計2：移動介護緊急時支援加算
 * @property-read array|\Domain\Billing\DwsBillingServiceReportItem[] $items 明細
 * @property-read \Domain\Billing\DwsBillingStatus $status 状態
 * @property-read null|\Domain\Common\Carbon $fixedAt 確定日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsBillingServiceReport extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dwsBillingId',
            'dwsBillingBundleId',
            'user',
            'format',
            'plan',
            'result',
            'emergencyCount',
            'firstTimeCount',
            'welfareSpecialistCooperationCount',
            'behavioralDisorderSupportCooperationCount',
            'movingCareSupportCount',
            'items',
            'status',
            'fixedAt',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'dwsBillingId' => true,
            'dwsBillingBundleId' => true,
            'user' => true,
            'format' => true,
            'plan' => true,
            'result' => true,
            'emergencyCount' => true,
            'firstTimeCount' => true,
            'welfareSpecialistCooperationCount' => true,
            'behavioralDisorderSupportCooperationCount' => true,
            'movingCareSupportCount' => true,
            'items' => true,
            'status' => true,
            'fixedAt' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
