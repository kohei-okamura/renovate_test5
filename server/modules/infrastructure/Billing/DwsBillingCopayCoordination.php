<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingCopayCoordination as DomainCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationItem as DomainCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 利用者負担上限額管理結果票.
 *
 * @property int $id 利用者負担上限額管理結果票ID
 * @property int $dws_billing_id 障害福祉サービス請求ID
 * @property int $dws_billing_bundle_id 障害福祉サービス請求単位ID
 * @property \Domain\Billing\DwsBillingOffice $office 上限管理事業所
 * @property \Domain\Billing\DwsBillingUser $user 上限管理対象利用者
 * @property \Domain\Billing\CopayCoordinationResult $result 利用者負担上限額管理結果
 * @property \Domain\Billing\DwsBillingCopayCoordinationExchangeAim $exchangeAim 作成区分
 * @property \Domain\Billing\DwsBillingCopayCoordinationPayment $total 合計
 * @property \Domain\Billing\DwsBillingStatus $status 状態
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Billing\DwsBillingCopayCoordinationItem[] $items 利用者負担上限額管理結果表：費用
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereDwsBillingBundleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereDwsCertificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereDwsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination wherePhoneticName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereChildPhoneticName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereCopayLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereCoordinatedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingCopayCoordination whereResult($value)
 */
final class DwsBillingCopayCoordination extends Model implements Domainable
{
    use DwsBillingOfficeHolder;
    use DwsBillingUserHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_copay_coordination';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'dws_billing_id',
        'dws_billing_bundle_id',
        'office',
        'user',
        'result',
        'exchange_aim',
        'total',
        'status',
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
        'result' => CastsCopayCoordinationResult::class,
        'exchange_aim' => CastsDwsBillingCopayCoordinationExchangeAim::class,
        'status' => CastsDwsBillingStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'items',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordination $domain
     * @return static
     */
    public static function fromDomain(DomainCopayCoordination $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainCopayCoordination
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainCopayCoordination::create($attrs);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\DwsBillingCopayCoordinationItem}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(DwsBillingCopayCoordinationItem::class)->orderBy('sort_order');
    }

    /**
     * Get mutator for items attribute.
     *
     * @return array|\Domain\Billing\DwsBillingFile[]
     * @noinspection PhpUnused
     */
    protected function getItemsAttribute(): array
    {
        return $this->mapRelation(
            'items',
            fn (DwsBillingCopayCoordinationItem $x): DomainCopayCoordinationItem => $x->toDomain()
        );
    }

    /**
     * Get mutator for total attribute.
     *
     * @return \Domain\Billing\DwsBillingCopayCoordinationPayment
     * @noinspection PhpUnused
     */
    protected function getTotalAttribute(): DwsBillingCopayCoordinationPayment
    {
        return DwsBillingCopayCoordinationPayment::create([
            'fee' => $this->attributes['total_fee'],
            'copay' => $this->attributes['total_copay'],
            'coordinatedCopay' => $this->attributes['total_coordinated_copay'],
        ]);
    }

    /**
     * Set mutator for total attribute.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordinationPayment $total
     * @noinspection PhpUnused
     */
    protected function setTotalAttribute(DwsBillingCopayCoordinationPayment $total): void
    {
        $this->attributes['total_fee'] = $total->fee;
        $this->attributes['total_copay'] = $total->copay;
        $this->attributes['total_coordinated_copay'] = $total->coordinatedCopay;
    }
}
