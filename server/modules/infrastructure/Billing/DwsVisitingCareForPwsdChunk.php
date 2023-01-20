<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk as DomainDwsVisitingCareForPwsdChunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl as DomainDwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Infrastructure\Common\CastsDateAsInt;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Lib\Json;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護） Eloquent Model.
 *
 * @property int $id
 * @property int $user_id 利用者ID
 * @property int $category サービスコード区分
 * @property bool $is_emergency 緊急時対応フラグ
 * @property \Domain\Common\Carbon $provided_on サービス提供日
 * @property \Domain\Common\CarbonRange $range 全体の時間範囲
 * @property \Domain\Billing\DwsVisitingCareForPwsdFragment[]|\ScalikePHP\Seq $fragments 要素
 */
final class DwsVisitingCareForPwsdChunk extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_visiting_care_for_pwsd_chunk';

    /** {@inheritdoc} */
    protected $connection = 'temporary';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_id',
        'category',
        'is_emergency',
        'is_first',
        'is_behavioral_disorder_support_cooperation',
        'provided_on',
        'range',
        'fragments',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'category' => 'integer',
        'is_emergency' => 'boolean',
        'is_first' => 'boolean',
        'is_behavioral_disorder_support_cooperation' => 'boolean',
        'provided_on' => CastsDateAsInt::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function toDomain(): DomainDwsVisitingCareForPwsdChunk
    {
        $values = [
            'fragments' => Seq::fromArray(Json::decode($this->fragments, true))
                ->map(fn (array $x): DwsVisitingCareForPwsdFragment => DwsVisitingCareForPwsdFragment::create(
                    [
                        'range' => CarbonRange::create([
                            'start' => Carbon::create($x['range']['start']),
                            'end' => Carbon::create($x['range']['end']),
                        ]),
                    ]
                    + $x
                )),
        ];
        return DomainDwsVisitingCareForPwsdChunkImpl::create($values + $this->toDomainValues());
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'category',
            'range',
        ];

        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $domain
     * @return static
     */
    public static function fromDomain(DomainDwsVisitingCareForPwsdChunk $domain): self
    {
        $keys = [
            'id',
            'user_id',
            'category',
            'is_emergency',
            'is_first',
            'is_behavioral_disorder_support_cooperation',
            'provided_on',
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
        return DwsServiceCodeCategory::from((int)$this->attributes['category']);
    }

    /**
     * Set mutator for category.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @noinspection PhpUnused
     */
    protected function setCategoryAttribute(DwsServiceCodeCategory $category): void
    {
        $this->attributes['category'] = $category->value();
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
