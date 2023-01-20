<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBilling as DomainBilling;
use Domain\Billing\LtcsBillingFile as DomainBillingFile;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：請求 Eloquent モデル.
 *
 * @property int $id 請求 ID
 * @property int $organization_id 事業者 ID
 * @property \Domain\Common\Carbon $transacted_in 処理対象年月
 * @property \Domain\Billing\LtcsBillingStatus $status 状態
 * @property null|\Domain\Common\Carbon $fixed_at 確定日時
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read array|\Domain\Billing\LtcsBillingFile[] $files
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTransactedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereFixedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUpdatedAt($value)
 */
final class LtcsBilling extends Model implements Domainable
{
    use LtcsBillingOfficeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'organization_id',
        'office',
        'transacted_in',
        'status',
        'fixed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'files',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'transacted_in' => 'date',
        'status' => CastsLtcsBillingStatus::class,
        'fixed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'files',
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBilling $domain
     * @return static
     */
    public static function fromDomain(DomainBilling $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBilling
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainBilling::create($attrs);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\LtcsBillingFile}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(LtcsBillingFile::class, 'billing_id')->orderBy('sort_order');
    }

    /**
     * Get mutator for files attribute.
     *
     * @return array|\Domain\Billing\LtcsBillingFile[]
     * @noinspection PhpUnused
     */
    protected function getFilesAttribute(): array
    {
        return $this->mapRelation('files', fn (LtcsBillingFile $x): DomainBillingFile => $x->toDomain());
    }
}
