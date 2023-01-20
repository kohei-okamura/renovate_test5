<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\LtcsProjectContent as DomainLtcsProjectContent;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：計画：サービス詳細 Eloquent モデル.
 *
 * @property int $ltcs_project_program_id 介護保険サービス：計画：週間サービス計画ID
 * @property int $ltcs_project_service_menu_id サービス内容ID
 * @property int $sort_order 表示順
 * @property null|int $duration 所要時間
 * @property string $content サービスの具体的内容
 * @property string $memo 留意事項
 */
final class LtcsProjectContent extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_project_content';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'ltcs_project_program_id',
        'ltcs_project_service_menu_id',
        'sort_order',
        'duration',
        'content',
        'memo',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\LtcsProjectContent $domain
     * @param array $additional
     * @return \Infrastructure\Project\LtcsProjectContent
     */
    public static function fromDomain(DomainLtcsProjectContent $domain, array $additional): self
    {
        $keys = [
            'duration',
            'content',
            'memo',
        ];
        $values = ['ltcs_project_service_menu_id' => $domain->menuId] + self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsProjectContent
    {
        return DomainLtcsProjectContent::create(
            ['menuId' => $this->ltcs_project_service_menu_id] + $this->toDomainValues()
        );
    }
}
