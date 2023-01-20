<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\Common\IntRange;
use Domain\ServiceCodeDictionary\LtcsCalcCycle;
use Domain\ServiceCodeDictionary\LtcsCalcExtraScore;
use Domain\ServiceCodeDictionary\LtcsCalcScore;
use Domain\ServiceCodeDictionary\LtcsCalcType;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry as DomainLtcsHomeVisitLongTermCareDictionaryEntry;
use Infrastructure\Concerns\IntRangeMutator;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Office\CastsHomeVisitLongTermCareSpecifiedOfficeAddition;
use Infrastructure\ServiceCode\ServiceCodeHolder;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ Eloquent モデル.
 *
 * @property int $id 辞書エントリ ID
 * @property int $dictionary_id 辞書 ID
 * @property string $name 名称
 * @property \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory $category サービスコード区分
 * @property int $headcount 提供人数
 * @property \Domain\ServiceCodeDictionary\LtcsCompositionType $composition_type 合成識別区分
 * @property \Domain\ServiceCodeDictionary\LtcsNoteRequirement $note_requirement 摘要欄記載要件
 * @property bool $is_limited 支給限度額対象
 * @property bool $is_bulk_subtraction_target 同一建物減算対象
 * @property bool $is_symbiotic_subtraction_target 共生型減算対象
 * @property \Domain\ServiceCodeDictionary\LtcsCalcScore $score 算定単位数
 * @property \Domain\ServiceCodeDictionary\LtcsCalcExtraScore $extra_score きざみ単位数
 * @property \Domain\Common\IntRange $totalMinutes 合計時間数
 * @property \Domain\Common\IntRange $physicalMinutes 身体時間数
 * @property \Domain\Common\IntRange $houseworkMinutes 生活時間数
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @method static \Illuminate\Database\Eloquent\Builder|static newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDictionaryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHeadcount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCompositionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereNoteRequirement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereIsLimited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereIsBulkSubtractionTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereIsSymbioticSubtractionTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereScoreValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereScoreCalcType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereScoreCalcCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereExtraScoreIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereExtraScoreBaseMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereExtraScoreUnitScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereExtraScoreUnitMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereExtraScoreSpecifiedOfficeAdditionCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereExtraScoreTimeframeAdditionCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalMinutesStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalMinutesEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static wherePhysicalMinutesStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static wherePhysicalMinutesEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHouseworkMinutesStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHouseworkMinutesEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUpdatedAt($value)
 */
final class LtcsHomeVisitLongTermCareDictionaryEntry extends Model implements Domainable
{
    use IntRangeMutator;
    use ServiceCodeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_home_visit_long_term_care_dictionary_entry';

    /**
     * 属性.
     */
    private const ATTRIBUTES = [
        'id',
        'dictionary_id',
        'service_code',
        'name',
        'category',
        'headcount',
        'composition_type',
        'specified_office_addition',
        'note_requirement',
        'is_limited',
        'is_bulk_subtraction_target',
        'is_symbiotic_subtraction_target',
        'score',
        'extra_score',
        'timeframe',
        'total_minutes',
        'physical_minutes',
        'housework_minutes',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'is_limited' => 'bool',
        'is_bulk_subtraction_target' => 'bool',
        'is_symbiotic_subtraction_target' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'timeframe' => CastsTimeframe::class,
        'category' => CastsLtcsServiceCodeCategory::class,
        'composition_type' => CastsLtcsCompositionType::class,
        'specified_office_addition' => CastsHomeVisitLongTermCareSpecifiedOfficeAddition::class,
        'note_requirement' => CastsLtcsNoteRequirement::class,
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry $domain
     * @return static
     */
    public static function fromDomain(DomainLtcsHomeVisitLongTermCareDictionaryEntry $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsHomeVisitLongTermCareDictionaryEntry
    {
        $attrs = $this->toDomainAttributes(self::ATTRIBUTES);
        return DomainLtcsHomeVisitLongTermCareDictionaryEntry::create($attrs);
    }

    /**
     * Get mutator for score attribute.
     *
     * @return \Domain\ServiceCodeDictionary\LtcsCalcScore
     * @noinspection PhpUnused
     */
    protected function getScoreAttribute(): LtcsCalcScore
    {
        return LtcsCalcScore::create([
            'value' => $this->attributes['score_value'],
            'calcType' => LtcsCalcType::from($this->attributes['score_calc_type']),
            'calcCycle' => LtcsCalcCycle::from($this->attributes['score_calc_cycle']),
        ]);
    }

    /**
     * Set mutator for score attribute.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsCalcScore $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setScoreAttribute(LtcsCalcScore $value): void
    {
        $this->attributes['score_value'] = $value->value;
        $this->attributes['score_calc_type'] = $value->calcType->value();
        $this->attributes['score_calc_cycle'] = $value->calcCycle->value();
    }

    /**
     * Get mutator for extra_score attribute.
     *
     * @return \Domain\ServiceCodeDictionary\LtcsCalcExtraScore
     * @noinspection PhpUnused
     */
    protected function getExtraScoreAttribute(): LtcsCalcExtraScore
    {
        return LtcsCalcExtraScore::create([
            'isAvailable' => (bool)$this->attributes['extra_score_is_available'],
            'baseMinutes' => $this->attributes['extra_score_base_minutes'],
            'unitScore' => $this->attributes['extra_score_unit_score'],
            'unitMinutes' => $this->attributes['extra_score_unit_minutes'],
            'specifiedOfficeAdditionCoefficient' => $this->attributes['extra_score_specified_office_addition_coefficient'],
            'timeframeAdditionCoefficient' => $this->attributes['extra_score_timeframe_addition_coefficient'],
        ]);
    }

    /**
     * Set mutator for extra_score attribute.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsCalcExtraScore $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setExtraScoreAttribute(LtcsCalcExtraScore $value): void
    {
        $this->attributes['extra_score_is_available'] = $value->isAvailable;
        $this->attributes['extra_score_base_minutes'] = $value->baseMinutes;
        $this->attributes['extra_score_unit_score'] = $value->unitScore;
        $this->attributes['extra_score_unit_minutes'] = $value->unitMinutes;
        $this->attributes['extra_score_specified_office_addition_coefficient'] = $value->specifiedOfficeAdditionCoefficient;
        $this->attributes['extra_score_timeframe_addition_coefficient'] = $value->timeframeAdditionCoefficient;
    }

    /**
     * Get mutator for total_minutes attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getTotalMinutesAttribute(): IntRange
    {
        return $this->getIntRange('total_minutes');
    }

    /**
     * Set mutator for total_minutes attribute.
     *
     * @param \Domain\Common\IntRange $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setTotalMinutesAttribute(IntRange $value): void
    {
        $this->setIntRange($value, 'total_minutes');
    }

    /**
     * Get mutator for physical_minutes attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getPhysicalMinutesAttribute(): IntRange
    {
        return $this->getIntRange('physical_minutes');
    }

    /**
     * Set mutator for physical_minutes attribute.
     *
     * @param \Domain\Common\IntRange $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setPhysicalMinutesAttribute(IntRange $value): void
    {
        $this->setIntRange($value, 'physical_minutes');
    }

    /**
     * Get mutator for housework_minutes attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getHouseworkMinutesAttribute(): IntRange
    {
        return $this->getIntRange('housework_minutes');
    }

    /**
     * Set mutator for housework_minutes attribute.
     *
     * @param \Domain\Common\IntRange $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setHouseworkMinutesAttribute(IntRange $value): void
    {
        $this->setIntRange($value, 'housework_minutes');
    }
}
