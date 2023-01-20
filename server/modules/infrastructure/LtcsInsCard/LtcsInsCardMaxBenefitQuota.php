<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsInsCard;

use Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota as DomainLtcsInsCardMaxBenefitQuota;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険被保険者証・種類支給限度基準額 Eloquent モデル.
 *
 * @property int $id 種類支給限度基準額ID
 * @property int $ltcs_ins_card_attr_id 介護保険被保険者証属性ID
 * @property \Domain\LtcsInsCard\LtcsInsCardServiceType $ltcs_ins_card_service_type サービス内容
 * @property int $max_benefit_quota 種類支給限度基準額
 * @property int $sort_order 表示順
 * @mixin \Eloquent
 */
final class LtcsInsCardMaxBenefitQuota extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_ins_card_max_benefit_quota';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'ltcs_ins_card_attr_id',
        'ltcs_ins_card_service_type',
        'max_benefit_quota',
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'ltcs_ins_card_service_type' => CastsLtcsInsCardServiceType::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsInsCardMaxBenefitQuota
    {
        return DomainLtcsInsCardMaxBenefitQuota::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota $domain
     * @param array $values
     * @return \Infrastructure\LtcsInsCard\LtcsInsCardMaxBenefitQuota
     */
    public static function fromDomain(DomainLtcsInsCardMaxBenefitQuota $domain, array $values): self
    {
        $keys = ['ltcs_ins_card_service_type', 'max_benefit_quota'];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs + $values);
    }
}
