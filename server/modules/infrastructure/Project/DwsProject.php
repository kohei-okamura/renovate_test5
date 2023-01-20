<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\DwsProject as DomainDwsProject;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Organization\BelongsToOrganization;

/**
 * 障害福祉サービス：計画 Eloquent モデル.
 *
 * @property int $id 計画ID
 * @property int $organization_id 事業者ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\Project\DwsProjectAttr $attr
 */
final class DwsProject extends Model implements Domainable
{
    use BelongsToOrganization;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_project';

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
     * HasOne: {@link \Infrastructure\Project\DwsProjectAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(DwsProjectAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsProject
    {
        return DomainDwsProject::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\DwsProject $domain
     * @return \Infrastructure\Project\DwsProject
     */
    public static function fromDomain(DomainDwsProject $domain): self
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
