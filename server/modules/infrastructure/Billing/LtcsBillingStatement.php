<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingStatement as DomainStatement;
use Domain\Billing\LtcsBillingStatementAggregate as DomainBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementInsurance;
use Domain\Billing\LtcsBillingStatementItem as DomainBillingStatementItem;
use Domain\Billing\LtcsBillingStatementSubsidy as DomainBillingStatementSubsidy;
use Domain\Billing\LtcsCarePlanAuthor;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix as DomainAppendix;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：明細書 Eloquent モデル.
 *
 * @property int $id 明細書 ID
 * @property int $billing_id 請求 ID
 * @property int $bundle_id 請求単位 ID
 * @property string $insurer_number 保険者番号
 * @property string $insurer_name 保険者名
 * @property \Domain\Billing\LtcsCarePlanAuthor $care_plan_author 居宅サービス計画
 * @property null|\Domain\Common\Carbon $agreed_on 開始年月日
 * @property null|\Domain\Common\Carbon $expired_on 中止年月日
 * @property \Domain\Billing\LtcsExpiredReason $expired_reason 中止理由
 * @property \Domain\Billing\LtcsBillingStatementInsurance $insurance 保険集計結果
 * @property \Domain\Billing\LtcsBillingStatus $status 状態
 * @property null|\Domain\Common\Carbon $fixed_at 確定日時
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Domain\Billing\LtcsBillingStatementSubsidy[] $subsidies 公費集計結果
 * @property-read \Domain\Billing\LtcsBillingStatementItem[] $items 明細
 * @property-read \Domain\Billing\LtcsBillingStatementAggregate[] $aggregates 集計
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUpdatedAt($value)
 */
final class LtcsBillingStatement extends Model implements Domainable
{
    use LtcsBillingUserHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_statement';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'billing_id',
        'bundle_id',
        'insurer_number',
        'insurer_name',
        'user',
        'care_plan_author',
        'agreed_on',
        'expired_on',
        'expired_reason',
        'insurance',
        'status',
        'fixed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'subsidies',
        'items',
        'aggregates',
        'appendix',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'agreed_on' => 'date',
        'expired_on' => 'date',
        'status' => CastsLtcsBillingStatus::class,
        'fixed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expired_reason' => CastsLtcsExpiredReason::class,
    ];

    /** {@inheritdoc} */
    protected $with = [
        'subsidies',
        'items',
        'items.subsidies',
        'aggregates',
        'aggregates.subsidies',
        'appendix',
        'appendix.entries',
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatement $domain
     * @return static
     */
    public static function fromDomain(DomainStatement $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainStatement
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainStatement::fromAssoc([
            'appendix' => null,
            ...$attrs,
        ]);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\LtcsBillingStatementSubsidy}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subsidies(): HasMany
    {
        return $this
            ->hasMany(LtcsBillingStatementSubsidy::class, 'statement_id')
            ->orderBy('sort_order');
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\LtcsBillingStatementItem}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this
            ->hasMany(LtcsBillingStatementItem::class, 'statement_id')
            ->orderBy('sort_order');
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\LtcsBillingStatementAggregate}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aggregates(): HasMany
    {
        return $this
            ->hasMany(LtcsBillingStatementAggregate::class, 'statement_id')
            ->orderBy('sort_order');
    }

    /**
     * HasOne: {@link \Infrastructure\Billing\LtcsBillingStatementAppendix}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function appendix(): HasOne
    {
        return $this->hasOne(LtcsBillingStatementAppendix::class, 'statement_id');
    }

    /**
     * Get mutator for care_plan_author attribute.
     *
     * @return \Domain\Billing\LtcsCarePlanAuthor
     * @noinspection PhpUnused
     */
    protected function getCarePlanAuthorAttribute(): LtcsCarePlanAuthor
    {
        return new LtcsCarePlanAuthor(
            authorType: LtcsCarePlanAuthorType::from($this->attributes['care_plan_author_type']),
            officeId: $this->attributes['care_plan_author_office_id'],
            code: $this->attributes['care_plan_author_code'],
            name: $this->attributes['care_plan_author_name'],
        );
    }

    /**
     * Set mutator for care_plan_author attribute.
     *
     * @param \Domain\Billing\LtcsCarePlanAuthor $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setCarePlanAuthorAttribute(LtcsCarePlanAuthor $value): void
    {
        $this->attributes['care_plan_author_type'] = $value->authorType->value();
        $this->attributes['care_plan_author_office_id'] = $value->officeId;
        $this->attributes['care_plan_author_code'] = $value->code;
        $this->attributes['care_plan_author_name'] = $value->name;
    }

    /**
     * Get mutator for insurance attribute.
     *
     * @return \Domain\Billing\LtcsBillingStatementInsurance
     * @noinspection PhpUnused
     */
    protected function getInsuranceAttribute(): LtcsBillingStatementInsurance
    {
        return new LtcsBillingStatementInsurance(
            benefitRate: $this->attributes['insurance_benefit_rate'],
            totalScore: $this->attributes['insurance_total_score'],
            claimAmount: $this->attributes['insurance_claim_amount'],
            copayAmount: $this->attributes['insurance_copay_amount'],
        );
    }

    /**
     * Set mutator for insurance attribute.
     *
     * @param \Domain\Billing\LtcsBillingStatementInsurance $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setInsuranceAttribute(LtcsBillingStatementInsurance $value): void
    {
        $this->attributes['insurance_benefit_rate'] = $value->benefitRate;
        $this->attributes['insurance_total_score'] = $value->totalScore;
        $this->attributes['insurance_claim_amount'] = $value->claimAmount;
        $this->attributes['insurance_copay_amount'] = $value->copayAmount;
    }

    /**
     * Get mutator for subsidies attribute.
     *
     * @return array&\Domain\Billing\LtcsBillingStatementSubsidy[]
     * @noinspection PhpUnused
     */
    protected function getSubsidiesAttribute(): array
    {
        return $this->mapRelation(
            'subsidies',
            fn (LtcsBillingStatementSubsidy $x): DomainBillingStatementSubsidy => $x->toDomain()
        );
    }

    /**
     * Get mutator for items attribute.
     *
     * @return array&\Domain\Billing\LtcsBillingStatementItem[]
     * @noinspection PhpUnused
     */
    protected function getItemsAttribute(): array
    {
        return $this->mapRelation(
            'items',
            fn (LtcsBillingStatementItem $x): DomainBillingStatementItem => $x->toDomain()
        );
    }

    /**
     * Get mutator for aggregates attribute.
     *
     * @return array&\Domain\Billing\LtcsBillingStatementAggregate[]
     * @noinspection PhpUnused
     */
    protected function getAggregatesAttribute(): array
    {
        return $this->mapRelation(
            'aggregates',
            fn (LtcsBillingStatementAggregate $x): DomainBillingStatementAggregate => $x->toDomain()
        );
    }

    /**
     * Get mutator for appendix attribute.
     *
     * @return null|\Domain\ProvisionReport\LtcsProvisionReportSheetAppendix
     * @noinspection PhpUnused
     */
    protected function getAppendixAttribute(): ?DomainAppendix
    {
        $x = $this->getRelationValue('appendix');
        assert($x === null || $x instanceof LtcsBillingStatementAppendix);
        return $x?->toDomain();
    }
}
