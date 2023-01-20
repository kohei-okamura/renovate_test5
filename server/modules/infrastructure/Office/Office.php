<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\Office as DomainOffice;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Organization\BelongsToOrganization;

/**
 * 事業所 Eloquent モデル.
 *
 * @property int $id 事業所ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\Office\OfficeAttr $attr
 * @mixin \Eloquent
 */
final class Office extends Model implements Domainable
{
    use BelongsToOrganization;

    /**
     * テーブル名.
     */
    public const TABLE = 'office';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'organization_id',
        'created_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'attr',
        'attr.dwsGenericService',
        'attr.dwsCommAccompanyService',
        'attr.ltcsCareManagementService',
        'attr.ltcsHomeVisitLongTermCareService',
        'attr.ltcsCompHomeVisitingService',
        'attr.qualifications',
    ];

    /**
     * HasOne: {@link \Infrastructure\Office\OfficeAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(OfficeAttr::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Office\Office $domain
     * @return \Infrastructure\Office\Office
     */
    public static function fromDomain(DomainOffice $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainOffice
    {
        return DomainOffice::create($this->toDomainValues() + $this->attr->toDomainValues());
    }
}
