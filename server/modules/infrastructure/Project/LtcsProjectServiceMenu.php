<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\LtcsProjectServiceMenu as DomainLtcsProjectServiceMenu;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：計画：サービス内容 Eloquent モデル.
 *
 * @property int $id 介護保険サービス：計画：サービス内容ID
 * @property \Domain\Project\LtcsProjectServiceCategory $category サービス区分
 * @property string $name 名称
 * @property string $display_name 表示名
 * @property int $sort_order 表示順
 * @property \Domain\Common\Carbon $created_at 登録日時
 */
final class LtcsProjectServiceMenu extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_project_service_menu';

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
        'category' => CastsLtcsProjectServiceCategory::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsProjectServiceMenu
    {
        return DomainLtcsProjectServiceMenu::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\LtcsProjectServiceMenu $domain
     * @return \Infrastructure\Project\LtcsProjectServiceMenu
     */
    public static function fromDomain(DomainLtcsProjectServiceMenu $domain): self
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
