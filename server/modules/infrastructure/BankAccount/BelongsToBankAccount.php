<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\BankAccount;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsTo: {@link \Infrastructure\BankAccount\BankAccount}.
 *
 * @property int $bank_account_id 銀行口座ID
 * @property-read \Infrastructure\BankAccount\BankAccount $bankAccount 銀行口座
 * @method static \Illuminate\Database\Eloquent\Builder|static whereBankAccountId($value)
 * @mixin \Eloquent
 */
trait BelongsToBankAccount
{
    /**
     * BelongsTo: {@link \Infrastructure\BankAccount\BankAccount}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @noinspection PhpUnused
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
