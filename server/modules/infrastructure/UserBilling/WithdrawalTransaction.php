<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\UserBilling\WithdrawalTransaction as DomainWithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionItem as DomainWithdrawalTransactionItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 口座振替データ　Eloquent モデル.
 *
 * @property int $id 口座振替データ ID
 * @property int $organization_id 事業者 ID
 * @property \Domain\Common\Carbon $deducted_on 口座振替日
 * @property null|\Domain\Common\Carbon $downloaded_at 最終ダウンロード日時
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 */
final class WithdrawalTransaction extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'withdrawal_transaction';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'deducted_on',
        'downloaded_at',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'deducted_on' => 'date',
        'downloaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(WithdrawalTransactionItem::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainWithdrawalTransaction
    {
        $hasGetMutatorAttrs = [
            'items',
        ];
        return DomainWithdrawalTransaction::create($this->only($hasGetMutatorAttrs) + $this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\UserBilling\WithdrawalTransaction $domain
     * @return \Infrastructure\UserBilling\WithdrawalTransaction
     */
    public static function fromDomain(DomainWithdrawalTransaction $domain): self
    {
        $keys = [
            'id',
            'organization_id',
            'deducted_on',
            'downloaded_at',
            'created_at',
            'updated_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }

    /**
     * Get mutator for items.
     *
     * @noinspection PhpUnused
     */
    protected function getItemsAttribute(): array
    {
        return $this->mapSortRelation(
            'items',
            'sort_order',
            fn (WithdrawalTransactionItem $x): DomainWithdrawalTransactionItem => $x->toDomain()
        );
    }
}
