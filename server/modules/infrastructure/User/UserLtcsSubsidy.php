<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserLtcsSubsidy as DomainUserLtcsSubsidy;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 公費情報 Eloquent モデル
 *
 * @property int $id 公費情報ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\User\UserLtcsSubsidyAttr $attr
 */
class UserLtcsSubsidy extends Model implements Domainable
{
    use BelongsToUser;

    /**
     * テーブル名.
     */
    public const TABLE = 'user_ltcs_subsidy';

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
     * HasOne: {@link \Infrastructure\User\UserLtcsSubsidyAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(UserLtcsSubsidyAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainUserLtcsSubsidy
    {
        return DomainUserLtcsSubsidy::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\UserLtcsSubsidy $domain
     * @return \Infrastructure\User\UserLtcsSubsidy
     */
    public static function fromDomain(DomainUserLtcsSubsidy $domain): self
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
