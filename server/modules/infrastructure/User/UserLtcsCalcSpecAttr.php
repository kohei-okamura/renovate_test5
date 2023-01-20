<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserLtcsCalcSpec as DomainUserLtcsCalcSpec;
use Infrastructure\Model;

/**
 * 介護保険サービス：利用者別算定情報 Eloquent モデル.
 *
 * @property int $id 利用者別算定情報属性 ID
 * @property \Domain\Common\Carbon $effectivated_on 適用日
 * @property \Domain\User\LtcsUserLocationAddition $location_addition 地域加算
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 *
 * @mixin \Eloquent
 */
final class UserLtcsCalcSpecAttr extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'user_ltcs_calc_spec_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'effectivated_on',
        'location_addition',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_enabled' => 'boolean',
        'location_addition' => CastsLtcsUserLocationAddition::class,
        'effectivated_on' => 'date',
        'updated_at' => 'datetime',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\UserLtcsCalcSpec $domain
     * @return \Infrastructure\User\UserLtcsCalcSpecAttr
     */
    public static function fromDomain(DomainUserLtcsCalcSpec $domain): self
    {
        $keys = [
            'effectivated_on',
            'location_addition',
            'is_enabled',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $keys = [
            'effectivated_on',
            'location_addition',
            'is_enabled',
            'version',
            'updated_at',
        ];
        return $this->toDomainAttributes($keys);
    }
}
