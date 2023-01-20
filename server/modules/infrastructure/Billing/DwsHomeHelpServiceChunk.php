<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk as DomainDwsHomeHelpServiceChunk;
use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Lib\Json;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護） Eloquent モデル.
 *
 * @property int $id ID
 * @property int $user_id 利用者ID
 * @property int $category_value サービスコード区分値
 * @property int $building_type_value 建物区分値
 * @property bool $is_emergency 緊急時対応
 * @property bool $is_planned_by_novice 初計
 * @property \Domain\Common\Carbon $range 全体の時間範囲
 * @property \Domain\Billing\DwsHomeHelpServiceFragment[]|\ScalikePHP\Seq $fragments 要素
 */
final class DwsHomeHelpServiceChunk extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_home_help_service_chunk';

    /** {@inheritdoc} */
    protected $connection = 'temporary';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_id',
        'category',
        'building_type',
        'is_emergency',
        'is_first',
        'is_welfare_specialist_cooperation',
        'is_planned_by_novice',
        'range',
        'fragments',
    ];

    /**
     * {@inheritdoc}
     *
     * Sqliteの場合intもcast指定が必要: https://github.com/laravel/framework/issues/3548#issuecomment-34982457
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'category_value' => 'integer',
        'building_type_value' => 'integer',
        'is_emergency' => 'boolean',
        'is_first' => 'boolean',
        'is_welfare_specialist_cooperation' => 'boolean',
        'is_planned_by_novice' => 'boolean',
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsHomeHelpServiceChunk
    {
        $values = [
            'fragments' => Seq::fromArray(Json::decode($this->fragments, true))
                ->map(fn (array $x): DwsHomeHelpServiceFragment => DwsHomeHelpServiceFragment::create(
                    [
                        'providerType' => DwsHomeHelpServiceProviderType::from($x['providerType']),
                        'range' => CarbonRange::create([
                            'start' => Carbon::create($x['range']['start']),
                            'end' => Carbon::create($x['range']['end']),
                        ]),
                    ]
                    + $x
                )),
        ];
        return DwsHomeHelpServiceChunkImpl::create($values + $this->toDomainValues());
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'category',
            'buildingType',
            'range',
        ];

        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $domain
     * @return \Infrastructure\Billing\DwsHomeHelpServiceChunk
     */
    public static function fromDomain(DomainDwsHomeHelpServiceChunk $domain): self
    {
        $keys = [
            'id',
            'user_id',
            'category',
            'building_type',
            'is_emergency',
            'is_first',
            'is_welfare_specialist_cooperation',
            'is_planned_by_novice',
            'range',
        ];
        $values = ['fragments' => json_encode($domain->fragments->toArray())]
            + self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }

    /**
     * Get mutator for category.
     *
     * @return \Domain\ServiceCodeDictionary\DwsServiceCodeCategory
     * @noinspection PhpUnused
     */
    protected function getCategoryAttribute(): DwsServiceCodeCategory
    {
        return DwsServiceCodeCategory::from($this->category_value);
    }

    /**
     * Set mutator for category.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @noinspection PhpUnused
     */
    protected function setCategoryAttribute(DwsServiceCodeCategory $category): void
    {
        $this->attributes['category_value'] = $category->value();
    }

    /**
     * Get mutator for buildingType.
     *
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType
     * @noinspection PhpUnused
     */
    protected function getBuildingTypeAttribute(): DwsHomeHelpServiceBuildingType
    {
        return DwsHomeHelpServiceBuildingType::from($this->building_type_value);
    }

    /**
     * Set mutator for buildingType.
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType $buildingType
     * @noinspection PhpUnused
     */
    protected function setBuildingTypeAttribute(DwsHomeHelpServiceBuildingType $buildingType): void
    {
        $this->attributes['building_type_value'] = $buildingType->value();
    }

    /**
     * Get mutator for range.
     *
     * @return \Domain\Common\CarbonRange
     * @noinspection PhpUnused
     */
    protected function getRangeAttribute(): CarbonRange
    {
        return CarbonRange::create([
            'start' => Carbon::parse($this->attributes['range_start']),
            'end' => Carbon::parse($this->attributes['range_end']),
        ]);
    }

    /**
     * Set mutator for range.
     *
     * @param \Domain\Common\CarbonRange $range
     * @noinspection PhpUnused
     */
    protected function setRangeAttribute(CarbonRange $range): void
    {
        $this->attributes['range_start'] = $range->start;
        $this->attributes['range_end'] = $range->end;
    }
}
