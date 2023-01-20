<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\UserBilling\UserBillingOtherItem as DomainUserBillingOtherItem;
use Infrastructure\Common\CastsConsumptionTaxRate;
use Infrastructure\Common\CastsDecimal;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 利用者請求：その他サービス明細 Eloquent モデル.
 *
 * @property int $id 利用者請求：その他サービス明細ID
 * @property int $user_billing_id 利用者請求ID
 * @property int $sort_order 表示順
 * @property int $score 単位数
 * @property int $unit_cost 単価
 * @property int $subtotal_cost 小計
 * @property int $tax 消費税
 * @property int $medical_deduction_amount 医療費控除対象額
 * @property int $total_amount 合計
 * @property int $copay_without_tax 自己負担額（税抜）
 * @property int $copay_with_tax 自己負担額（税込）
 */
final class UserBillingOtherItem extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'user_billing_other_item';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_billing_id',
        'sort_order',
        'score',
        'unit_cost',
        'subtotal_cost',
        'tax',
        'medical_deduction_amount',
        'total_amount',
        'copay_without_tax',
        'copay_with_tax',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'tax' => CastsConsumptionTaxRate::class,
        'unit_cost' => CastsDecimal::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainUserBillingOtherItem
    {
        return DomainUserBillingOtherItem::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\UserBilling\UserBillingOtherItem $domain
     * @param array $additional
     * @return \Infrastructure\UserBilling\UserBillingOtherItem
     */
    public static function fromDomain(DomainUserBillingOtherItem $domain, array $additional): self
    {
        $keys = [
            'score',
            'unit_cost',
            'subtotal_cost',
            'tax',
            'medical_deduction_amount',
            'total_amount',
            'copay_without_tax',
            'copay_with_tax',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }
}
