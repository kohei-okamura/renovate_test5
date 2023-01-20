<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Contract;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Contract\ContractPeriod as DomainContractPeriod;
use Infrastructure\Billing\CastsDwsServiceDivisionCode;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 契約：障害福祉サービス提供期間 Eloquent モデル.
 *
 * @property int $id ID
 * @property int $contract_attr_id 属性 ID
 * @property \Domain\Billing\DwsServiceDivisionCode $service_division_code サービス種類コード
 * @property null|\Domain\Common\Carbon $start 初回サービス提供日
 * @property null|\Domain\Common\Carbon $end 最終サービス提供日
 * @mixin \Eloquent
 */
final class ContractAttrDwsPeriod extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'contract_attr_dws_period';
    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'start',
        'end',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'contract_attr_id',
        'service_division_code',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'service_division_code' => CastsDwsServiceDivisionCode::class,
        'start' => 'date',
        'end' => 'date',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Contract\ContractPeriod $domain
     * @param int $attrId
     * @param DwsServiceDivisionCode $serviceDivisionCode
     * @return static
     */
    public static function fromDomain(
        DomainContractPeriod $domain,
        int $attrId,
        DwsServiceDivisionCode $serviceDivisionCode
    ): self {
        $keys = [
            'contract_attr_id' => $attrId,
            'service_division_code' => $serviceDivisionCode,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::newModelInstance($keys + $attrs);
    }

    /** {@inheritdoc} */
    public function toDomain()
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainContractPeriod::create($attrs);
    }
}
