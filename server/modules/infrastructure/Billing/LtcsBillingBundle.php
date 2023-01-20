<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingBundle as DomainBundle;
use Domain\Billing\LtcsBillingServiceDetail as DomainServiceDetail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：請求単位 Eloquent モデル.
 *
 * @property int $id 辞書 ID
 * @property int $billing_id 請求 ID
 * @property \Domain\Common\Carbon $provided_in サービス提供年月
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read array|\Infrastructure\Billing\LtcsBillingServiceDetail[] $details
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereBillingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereProvidedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUpdatedAt($value)
 */
final class LtcsBillingBundle extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_bundle';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'billing_id',
        'provided_in',
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
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingBundle $domain
     * @return static
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
     * HasMany: {@link \Infrastructure\Billing\LtcsBillingServiceDetail}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(LtcsBillingServiceDetail::class, 'bundle_id')->orderBy('sort_order');
    }

    /**
     * Get mutator for details attribute.
     *
     * @return array|\Domain\Billing\LtcsBillingServiceDetail[]
     * @noinspection PhpUnused
     */
    protected function getDetailsAttribute(): array
    {
        return $this->mapRelation('details', fn (LtcsBillingServiceDetail $x): DomainServiceDetail => $x->toDomain());
    }
}
