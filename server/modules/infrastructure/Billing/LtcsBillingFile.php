<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingFile as DomainBillingFile;
use Infrastructure\Common\CastsMimeType;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：請求：ファイル Eloquent モデル.
 *
 * @property int $id ファイル ID
 * @property int $billing_id 請求 ID
 * @property string $name ファイル名
 * @property string $path パス
 * @property string $token トークン
 * @property \Domain\Common\Carbon $created_at 作成日時
 * @property \Domain\Common\Carbon $downloaded_at 最終ダウンロード日時
 * @property int $sort_order 並び順
 * @method static \Illuminate\Database\Eloquent\Builder|static newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereBillingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDownloadedAt($value)
 */
final class LtcsBillingFile extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_file';

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
        'billing_id',
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
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingFile $domain
     * @param int $billingId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainBillingFile $domain, int $billingId, int $sortOrder): self
    {
        $keys = [
            'billing_id' => $billingId,
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
