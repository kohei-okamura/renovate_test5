<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingCopayCoordinationItem as DomainItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 利用者負担上限額管理結果票：明細.
 *
 * @property int $id 利用者負担上限額管理結果票：明細ID
 * @property int $dws_billing_copay_coordination_id 利用者負担上限額管理結果票ID
 * @property \Domain\Billing\DwsBillingOffice $office 事業所
 * @property \Domain\Billing\DwsBillingCopayCoordinationPayment $subtotal 利用者負担額集計・調整欄
 * @property int $item_number 項番
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereDwsBillingCopayCoordinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereCoordinatedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordinationItem whereItemNumber($value)
 */
final class DwsBillingCopayCoordinationItem extends Model implements Domainable
{
    use DwsBillingOfficeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_copay_coordination_item';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'office',
        'subtotal',
        'item_number',
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
        'dws_billing_copay_coordination_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordinationItem $domain
     * @param int $copayCoordinationId
     * @param int $sortOrder
     * @return \Infrastructure\Billing\DwsBillingCopayCoordinationItem
     */
    public static function fromDomain(
        DomainItem $domain,
        int $copayCoordinationId,
        int $sortOrder
    ): DwsBillingCopayCoordinationItem {
        $keys = [
            'dws_billing_copay_coordination_id' => $copayCoordinationId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainItem
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainItem::create($attrs);
    }

    /**
     * Get mutator for subtotal attribute.
     *
     * @return \Domain\Billing\DwsBillingCopayCoordinationPayment
     */
    protected function getSubtotalAttribute(): DwsBillingCopayCoordinationPayment
    {
        return DwsBillingCopayCoordinationPayment::create([
            'fee' => $this->attributes['subtotal_fee'],
            'copay' => $this->attributes['subtotal_copay'],
            'coordinatedCopay' => $this->attributes['subtotal_coordinated_copay'],
        ]);
    }

    /**
     * Set mutator for subtotal attribute.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordinationPayment $value
     */
    protected function setSubtotalAttribute(DwsBillingCopayCoordinationPayment $value): void
    {
        $this->attributes['subtotal_fee'] = $value->fee;
        $this->attributes['subtotal_copay'] = $value->copay;
        $this->attributes['subtotal_coordinated_copay'] = $value->coordinatedCopay;
    }
}
