<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsAreaGrade;

use Domain\LtcsAreaGrade\LtcsAreaGradeFee as DomainLtcsAreaGradeFee;
use Infrastructure\Common\CastsDecimal;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：地域区分単価 Eloquent モデル.
 *
 * @property int $id 単価 ID
 * @property int $ltcs_area_grade_id 地域区分 ID
 * @property \Domain\Common\Carbon $effectivated_on 適用日
 * @property \Domain\Common\Decimal $fee 単価
 * @mixin \Eloquent
 */
final class LtcsAreaGradeFee extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_area_grade_fee';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'ltcs_area_grade_id',
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
     * @param \Domain\LtcsAreaGrade\LtcsAreaGradeFee $domain
     * @return \Infrastructure\LtcsAreaGrade\LtcsAreaGradeFee
     */
    public static function fromDomain(DomainLtcsAreaGradeFee $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsAreaGradeFee
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainLtcsAreaGradeFee::create($attrs);
    }
}
