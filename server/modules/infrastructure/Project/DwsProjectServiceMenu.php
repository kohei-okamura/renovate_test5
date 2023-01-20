<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\DwsProjectServiceMenu as DomainDwsProjectServiceMenu;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス：計画：サービス内容 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス：計画：サービス内容ID
 * @property \Domain\Project\DwsProjectServiceCategory $category サービス区分
 * @property string $name 名称
 * @property string $display_name 表示名
 * @property int $sort_order 表示順
 * @property \Domain\Common\Carbon $created_at 登録日時
 */
final class DwsProjectServiceMenu extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_project_service_menu';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'category',
        'name',
        'display_name',
        'sort_order',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
        'category' => CastsDwsProjectServiceCategory::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsProjectServiceMenu
    {
        return DomainDwsProjectServiceMenu::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\DwsProjectServiceMenu $domain
     * @return \Infrastructure\Project\DwsProjectServiceMenu
     */
    public static function fromDomain(DomainDwsProjectServiceMenu $domain): self
    {
        $keys = [
            'id',
            'category',
            'name',
            'display_name',
            'sort_order',
            'created_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
