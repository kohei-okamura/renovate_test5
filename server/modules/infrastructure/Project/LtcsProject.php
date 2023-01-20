<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\LtcsProject as DomainLtcsProject;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：計画 Eloquent モデル.
 *
 * @property int $id 計画ID
 * @property int $organization_id 事業者ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\Project\LtcsProjectAttr $attr
 */
final class LtcsProject extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_project';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['attr'];

    /**
     * HasOne: {@link \Infrastructure\Project\LtcsProjectAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(LtcsProjectAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsProject
    {
        return DomainLtcsProject::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\LtcsProject $domain
     * @return \Infrastructure\Project\LtcsProject
     */
    public static function fromDomain(DomainLtcsProject $domain): self
    {
        $keys = [
            'id',
            'organization_id',
            'created_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
