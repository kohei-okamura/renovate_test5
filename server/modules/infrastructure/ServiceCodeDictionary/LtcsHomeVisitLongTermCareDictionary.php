<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary as DomainLtcsHomeVisitLongTermCareDictionary;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書 Eloquent モデル.
 *
 * @property int $id 辞書 ID
 * @property \Domain\Common\Carbon $effectivated_on 適用開始日
 * @property string $name 名前
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[] $entries
 * @method static \Illuminate\Database\Eloquent\Builder|static newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereEffectivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUpdatedAt($value)
 */
final class LtcsHomeVisitLongTermCareDictionary extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_home_visit_long_term_care_dictionary';

    /**
     * 属性.
     */
    private const ATTRIBUTES = [
        'id',
        'effectivated_on',
        'name',
        'version',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'effectivated_on' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary $domain
     * @return static
     */
    public static function fromDomain(DomainLtcsHomeVisitLongTermCareDictionary $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsHomeVisitLongTermCareDictionary
    {
        $attrs = $this->toDomainAttributes(self::ATTRIBUTES);
        return DomainLtcsHomeVisitLongTermCareDictionary::create($attrs);
    }
}
