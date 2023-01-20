<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserDwsSubsidy as DomainUserDwsSubsidy;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 自治体助成情報 Eloquent モデル.
 *
 * @property int $id 自治体助成情報ID
 * @property int $user_id 利用者ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\User\UserDwsSubsidyAttr $attr
 */
class UserDwsSubsidy extends Model implements Domainable
{
    use BelongsToUser;

    /**
     * テーブル名.
     */
    public const TABLE = 'user_dws_subsidy';

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
     * HasOne: {@link \Infrastructure\User\UserDwsSubsidyAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(UserDwsSubsidyAttr::class);
    }

    /**
     * {@inheritdoc}
     */
    public function toDomain(): DomainUserDwsSubsidy
    {
        return DomainUserDwsSubsidy::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\UserDwsSubsidy $domain
     * @return \Infrastructure\User\UserDwsSubsidy
     */
    public static function fromDomain(DomainUserDwsSubsidy $domain): self
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
