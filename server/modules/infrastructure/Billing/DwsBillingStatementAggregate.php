<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingStatementAggregate as DomainStatementAggregate;
use Infrastructure\Common\CastsDecimal;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス明細書：集計 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス明細書：集計ID
 * @property int $dws_billing_statement_id 障害福祉サービス明細書ID
 * @property string $service_division_code サービス種類コード
 * @property \Domain\Common\Carbon $started_on サービス開始年月日
 * @property \Domain\Common\Carbon $terminated_on サービス終了年月日
 * @property int $service_days サービス利用日数
 * @property int $subtotal_score 給付単位数
 * @property int $unit_cost 単位数単価
 * @property int $subtotal_fee 総費用額
 * @property int $unmanaged_copay 1割相当額
 * @property int $managed_copay 利用者負担額
 * @property int $capped_copay 上限月額調整
 * @property int $adjusted_copay 調整後利用者負担額
 * @property int $coordinated_copay 上限額管理後利用者負担額
 * @property int $subtotal_copay 決定利用者負担額
 * @property int $subtotal_benefit 請求額：給付費
 * @property int $subtotal_subsidy 自治体助成分請求額
 * @property int $sort_order 並び順
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereDwsBillingStatementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereServiceDivisionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereStartedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereTerminatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereServiceDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereSubtotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereSubtotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereUnmanagedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereCappedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereAdjustedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereCoordinatedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereSubtotalCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereSubtotalBenefit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatementAggregate whereSubtotalSubsidy($value)
 */
final class DwsBillingStatementAggregate extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_statement_aggregate';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'service_division_code',
        'started_on',
        'terminated_on',
        'service_days',
        'subtotal_score',
        'unit_cost',
        'subtotal_fee',
        'unmanaged_copay',
        'managed_copay',
        'capped_copay',
        'adjusted_copay',
        'coordinated_copay',
        'subtotal_copay',
        'subtotal_benefit',
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
        'dws_billing_statement_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'service_division_code' => CastsDwsServiceDivisionCode::class,
        'started_on' => 'date',
        'terminated_on' => 'date',
        'unit_cost' => CastsDecimal::class,
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate $domain
     * @param int $statementId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainStatementAggregate $domain, int $statementId, int $sortOrder): self
    {
        $keys = [
            'dws_billing_statement_id' => $statementId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainStatementAggregate
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainStatementAggregate::fromAssoc($attrs);
    }
}
