<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\DwsProjectContent as DomainDwsProjectContent;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス：計画：サービス詳細 Eloquent モデル.
 *
 * @property-read int $dws_project_program_id 障害福祉サービス：計画：週間サービス計画ID
 * @property int $dws_project_service_menu_id サービス内容ID
 * @property int $sort_order 表示順
 * @property null|int $duration 所要時間
 * @property string $content サービスの具体的内容
 * @property string $memo 留意事項
 */
final class DwsProjectContent extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_project_content';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_project_program_id',
        'dws_project_service_menu_id',
        'sort_order',
        'duration',
        'content',
        'memo',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\DwsProjectContent $domain
     * @param array $additional
     * @return \Infrastructure\Project\DwsProjectContent
     */
    public static function fromDomain(DomainDwsProjectContent $domain, array $additional): self
    {
        $keys = [
            'duration',
            'content',
            'memo',
        ];
        $values = ['dws_project_service_menu_id' => $domain->menuId] + self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsProjectContent
    {
        return DomainDwsProjectContent::create(
            ['menuId' => $this->dws_project_service_menu_id] + $this->toDomainValues()
        );
    }
}
