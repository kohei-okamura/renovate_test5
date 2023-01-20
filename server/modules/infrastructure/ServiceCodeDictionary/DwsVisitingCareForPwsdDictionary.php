<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary as DomainDwsVisitingCareForPwsdDictionary;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス：重度訪問介護：サービスコード辞書 Eloquent モデル.
 *
 * @property int $id 辞書ID
 * @property \Domain\Common\Carbon $effectivated_on 適用開始日
 * @property string $name 名前
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[] $entries
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary whereEffectivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsVisitingCareForPwsdDictionary whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class DwsVisitingCareForPwsdDictionary extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_visiting_care_for_pwsd_dictionary';

    public $incrementing = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'effectivated_on',
        'name',
        'version',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'effectivated_on' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * HasMany: {@link \Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries(): HasMany
    {
        return $this->hasMany(DwsVisitingCareForPwsdDictionaryEntry::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsVisitingCareForPwsdDictionary
    {
        $entries = [
            'entries' => $this->entries->map(fn (DwsVisitingCareForPwsdDictionaryEntry $x) => $x->toDomain())->all(),
        ];
        return DomainDwsVisitingCareForPwsdDictionary::create($this->toDomainValues() + $entries);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary $domain
     * @return \Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary
     */
    public static function fromDomain(DomainDwsVisitingCareForPwsdDictionary $domain): self
    {
        $keys = ['id', 'effectivated_on', 'name', 'version', 'created_at', 'updated_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
