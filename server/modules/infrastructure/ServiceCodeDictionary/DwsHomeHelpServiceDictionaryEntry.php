<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\Common\IntRange;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry as DomainDwsHomeHelpServiceDictionaryEntry;
use Infrastructure\Concerns\IntRangeMutator;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\ServiceCode\ServiceCodeHolder;

/**
 * サービスコード辞書エントリ（障害：居宅介護）Eloquent モデル.
 *
 * @property int $id サービスコード辞書エントリ（障害：居宅介護）ID
 * @property int $dws_home_help_service_dictionary_id 障害福祉サービス：居宅介護：サービスコード辞書ID
 * @property string $name 名称
 * @property \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property bool $is_extra 増分
 * @property bool $is_secondary 2人（2人目の居宅介護従業者による場合）
 * @property \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType $provider_type 提供者区分
 * @property bool $is_planned_by_novice 初計（初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合）
 * @property \Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType $building_type 障害居宅介護建物区分
 * @property int $score 単位数
 * @property \Domain\Common\IntRange $daytime_duration 時間数（日中）
 * @property \Domain\Common\IntRange $morning_duration 時間数（早朝）
 * @property \Domain\Common\IntRange $night_duration 時間数（夜間）
 * @property \Domain\Common\IntRange $midnight_duration1 時間数（深夜1）
 * @property \Domain\Common\IntRange $midnight_duration2 時間数（深夜2）
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereDwsHomeHelpServiceDictionaryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereServiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereIsExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereIsSecondary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereProviderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereisPlannedByNovice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereBuildingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereDaytimeDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereMorningDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereNightDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereMidnightDuration1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsHomeHelpServiceDictionaryEntry whereMidnightDuration2($value)
 * @mixin \Eloquent
 */
final class DwsHomeHelpServiceDictionaryEntry extends Model implements Domainable
{
    use IntRangeMutator;
    use ServiceCodeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_home_help_service_dictionary_entry';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_home_help_service_dictionary_id',
        'service_code',
        'name',
        'category',
        'is_extra',
        'is_secondary',
        'provider_type',
        'is_planned_by_novice',
        'building_type',
        'score',
        'daytime_duration',
        'morning_duration',
        'night_duration',
        'midnight_duration1',
        'midnight_duration2',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_extra' => 'boolean',
        'is_secondary' => 'boolean',
        'is_planned_by_novice' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'category' => CastsDwsServiceCodeCategory::class,
        'provider_type' => CastsDwsHomeHelpServiceProviderType::class,
        'building_type' => CastsDwsHomeHelpServiceBuildingType::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsHomeHelpServiceDictionaryEntry
    {
        return DomainDwsHomeHelpServiceDictionaryEntry::create($this->toDomainValues());
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'daytimeDuration',
            'morningDuration',
            'nightDuration',
            'midnightDuration1',
            'midnightDuration2',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry $domain
     * @return \Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry
     */
    public static function fromDomain(DomainDwsHomeHelpServiceDictionaryEntry $domain): self
    {
        $keys = [
            'id',
            'dws_home_help_service_dictionary_id',
            'service_code',
            'name',
            'category',
            'is_extra',
            'is_secondary',
            'provider_type',
            'is_planned_by_novice',
            'building_type',
            'score',
            'daytime_duration',
            'morning_duration',
            'night_duration',
            'midnight_duration1',
            'midnight_duration2',
            'created_at',
            'updated_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }

    /**
     * Get mutator for daytime_duration attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getDaytimeDurationAttribute(): IntRange
    {
        return $this->getIntRange('daytime_duration');
    }

    /**
     * Set mutator for daytime_duration attribute.
     *
     * @param \Domain\Common\IntRange $daytimeDuration
     * @return void
     * @noinspection PhpUnused
     */
    protected function setDaytimeDurationAttribute(IntRange $daytimeDuration): void
    {
        $this->setIntRange($daytimeDuration, 'daytime_duration');
    }

    /**
     * Get mutator for morning_duration attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getMorningDurationAttribute(): IntRange
    {
        return $this->getIntRange('morning_duration');
    }

    /**
     * Set mutator for morning_duration attribute.
     *
     * @param \Domain\Common\IntRange $morningDuration
     * @return void
     * @noinspection PhpUnused
     */
    protected function setMorningDurationAttribute(IntRange $morningDuration): void
    {
        $this->setIntRange($morningDuration, 'morning_duration');
    }

    /**
     * Get mutator for night_duration attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getNightDurationAttribute(): IntRange
    {
        return $this->getIntRange('night_duration');
    }

    /**
     * Set mutator for night_duration attribute.
     *
     * @param \Domain\Common\IntRange $nightDuration
     * @return void
     * @noinspection PhpUnused
     */
    protected function setNightDurationAttribute(IntRange $nightDuration): void
    {
        $this->setIntRange($nightDuration, 'night_duration');
    }

    /**
     * Get mutator for midnight_duration1 attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getMidnightDuration1Attribute(): IntRange
    {
        return $this->getIntRange('midnight_duration1');
    }

    /**
     * Set mutator for midnight_duration1 attribute.
     *
     * @param \Domain\Common\IntRange $midnightDuration1
     * @return void
     * @noinspection PhpUnused
     */
    protected function setMidnightDuration1Attribute(IntRange $midnightDuration1): void
    {
        $this->setIntRange($midnightDuration1, 'midnight_duration1');
    }

    /**
     * Get mutator for midnight_duration2 attribute.
     *
     * @return \Domain\Common\IntRange
     * @noinspection PhpUnused
     */
    protected function getMidnightDuration2Attribute(): IntRange
    {
        return $this->getIntRange('midnight_duration2');
    }

    /**
     * Set mutator for midnight_duration2 attribute.
     *
     * @param \Domain\Common\IntRange $midnightDuration2
     * @return void
     * @noinspection PhpUnused
     */
    protected function setMidnightDuration2Attribute(IntRange $midnightDuration2): void
    {
        $this->setIntRange($midnightDuration2, 'midnight_duration2');
    }
}
