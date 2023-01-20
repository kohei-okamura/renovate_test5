<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\VisitingCareForPwsdCalcSpec as DomainVisitingCareForPwsdCalcSpec;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infrastructure\Common\PeriodHolder;
use Infrastructure\Model;

/**
 * 事業所算定情報（障害・重度訪問介護）属性 Eloquent モデル.
 *
 * @property int $id 属性ID
 * @property \Domain\Common\CarbonRange $period 適用期間
 * @property \Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition $specified_office_addition 特定事業所加算
 * @property \Domain\Office\DwsTreatmentImprovementAddition $treatment_improvement_addition 処遇改善加算
 * @property \Domain\Office\DwsSpecifiedTreatmentImprovementAddition $specified_treatment_improvement_addition 特定処遇改善加算
 * @property \Domain\Office\DwsBaseIncreaseSupportAddition $base_increase_support_addition ベースアップ等支援加算
 */
final class VisitingCareForPwsdCalcSpecAttr extends Model
{
    use PeriodHolder;

    /**
     * テーブル名
     */
    public const TABLE = 'visiting_care_for_pwsd_calc_spec_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'visiting_care_for_pwsd_calc_spec_id',
        'period',
        'specified_office_addition',
        'treatment_improvement_addition',
        'specified_treatment_improvement_addition',
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
        'specified_office_addition' => CastsVisitingCareForPwsdSpecifiedOfficeAddition::class,
        'treatment_improvement_addition' => CastsDwsTreatmentImprovementAddition::class,
        'specified_treatment_improvement_addition' => CastsDwsSpecifiedTreatmentImprovementAddition::class,
        'base_increase_support_addition' => CastsDwsBaseIncreaseSupportAddition::class,
    ];

    /**
     * BelongsTo: {@link \Infrastructure\Office\VisitingCareForPwsdCalcSpec}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function visitingCareForPwsdCalcSpec(): BelongsTo
    {
        return $this->belongsTo(VisitingCareForPwsdCalcSpec::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec $domain
     * @return \Infrastructure\Office\VisitingCareForPwsdCalcSpecAttr
     */
    public static function fromDomain(DomainVisitingCareForPwsdCalcSpec $domain): self
    {
        $keys = [
            'period',
            'specified_office_addition',
            'treatment_improvement_addition',
            'specified_treatment_improvement_addition',
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
