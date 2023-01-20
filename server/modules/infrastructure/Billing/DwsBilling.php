<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBilling as DomainBilling;
use Domain\Billing\DwsBillingFile as DomainBillingFile;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス請求 Eloquent モデル.
 *
 * @property int $id 請求 ID
 * @property int $organization_id 事業者 ID
 * @property \Domain\Common\Carbon $transacted_in 処理対象年月
 * @property \Domain\Billing\DwsBillingStatus $status 状態
 * @property null|\Domain\Common\Carbon $fixed_at 確定日時
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Infrastructure\Billing\DwsBillingFile[] $files ファイル
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTransactedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereFixedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUpdatedAt($value)
 */
final class DwsBilling extends Model implements Domainable
{
    use DwsBillingOfficeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing';

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
        'status' => CastsDwsBillingStatus::class,
        'fixed_at' => 'datetime',
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'files',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBilling $domain
     * @return \Infrastructure\Billing\DwsBilling
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
     * HasMany: {@link \Infrastructure\Billing\DwsBillingFile}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(DwsBillingFile::class)->orderBy('sort_order');
    }

    /**
     * Get mutator for files attribute.
     *
     * @return array|\Domain\Billing\DwsBillingFile[]
     * @noinspection PhpUnused
     */
    protected function getFilesAttribute(): array
    {
        return $this->mapRelation('files', fn (DwsBillingFile $x): DomainBillingFile => $x->toDomain());
    }
}
