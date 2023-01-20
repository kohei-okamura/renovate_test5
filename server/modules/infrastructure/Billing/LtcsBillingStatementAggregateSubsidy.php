<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingStatementAggregateSubsidy as DomainBillingStatementAggregateSubsidy;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：明細書：集計：公費集計結果 Eloquent モデル.
 *
 * @property int $id 公費集計結果 ID
 * @property int $statement_aggregate_id 集計 ID
 * @property int $total_score サービス単位数
 * @property int $claim_amount 請求額
 * @property int $copay_amount 利用者負担額
 * @property int $sort_order 並び順
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereStatementAggregateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereClaimAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCopayAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereSortOrder($value)
 */
final class LtcsBillingStatementAggregateSubsidy extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_statement_aggregate_subsidy';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'total_score',
        'claim_amount',
        'copay_amount',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'statement_aggregate_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatementAggregateSubsidy $domain
     * @param int $aggregateId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(
        DomainBillingStatementAggregateSubsidy $domain,
        int $aggregateId,
        int $sortOrder
    ): self {
        $keys = [
            'statement_aggregate_id' => $aggregateId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBillingStatementAggregateSubsidy
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainBillingStatementAggregateSubsidy::fromAssoc($attrs);
    }
}
