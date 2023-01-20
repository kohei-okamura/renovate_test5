<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Organization;

use Domain\Organization\Organization as DomainOrganization;
use Infrastructure\Common\AddrHolder;
use Infrastructure\Model;

/**
 * 事業者属性 Eloquent モデル.
 *
 * @property int $id 事業者属性ID
 * @property string $name 事業者名
 * @property string $addr_postcode 郵便番号
 * @property int $addr_prefecture_id 都道府県ID
 * @property string $addr_city 市区町村
 * @property string $addr_street 町名・番地
 * @property string $addr_apartment 建物名など
 * @property string $tel 電話番号
 * @property string $fax FAX番号
 * @property int $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @method static \Illuminate\Database\Eloquent\Builder|static newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrApartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrPostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrPrefectureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereVersion($value)
 * @mixin \Eloquent
 */
final class OrganizationAttr extends Model
{
    use AddrHolder;
    use BelongsToOrganization;

    /**
     * テーブル名.
     */
    public const TABLE = 'organization_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'name',
        'addr',
        'tel',
        'fax',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Organization\Organization $domain
     * @return \Infrastructure\Organization\OrganizationAttr
     */
    public static function fromDomain(DomainOrganization $domain): self
    {
        $keys = [
            'name',
            'addr',
            'tel',
            'fax',
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
        return $this->only(['addr']) + parent::toDomainValues();
    }
}
