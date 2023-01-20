<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\Common\IntRange;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry as DomainDwsVisitingCareForPwsdDictionaryEntry;
use Infrastructure\Concerns\IntRangeMutator;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\ServiceCode\ServiceCodeHolder;

/**
 * サービスコード辞書エントリ（障害：重度訪問介護）Eloquent モデル.
 *
 * @property int $id サービスコード辞書エントリ（障害：重度訪問介護）ID
 * @property int $dws_visiting_care_for_pwsd_dictionary_id 障害福祉サービス：重度訪問介護：サービスコード辞書ID
 * @property string $name 名称
 * @property int $category サービスコード区分
 * @property bool $isSecondary 2人（2人目の重度訪問介護従業者による場合）
 * @property bool $isCoaching 同行（熟練従業者が同行して支援を行う場合）
 * @property bool $isHospitalized 入院（病院等に入院又は入所中に利用した場合）
 * @property bool $isLongHospitalized 90日（90日以上利用減算）
 * @property int $score 単位数
 * @property \Domain\Common\IntRange $timeframe 時間帯
 * @property int $duration 時間数
 * @property int $unit 単位
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereDwsVisitingCareForPwsdDictionaryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereIsSecondary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereIsCoaching($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereIsHospitalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereIsLongHospitalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereTimeframe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionaryEntry whereDuration($value)
 * @mixin \Eloquent
 */
final class DwsVisitingCareForPwsdDictionaryEntry extends Model implements Domainable
{
    use IntRangeMutator;
    use ServiceCodeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_visiting_care_for_pwsd_dictionary_entry';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_visiting_care_for_pwsd_dictionary_id',
        'service_code',
        'name',
        'category',
        'is_secondary',
        'is_coaching',
        'is_hospitalized',
        'is_long_hospitalized',
        'score',
        'timeframe',
        'duration',
        'unit',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_secondary' => 'boolean',
        'is_coaching' => 'boolean',
        'is_hospitalized' => 'boolean',
        'is_long_hospitalized' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'timeframe' => CastsTimeframe::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsVisitingCareForPwsdDictionaryEntry
    {
        return DomainDwsVisitingCareForPwsdDictionaryEntry::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry $domain
     * @return \Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry
     */
    public static function fromDomain(DomainDwsVisitingCareForPwsdDictionaryEntry $domain): self
    {
        $keys = [
            'id',
            'dws_visiting_care_for_pwsd_dictionary_id',
            'service_code',
            'name',
            'category',
            'is_secondary',
            'is_coaching',
            'is_hospitalized',
            'is_long_hospitalized',
            'score',
            'timeframe',
            'duration',
            'unit',
            'created_at',
            'updated_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'duration',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Get mutator for category attribute.
     *
     * @return \Domain\ServiceCodeDictionary\DwsServiceCodeCategory
     * @noinspection PhpUnused
     */
    protected function getCategoryAttribute(): DwsServiceCodeCategory
    {
        return DwsServiceCodeCategory::from($this->attributes['category']);
    }

    /**
     * Set mutator for category attribute.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return void
     * @noinspection PhpUnused
     */
    protected function setCategoryAttribute(DwsServiceCodeCategory $category): void
    {
        $this->attributes['category'] = $category->value();
    }

    /**
     * Get mutator for duration attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getDurationAttribute(): IntRange
    {
        return $this->getIntRange('duration');
    }

    /**
     * Set mutator for duration attribute.
     *
     * @param \Domain\Common\IntRange $duration
     * @return void
     * @noinspection PhpUnused
     */
    protected function setDurationAttribute(IntRange $duration): void
    {
        $this->setIntRange($duration, 'duration');
    }
}
