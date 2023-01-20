<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingStatementContract as DomainStatementContract;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス明細書：契約 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス明細書：契約ID
 * @property int $dws_billing_statement_id 障害福祉サービス明細書ID
 * @property \Domain\Billing\DwsGrantedServiceCode $dws_granted_service_code 決定サービスコード
 * @property int $granted_amount 契約支給量（分単位）
 * @property \Domain\Common\Carbon $agreed_on 契約開始年月日
 * @property null|\Domain\Common\Carbon $expired_on 契約終了年月日
 * @property int $index_number 事業者記入欄番号
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract whereDwsBillingStatementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract whereDwsGrantedServiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract whereGrantedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract whereAgreedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract whereExpiredOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementContract whereIndexNumber($value)
 */
final class DwsBillingStatementContract extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_statement_contract';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'dws_granted_service_code',
        'granted_amount',
        'agreed_on',
        'expired_on',
        'index_number',
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
        'dws_billing_statement_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'dws_granted_service_code' => CastsDwsGrantedServiceCode::class,
        'agreed_on' => 'date',
        'expired_on' => 'date',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingStatementContract $domain
     * @param int $statementId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainStatementContract $domain, int $statementId, int $sortOrder): self
    {
        $keys = [
            'dws_billing_statement_id' => $statementId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainStatementContract
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainStatementContract::create($attrs);
    }
}
