<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsAreaGrade;

use Domain\DwsAreaGrade\DwsAreaGradeFee as DomainDwsAreaGradeFee;
use Infrastructure\Common\CastsDecimal;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス：地域区分単価 Eloquent モデル.
 *
 * @property int $id 単価 ID
 * @property int $dws_area_grade_id 地域区分 ID
 * @property \Domain\Common\Carbon $effectivated_on 適用日
 * @property int $fee 単価
 * @property \Domain\Common\Carbon $created_at
 * @property \Domain\Common\Carbon $updated_at
 * @mixin \Eloquent
 */
class DwsAreaGradeFee extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_area_grade_fee';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'dws_area_grade_id',
        'effectivated_on',
        'fee',
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
        'effectivated_on' => 'date',
        'fee' => CastsDecimal::class,
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGradeFee $domain
     * @return \Infrastructure\DwsAreaGrade\DwsAreaGradeFee
     */
    public static function fromDomain(DomainDwsAreaGradeFee $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsAreaGradeFee
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainDwsAreaGradeFee::create($attrs);
    }
}
