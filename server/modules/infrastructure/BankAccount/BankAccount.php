<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\BankAccount;

use Domain\BankAccount\BankAccount as DomainBankAccount;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 銀行口座 Eloquent モデル.
 *
 * @property int $id 銀行口座ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\BankAccount\BankAccountAttr $attr
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereId($value)
 * @mixin \Eloquent
 */
final class BankAccount extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'bank_account';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['attr'];

    /**
     * HasOne: {@link \Infrastructure\BankAccount\BankAccountAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(BankAccountAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBankAccount
    {
        return DomainBankAccount::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\BankAccount\BankAccount $domain
     * @return \Infrastructure\BankAccount\BankAccount
     */
    public static function fromDomain(DomainBankAccount $domain): self
    {
        $keys = ['id', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
