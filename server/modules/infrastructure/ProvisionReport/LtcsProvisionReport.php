<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Domain\ProvisionReport\LtcsProvisionReport as DomainLtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry as DomainLtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Office\CastsHomeVisitLongTermCareSpecifiedOfficeAddition;
use Infrastructure\Office\CastsLtcsBaseIncreaseSupportAddition;
use Infrastructure\Office\CastsLtcsOfficeLocationAddition;
use Infrastructure\Office\CastsLtcsSpecifiedTreatmentImprovementAddition;
use Infrastructure\Office\CastsLtcsTreatmentImprovementAddition;

/**
 * 介護保険サービス：予実 Eloquent モデル.
 *
 * @property int $id 介護保険サービス：予実ID
 * @property int $user_id 利用者ID
 * @property int $office_id 事業所ID
 * @property int $contract_id 契約ID
 * @property \Domain\Common\Carbon $provided_in サービス提供年月
 * @property \Domain\Office\LtcsOfficeLocationAddition $location_addition 地域加算
 * @property int $plan_max_benefit_excess_score 超過単位（予定）：区分支給限度基準を超える単位数
 * @property int $plan_max_benefit_quota_excess_score 超過単位（予定）：種類支給限度基準を超える単位数
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportOverScore $plan 超過単位（予定）
 * @property int $result_max_benefit_excess_score 超過単位（実績）：区分支給限度基準を超える単位数
 * @property int $result_max_benefit_quota_excess_score 超過単位（実績）：種類支給限度基準を超える単位数
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportOverScore $result 超過単位（実績）
 * @property \Domain\ProvisionReport\LtcsProvisionReportStatus $status 状態
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\ProvisionReport\LtcsProvisionReportEntry[] $entries サービス情報
 */
final class LtcsProvisionReport extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_provision_report';

    /**
     * 属性.
     */
    private const ATTRIBUTES = [
        'id',
        'user_id',
        'office_id',
        'contract_id',
        'provided_in',
        'specified_office_addition',
        'treatment_improvement_addition',
        'specified_treatment_improvement_addition',
        'base_increase_support_addition',
        'location_addition',
        'plan',
        'result',
        'status',
        'fixed_at',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'provided_in' => 'date',
        'fixed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => CastsLtcsProvisionReportStatus::class,
        'specified_office_addition' => CastsHomeVisitLongTermCareSpecifiedOfficeAddition::class,
        'treatment_improvement_addition' => CastsLtcsTreatmentImprovementAddition::class,
        'specified_treatment_improvement_addition' => CastsLtcsSpecifiedTreatmentImprovementAddition::class,
        'base_increase_support_addition' => CastsLtcsBaseIncreaseSupportAddition::class,
        'location_addition' => CastsLtcsOfficeLocationAddition::class,
    ];

    /** {@inheritdoc} */
    protected $with = [
        'entries',
        'entries.options',
        'entries.amounts',
        'entries.plans',
        'entries.results',
    ];

    /**
     * HasMany: {@link \Infrastructure\ProvisionReport\LtcsProvisionReportEntry}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function entries(): HasMany
    {
        return $this->hasMany(LtcsProvisionReportEntry::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $domain
     * @return \Infrastructure\ProvisionReport\LtcsProvisionReport
     */
    public static function fromDomain(DomainLtcsProvisionReport $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsProvisionReport
    {
        $hasGetMutatorAttrs = [
            'entries',
            'plan',
            'result',
        ];
        return DomainLtcsProvisionReport::create($this->only($hasGetMutatorAttrs) + $this->toDomainValues());
    }

    /**
     * Get mutator for entries.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getEntriesAttribute(): array
    {
        return $this->mapSortRelation(
            'entries',
            'sort_order',
            fn (LtcsProvisionReportEntry $x): DomainLtcsProvisionReportEntry => $x->toDomain()
        );
    }

    /**
     * Get mutator for plan.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportOverScore
     * @noinspection PhpUnused
     */
    protected function getPlanAttribute(): LtcsProvisionReportOverScore
    {
        return new LtcsProvisionReportOverScore(
            maxBenefitExcessScore: $this->plan_max_benefit_excess_score,
            maxBenefitQuotaExcessScore: $this->plan_max_benefit_quota_excess_score
        );
    }

    /**
     * Set mutator for plan.
     *
     * @noinspection PhpUnused
     * @param \Domain\ProvisionReport\LtcsProvisionReportOverScore $plan
     */
    protected function setPlanAttribute(LtcsProvisionReportOverScore $plan): void
    {
        $this->attributes['plan_max_benefit_excess_score'] = $plan->maxBenefitExcessScore;
        $this->attributes['plan_max_benefit_quota_excess_score'] = $plan->maxBenefitQuotaExcessScore;
    }

    /**
     * Get mutator for result.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportOverScore
     * @noinspection PhpUnused
     */
    protected function getResultAttribute(): LtcsProvisionReportOverScore
    {
        return new LtcsProvisionReportOverScore(
            maxBenefitExcessScore: $this->result_max_benefit_excess_score,
            maxBenefitQuotaExcessScore: $this->result_max_benefit_quota_excess_score
        );
    }

    /**
     * Set mutator for result.
     *
     * @noinspection PhpUnused
     * @param \Domain\ProvisionReport\LtcsProvisionReportOverScore $result
     */
    protected function setResultAttribute(LtcsProvisionReportOverScore $result): void
    {
        $this->attributes['result_max_benefit_excess_score'] = $result->maxBenefitExcessScore;
        $this->attributes['result_max_benefit_quota_excess_score'] = $result->maxBenefitQuotaExcessScore;
    }
}
