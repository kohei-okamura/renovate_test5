<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * 障害福祉サービス：明細書.
 *
 * @property-read int $id 障害福祉サービス明細書ID
 * @property-read int $dwsBillingId 障害福祉サービスID
 * @property-read int $dwsBillingBundleId 障害福祉サービス請求単位ID
 * @property-read null|string $subsidyCityCode 助成自治体番号
 * @property-read \Domain\Billing\DwsBillingUser $user 利用者（支給決定者）
 * @property-read string $dwsAreaGradeName 地域区分名
 * @property-read string $dwsAreaGradeCode 地域区分コード
 * @property-read int $copayLimit 利用者負担上限月額
 * @property-read int $totalScore 請求額集計欄：合計：給付単位数
 * @property-read int $totalFee 請求額集計欄：合計：総費用額
 * @property-read int $totalCappedCopay 請求額集計欄：合計：上限月額調整
 * @property-read null|int $totalAdjustedCopay 請求額集計欄：合計：調整後利用者負担額
 * @property-read null|int $totalCoordinatedCopay 請求額集計欄：合計：上限管理後利用者負担額
 * @property-read int $totalCopay 請求額集計欄：合計：決定利用者負担額
 * @property-read int $totalBenefit 請求額集計欄：合計：請求額：給付費
 * @property-read null|int $totalSubsidy 請求額集計欄：合計：自治体助成分請求額
 * @property-read bool $isProvided 自社サービス提供有無
 * @property-read null|\Domain\Billing\DwsBillingStatementCopayCoordination $copayCoordination 上限管理結果
 * @property-read \Domain\Billing\DwsBillingStatementCopayCoordinationStatus $copayCoordinationStatus 上限管理区分
 * @property-read array|\Domain\Billing\DwsBillingStatementAggregate[] $aggregates 集計
 * @property-read \Domain\Billing\DwsBillingStatementContract[] $contracts 契約
 * @property-read array|\Domain\Billing\DwsBillingStatementItem[] $items 明細
 * @property-read \Domain\Billing\DwsBillingStatus $status 状態
 * @property-read null|\Domain\Common\Carbon $fixedAt 確定日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsBillingStatement extends Entity
{
    /**
     * 障害福祉サービス請求：明細書：契約インスタンスを生成する.
     *
     * @param array $values
     * @return \Domain\Billing\DwsBillingStatementContract
     */
    public static function contract(array $values): DwsBillingStatementContract
    {
        return DwsBillingStatementContract::create($values);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dwsBillingId',
            'dwsBillingBundleId',
            'subsidyCityCode',
            'user',
            'dwsAreaGradeName',
            'dwsAreaGradeCode',
            'copayLimit',
            'totalScore',
            'totalFee',
            'totalCappedCopay',
            'totalAdjustedCopay',
            'totalCoordinatedCopay',
            'totalCopay',
            'totalBenefit',
            'totalSubsidy',
            'isProvided',
            'copayCoordination',
            'copayCoordinationStatus',
            'aggregates',
            'contracts',
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
            'subsidyCityCode' => true,
            'user' => true,
            'dwsAreaGradeName' => true,
            'dwsAreaGradeCode' => true,
            'copayLimit' => true,
            'totalScore' => true,
            'totalFee' => true,
            'totalCappedCopay' => true,
            'totalAdjustedCopay' => true,
            'totalCoordinatedCopay' => true,
            'totalCopay' => true,
            'totalBenefit' => true,
            'totalSubsidy' => true,
            'isProvided' => true,
            'copayCoordination' => true,
            'copayCoordinationStatus' => true,
            'aggregates' => true,
            'contracts' => true,
            'items' => true,
            'status' => true,
            'fixedAt' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
