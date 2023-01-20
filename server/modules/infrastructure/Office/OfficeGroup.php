<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\OfficeGroup as DomainOfficeGroup;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Organization\BelongsToOrganization;

/**
 * 事業所グループ Eloquent モデル.
 *
 * @property int $id 事業所グループID
 * @property null|int $parent_office_group_id 上位事業所グループID
 * @property string $name 事業所グループ名
 * @property int $sort_order 表示順
 * @property string $created_at 登録日時
 * @property string $updated_at 更新日時
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup whereParentOfficeGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficeGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class OfficeGroup extends Model implements Domainable
{
    use BelongsToOrganization;

    /**
     * テーブル名.
     */
    public const TABLE = 'office_group';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'parent_office_group_id',
        'name',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainOfficeGroup
    {
        return DomainOfficeGroup::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\OfficeGroup $domain
     * @return \Infrastructure\Office\OfficeGroup
     */
    public static function fromDomain(DomainOfficeGroup $domain): self
    {
        $keys = ['id', 'organization_id', 'parent_office_group_id', 'name', 'sort_order', 'created_at', 'updated_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
