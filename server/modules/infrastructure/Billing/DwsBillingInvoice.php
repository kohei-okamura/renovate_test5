<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingHighCostPayment;
use Domain\Billing\DwsBillingInvoice as DomainInvoice;
use Domain\Billing\DwsBillingInvoiceItem as DomainInvoiceItem;
use Domain\Billing\DwsBillingPayment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス請求書 Eloquent モデル.
 *
 * @property int id 障害福祉サービス請求書ID
 * @property int $dws_billing_bundle_id 障害福祉サービス請求単位ID
 * @property int $claim_amount 請求金額
 * @property \Domain\Billing\DwsBillingPayment $dws_payment 小計：介護給付費等・特別介護給付費等
 * @property \Domain\Billing\DwsBillingHighCostPayment $high_cost_dws_payment 小計：特定障害者特別給付費・高額障害福祉サービス費
 * @property int $total_count 合計：件数
 * @property int $total_score 合計：単位数
 * @property int $total_fee 合計：費用合計
 * @property int $total_benefit 合計：給付費請求額
 * @property int $total_copay 合計：利用者負担額
 * @property int $total_subsidy 合計：自治体助成額
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Billing\DwsBillingInvoiceItem[] $items 明細
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereClaimAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereSubtotalDetailCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereSubtotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereSubtotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereSubtotalBenefit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereSubtotalCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereSubtotalSubsidy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereHighCostSubtotalDetailCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereHighCostSubtotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereHighCostSubtotalBenefit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereTotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereTotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereTotalBenefit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereTotalCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoice whereTotalSubsidy($value)
 */
final class DwsBillingInvoice extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_invoice';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'dws_billing_bundle_id',
        'claim_amount',
        'dws_payment',
        'high_cost_dws_payment',
        'total_count',
        'total_score',
        'total_fee',
        'total_benefit',
        'total_copay',
        'total_subsidy',
        'created_at',
        'updated_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'items',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\DwsBillingInvoice $domain
     * @return static
     */
    public static function fromDomain(DomainInvoice $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainInvoice
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainInvoice::create($attrs);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\DwsBillingInvoiceItem}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(DwsBillingInvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * Get mutator for dws_payment attribute.
     *
     * @return \Domain\Billing\DwsBillingPayment
     * @noinspection PhpUnused
     */
    protected function getDwsPaymentAttribute(): DwsBillingPayment
    {
        return DwsBillingPayment::create([
            'subtotalDetailCount' => $this->attributes['subtotal_detail_count'],
            'subtotalScore' => $this->attributes['subtotal_score'],
            'subtotalFee' => $this->attributes['subtotal_fee'],
            'subtotalBenefit' => $this->attributes['subtotal_benefit'],
            'subtotalCopay' => $this->attributes['subtotal_copay'],
            'subtotalSubsidy' => $this->attributes['subtotal_subsidy'],
        ]);
    }

    /**
     * Set mutator for dws_payment attribute.
     *
     * @param \Domain\Billing\DwsBillingPayment $value
     * @noinspection PhpUnused
     */
    protected function setDwsPaymentAttribute(DwsBillingPayment $value): void
    {
        $this->attributes['subtotal_detail_count'] = $value->subtotalDetailCount;
        $this->attributes['subtotal_score'] = $value->subtotalScore;
        $this->attributes['subtotal_fee'] = $value->subtotalFee;
        $this->attributes['subtotal_benefit'] = $value->subtotalBenefit;
        $this->attributes['subtotal_copay'] = $value->subtotalCopay;
        $this->attributes['subtotal_subsidy'] = $value->subtotalSubsidy;
    }

    /**
     * Get mutator for high_cost_dws_payment attribute.
     *
     * @return \Domain\Billing\DwsBillingHighCostPayment
     * @noinspection PhpUnused
     */
    protected function getHighCostDwsPaymentAttribute(): DwsBillingHighCostPayment
    {
        return DwsBillingHighCostPayment::create([
            'subtotalDetailCount' => $this->attributes['high_cost_subtotal_detail_count'],
            'subtotalFee' => $this->attributes['high_cost_subtotal_fee'],
            'subtotalBenefit' => $this->attributes['high_cost_subtotal_benefit'],
        ]);
    }

    /**
     * Set mutator for high_cost_dws_payment attribute.
     *
     * @param \Domain\Billing\DwsBillingHighCostPayment $value
     * @noinspection PhpUnused
     */
    protected function setHighCostDwsPaymentAttribute(DwsBillingHighCostPayment $value): void
    {
        $this->attributes['high_cost_subtotal_detail_count'] = $value->subtotalDetailCount;
        $this->attributes['high_cost_subtotal_fee'] = $value->subtotalFee;
        $this->attributes['high_cost_subtotal_benefit'] = $value->subtotalBenefit;
    }

    /**
     * Get mutator for items attribute.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getItemsAttribute(): array
    {
        return $this->mapRelation('items', fn (DwsBillingInvoiceItem $x): DomainInvoiceItem => $x->toDomain());
    }
}
