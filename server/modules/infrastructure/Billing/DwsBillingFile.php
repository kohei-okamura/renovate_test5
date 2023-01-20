<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingFile as DomainBillingFile;
use Infrastructure\Common\CastsMimeType;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス：請求：ファイル Eloquent モデル.
 *
 * @property int $id 障害福祉サービス：請求：ファイルID
 * @property int $dws_billing_id 請求ID
 * @property string $name ファイル名
 * @property string $path パス
 * @property string $token トークン
 * @property string $mime_type MimeType
 * @property int $sort_order
 * @property \Domain\Common\Carbon $created_at
 * @property null|\Domain\Common\Carbon $downloaded_at
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingFile query()
 * @mixin \Eloquent
 */
final class DwsBillingFile extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_file';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'name',
        'path',
        'token',
        'mime_type',
        'created_at',
        'downloaded_at',
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
        'dws_billing_id',
        ...self::DOMAIN_ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'mime_type' => CastsMimeType::class,
        'created_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingFile $domain
     * @param int $billingId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainBillingFile $domain, int $billingId, int $sortOrder): self
    {
        $keys = [
            'dws_billing_id' => $billingId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBillingFile
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainBillingFile::fromAssoc($attrs);
    }
}
