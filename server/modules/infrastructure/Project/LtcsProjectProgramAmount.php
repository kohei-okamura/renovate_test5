<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\LtcsProjectAmount as DomainLtcsProjectAmount;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：計画：週間サービス計画：サービス提供量 Eloquent モデル.
 *
 * @property int $id 介護保険サービス：計画：週間サービス計画：サービス提供量ID
 * @property int $ltcs_project_program_id 介護保険サービス：計画：週間サービス計画ID
 * @property int $sort_order 表示順
 * @property \Domain\Project\LtcsProjectAmountCategory $category サービス区分
 * @property int $amount サービス時間
 */
final class LtcsProjectProgramAmount extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_project_program_amount';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'ltcs_project_program_id',
        'sort_order',
        'category',
        'amount',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'category' => CastsLtcsProjectAmountCategory::class,
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\LtcsProjectAmount $domain
     * @param array $additional
     * @return \Infrastructure\Project\LtcsProjectProgramAmount
     */
    public static function fromDomain(DomainLtcsProjectAmount $domain, array $additional): self
    {
        $keys = [
            'category',
            'amount',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsProjectAmount
    {
        return DomainLtcsProjectAmount::create(parent::toDomainValues());
    }
}
