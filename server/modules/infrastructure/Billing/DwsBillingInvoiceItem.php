<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingInvoiceItem as DomainInvoiceItem;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス請求書：明細 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス請求書：明細ID
 * @property int $dws_billing_invoice_id 障害福祉サービス請求書ID
 * @property \Domain\Billing\DwsBillingPaymentCategory $payment_category 給付種別
 * @property string $service_division_code サービス種類コード
 * @property int $subtotal_count 件数
 * @property int $subtotal_score 単位数
 * @property int $subtotal_fee 費用合計
 * @property int $subtotal_benefit 給付費請求額
 * @property int $subtotal_copay 利用者負担額
 * @property int $subtotal_subsidy 自治体助成額
 * @property int $sort_order 並び順
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereDwsBillingInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem wherePaymentCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereServiceDivisionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereSubtotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereSubtotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereSubtotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereSubtotalBenefit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereSubtotalCopay($value)
 */
final class DwsBillingInvoiceItem extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_invoice_item';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'payment_category',
        'service_division_code',
        'subtotal_count',
        'subtotal_score',
        'subtotal_fee',
        'subtotal_benefit',
        'subtotal_copay',
        'subtotal_subsidy',
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
        'dws_billing_invoice_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'payment_category' => CastsDwsBillingPaymentCategory::class,
        'service_division_code' => CastsDwsServiceDivisionCode::class,
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingInvoiceItem $domain
     * @param int $invoiceId
     * @param int $sortOrder
     * @return \Infrastructure\Billing\DwsBillingInvoiceItem
     */
    public static function fromDomain(DomainInvoiceItem $domain, int $invoiceId, int $sortOrder): self
    {
        $keys = [
            'dws_billing_invoice_id' => $invoiceId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainInvoiceItem
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainInvoiceItem::create($attrs);
    }
}
