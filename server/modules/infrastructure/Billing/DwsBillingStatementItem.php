<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingStatementItem as DomainStatementItem;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\ServiceCode\ServiceCodeHolder;
use Infrastructure\ServiceCodeDictionary\CastsDwsServiceCodeCategory;

/**
 * 障害福祉サービス明細書：明細 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス明細書：明細ID
 * @property int $dws_billing_statement_id 障害福祉サービス明細書ID
 * @property \Domain\ServiceCode\ServiceCode $service_code サービスコード
 * @property \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $service_code_category サービスコード区分
 * @property int $unit_score 単位数
 * @property int $count 回数
 * @property int $total_score サービス単位数
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem whereDwsBillingStatementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem whereServiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem whereUnitScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementItem whereTotalScore($value)
 */
final class DwsBillingStatementItem extends Model implements Domainable
{
    use ServiceCodeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_statement_item';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'service_code',
        'service_code_category',
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
        'dws_billing_statement_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'service_code_category' => CastsDwsServiceCodeCategory::class,
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingStatementItem $domain
     * @param int $statementId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainStatementItem $domain, int $statementId, int $sortOrder): self
    {
        $keys = [
            'dws_billing_statement_id' => $statementId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainStatementItem
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainStatementItem::fromAssoc($attrs);
    }
}
