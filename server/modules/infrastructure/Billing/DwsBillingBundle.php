<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingBundle as DomainBundle;
use Domain\Billing\DwsBillingServiceDetail as DomainServiceDetail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス請求単位 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス請求単位ID
 * @property int $dws_billing_id 障害福祉サービス請求ID
 * @property \Domain\Common\Carbon $provided_in サービス提供年月日
 * @property string $city_code 市町村番号
 * @property string $city_name 市町村名
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Billing\DwsBillingServiceDetail[] $details サービス詳細
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingBundle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingBundle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingBundle query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingBundle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingBundle whereProvidedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingBundle whereCityCode($value)
 */
final class DwsBillingBundle extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_bundle';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'dws_billing_id',
        'provided_in',
        'city_code',
        'city_name',
        'created_at',
        'updated_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'details',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'provided_in' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'details',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingBundle $domain
     * @return \Infrastructure\Billing\DwsBillingBundle
     */
    public static function fromDomain(DomainBundle $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBundle
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainBundle::create($attrs);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\DwsBillingServiceDetail}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function details(): HasMany
    {
        return $this->hasMany(DwsBillingServiceDetail::class)->orderBy('sort_order');
    }

    /**
     * Get mutator for details attribute.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getDetailsAttribute(): array
    {
        return $this->mapRelation('details', fn (DwsBillingServiceDetail $x): DomainServiceDetail => $x->toDomain());
    }
}
