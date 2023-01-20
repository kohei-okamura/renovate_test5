<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingStatementAggregate as DomainBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy as DomainBillingStatementAggregateSubsidy;
use Domain\Common\Decimal;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：明細書：集計 Eloquent モデル.
 *
 * @property int $id 集計 ID
 * @property int $statement_id 明細書 ID
 * @property int $service_days サービス実日数
 * @property int $planned_score 計画単位数
 * @property int $managed_score 限度額管理対象単位数
 * @property int $unmanaged_score 限度額管理対象外単位数
 * @property \Domain\Billing\LtcsBillingStatementAggregateInsurance $insurance 保険集計結果
 * @property int $sort_order 並び順
 * @property-read array|\Domain\Billing\LtcsBillingStatementAggregateSubsidy[] $subsidies 公費集計結果
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereStatementId($value)
 */
final class LtcsBillingStatementAggregate extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_statement_aggregate';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'service_division_code',
        'service_days',
        'planned_score',
        'managed_score',
        'unmanaged_score',
        'insurance',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'subsidies',
    ];

    /**
     * 小数部の桁数.
     */
    private const FRACTION_DIGITS = 4;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'statement_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'service_division_code' => CastsLtcsServiceDivisionCode::class,
    ];

    /** {@inheritdoc} */
    protected $with = [
        'subsidies',
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatementAggregate $domain
     * @param int $statementId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainBillingStatementAggregate $domain, int $statementId, int $sortOrder): self
    {
        $keys = [
            'statement_id' => $statementId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBillingStatementAggregate
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainBillingStatementAggregate::fromAssoc($attrs);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\LtcsBillingStatementAggregateSubsidy}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subsidies(): HasMany
    {
        return $this
            ->hasMany(LtcsBillingStatementAggregateSubsidy::class, 'statement_aggregate_id')
            ->orderBy('sort_order');
    }

    /**
     * Get mutator for insurance attribute.
     *
     * @return \Domain\Billing\LtcsBillingStatementAggregateInsurance
     * @noinspection PhpUnused
     */
    protected function getInsuranceAttribute(): LtcsBillingStatementAggregateInsurance
    {
        return new LtcsBillingStatementAggregateInsurance(
            totalScore: $this->attributes['insurance_total_score'],
            unitCost: Decimal::fromInt($this->attributes['insurance_unit_cost'], self::FRACTION_DIGITS),
            claimAmount: $this->attributes['insurance_claim_amount'],
            copayAmount: $this->attributes['insurance_copay_amount'],
        );
    }

    /**
     * Set mutator for insurance attribute.
     *
     * @param \Domain\Billing\LtcsBillingStatementAggregateInsurance $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setInsuranceAttribute(LtcsBillingStatementAggregateInsurance $value): void
    {
        $this->attributes['insurance_total_score'] = $value->totalScore;
        $this->attributes['insurance_unit_cost'] = $value->unitCost->toInt(self::FRACTION_DIGITS);
        $this->attributes['insurance_claim_amount'] = $value->claimAmount;
        $this->attributes['insurance_copay_amount'] = $value->copayAmount;
    }

    /**
     * Get mutator for subsidies attribute.
     *
     * @return array|\Domain\Billing\LtcsBillingStatementAggregateSubsidy[]
     * @noinspection PhpUnused
     */
    protected function getSubsidiesAttribute(): array
    {
        return $this->mapRelation(
            'subsidies',
            fn (LtcsBillingStatementAggregateSubsidy $x): DomainBillingStatementAggregateSubsidy => $x->toDomain()
        );
    }
}
