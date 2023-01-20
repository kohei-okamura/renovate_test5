<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingServiceDetail as DomainServiceDetail;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\ServiceCode\ServiceCodeHolder;
use Infrastructure\ServiceCodeDictionary\CastsDwsServiceCodeCategory;
use Infrastructure\User\BelongsToUser;

/**
 * 障害福祉サービス請求：サービス詳細 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス請求：サービス詳細ID
 * @property int $dws_billing_bundle_id 障害福祉サービス請求単位ID
 * @property int $user_id 利用者ID
 * @property \Domain\Common\Carbon $provided_on サービス提供年月日
 * @property \Domain\ServiceCode\ServiceCode $service_code サービスコード
 * @property \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $service_code_category サービスコード区分
 * @property bool $is_addition 加算フラグ
 * @property int $unit_score 単位数
 * @property int $count 回数
 * @property int $total_score サービス単位数
 * @property int $index_number 番号
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereResultId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereprovidedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingInvoiceItem whereServiceCode($value)
 */
final class DwsBillingServiceDetail extends Model implements Domainable
{
    use BelongsToUser;
    use ServiceCodeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_service_detail';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'user_id',
        'provided_on',
        'service_code',
        'service_code_category',
        'is_addition',
        'unit_score',
        'count',
        'total_score',
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
        'dws_billing_bundle_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'provided_on' => 'date',
        'is_addition' => 'bool',
        'service_code_category' => CastsDwsServiceCodeCategory::class,
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingServiceDetail $domain
     * @param int $bundleId
     * @param int $sortOrder
     * @return \Infrastructure\Billing\DwsBillingServiceDetail
     */
    public static function fromDomain(DomainServiceDetail $domain, int $bundleId, int $sortOrder): self
    {
        $keys = [
            'dws_billing_bundle_id' => $bundleId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainServiceDetail
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainServiceDetail::create($attrs);
    }
}
