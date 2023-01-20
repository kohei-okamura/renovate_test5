<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserLtcsCalcSpec as DomainUserLtcsCalcSpec;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：利用者別算定情報 Eloquent モデル.
 *
 * @property int $id 利用者別算定情報 ID
 * @property null|int $user_id 利用者ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\User\UserLtcsCalcSpecAttr $attr
 * @mixin \Eloquent
 */
final class UserLtcsCalcSpec extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'user_ltcs_calc_spec';

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
     * HasOne: {@link \Infrastructure\User\UserLtcsCalcSpecAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(UserLtcsCalcSpecAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainUserLtcsCalcSpec
    {
        return DomainUserLtcsCalcSpec::fromAssoc($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\UserLtcsCalcSpec $domain
     * @return \Infrastructure\User\UserLtcsCalcSpec
     */
    public static function fromDomain(DomainUserLtcsCalcSpec $domain): self
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
