<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserDwsCalcSpec as DomainUserDwsCalcSpec;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス：利用者別算定情報 Eloquent モデル.
 *
 * @property int $id 利用者別算定情報 ID
 * @property null|int $user_id 利用者ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\User\UserDwsCalcSpecAttr $attr
 * @mixin \Eloquent
 */
final class UserDwsCalcSpec extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'user_dws_calc_spec';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_id',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['attr'];

    /**
     * HasOne: {@link \Infrastructure\User\UserDwsCalcSpecAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(UserDwsCalcSpecAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainUserDwsCalcSpec
    {
        return DomainUserDwsCalcSpec::fromAssoc($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\UserDwsCalcSpec $domain
     * @return \Infrastructure\User\UserDwsCalcSpec
     */
    public static function fromDomain(DomainUserDwsCalcSpec $domain): self
    {
        $keys = [
            'id',
            'user_id',
            'created_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
