<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\HomeVisitLongTermCareCalcSpec as DomainHomeVisitLongTermCareCalcSpec;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infrastructure\Common\PeriodHolder;
use Infrastructure\Model;

/**
 * 事業所算定情報（介保・訪問介護）属性 Eloquent モデル.
 *
 * @property int $id 属性ID
 */
final class HomeVisitLongTermCareCalcSpecAttr extends Model
{
    use PeriodHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'home_visit_long_term_care_calc_spec_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'home_visit_long_term_care_calc_spec_id',
        'period',
        'specified_office_addition',
        'treatment_improvement_addition',
        'specified_treatment_improvement_addition',
        'location_addition',
        'base_increase_support_addition',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_enabled' => 'boolean',
        'period_start' => 'date',
        'period_end' => 'date',
        'updated_at' => 'datetime',
        'specified_office_addition' => CastsHomeVisitLongTermCareSpecifiedOfficeAddition::class,
        'treatment_improvement_addition' => CastsLtcsTreatmentImprovementAddition::class,
        'specified_treatment_improvement_addition' => CastsLtcsSpecifiedTreatmentImprovementAddition::class,
        'base_increase_support_addition' => CastsLtcsBaseIncreaseSupportAddition::class,
        'location_addition' => CastsLtcsOfficeLocationAddition::class,
    ];

    /**
     * BelongsTo: {@link \Infrastructure\Office\HomeVisitLongTermCareCalcSpec}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function homeVisitLongTermCareCalcSpec(): BelongsTo
    {
        return $this->belongsTo(HomeVisitLongTermCareCalcSpec::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\HomeVisitLongTermCareCalcSpec $domain
     * @return \Infrastructure\Office\HomeVisitLongTermCareCalcSpecAttr
     */
    public static function fromDomain(DomainHomeVisitLongTermCareCalcSpec $domain): self
    {
        $keys = [
            'period',
            'specified_office_addition',
            'treatment_improvement_addition',
            'specified_treatment_improvement_addition',
            'location_addition',
            'base_increase_support_addition',
            'is_enabled',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'period',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }
}
