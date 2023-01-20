<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\HomeHelpServiceCalcSpec as DomainHomeHelpServiceCalcSpec;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infrastructure\Common\PeriodHolder;
use Infrastructure\Model;

/**
 * 事業所算定情報（障害・居宅介護）属性 Eloquent モデル.
 *
 * @property int $id 属性ID
 * @property \Domain\Common\CarbonRange $period 適用期間
 * @property \Domain\Office\HomeHelpServiceSpecifiedOfficeAddition $specified_office_addition 特定事業所加算
 * @property \Domain\Office\DwsTreatmentImprovementAddition $treatment_improvement_addition 処遇改善加算
 * @property \Domain\Office\DwsSpecifiedTreatmentImprovementAddition $specified_treatment_improvement_addition 特定処遇改善加算
 * @property \Domain\Office\DwsBaseIncreaseSupportAddition $base_increase_support_addition ベースアップ等支援加算
 */
final class HomeHelpServiceCalcSpecAttr extends Model
{
    use PeriodHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'home_help_service_calc_spec_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'home_help_service_calc_spec_id',
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
        'specified_office_addition' => CastsHomeHelpServiceSpecifiedOfficeAddition::class,
        'treatment_improvement_addition' => CastsDwsTreatmentImprovementAddition::class,
        'specified_treatment_improvement_addition' => CastsDwsSpecifiedTreatmentImprovementAddition::class,
        'base_increase_support_addition' => CastsDwsBaseIncreaseSupportAddition::class,
    ];

    /**
     * BelongsTo: {@link \Infrastructure\Office\HomeHelpServiceCalcSpec}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function homeHelpServiceCalcSpec(): BelongsTo
    {
        return $this->belongsTo(HomeHelpServiceCalcSpec::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\HomeHelpServiceCalcSpec $domain
     * @return \Infrastructure\Office\HomeHelpServiceCalcSpecAttr
     */
    public static function fromDomain(DomainHomeHelpServiceCalcSpec $domain): self
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
