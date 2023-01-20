<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\BankAccount\BankAccountType;
use Domain\UserBilling\WithdrawalResultCode;
use Domain\UserBilling\WithdrawalTransactionItem as DomainWithdrawalTransactionItem;
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginDataRecordCode;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 口座振替データ：明細　Eloquent モデル.
 *
 * @property int $id 口座振替データ：明細 ID
 * @property int $withdrawal_transaction_id 事業者 ID
 * @property int $sort_order 表示順
 * @property string $zengin_record_bank_code 全銀データ：引落銀行番号
 * @property string $zengin_record_bank_branch_code 全銀データ：引落支店番号
 * @property \Domain\BankAccount\BankAccountType $zengin_record_bank_account_type 全銀データ：預金種目
 * @property string $zengin_record_bank_account_number 全銀データ：口座番号
 * @property string $zengin_record_bank_account_holder 全銀データ：預金者名
 * @property int $zengin_record_amount 全銀データ：引落金額
 * @property $zengin_record_data_record_code 全銀データ：新規コード
 * @property string $zengin_record_client_number 全銀データ：顧客番号
 * @property \Domain\UserBilling\WithdrawalResultCode $zengin_record_withdrawal_result_code 全銀データ：振替結果コード
 */
final class WithdrawalTransactionItem extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'withdrawal_transaction_item';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'withdrawal_transaction_id',
        'sort_order',
        'zengin_record',
    ];

    /**
     * BelongsToMany: {@link \Infrastructure\UserBilling\UserBilling}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userBillings(): BelongsToMany
    {
        return $this->belongsToMany(
            UserBilling::class,
            WithdrawalTransactionItem::TABLE . '_to_' . UserBilling::TABLE
        );
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainWithdrawalTransactionItem
    {
        $hasGetMutatorAttrs = [
            'zenginRecord',
        ];
        $values = [
            'userBillingIds' => $this->userBillings()->allRelatedIds()->toArray(),
        ];
        return DomainWithdrawalTransactionItem::create($this->only($hasGetMutatorAttrs) + $values + $this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\UserBilling\WithdrawalTransactionItem $domain
     * @param array $additional
     * @return \Infrastructure\UserBilling\WithdrawalTransactionItem
     */
    public static function fromDomain(DomainWithdrawalTransactionItem $domain, array $additional): self
    {
        $keys = [
            'user_billing_ids',
            'zengin_record',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }

    /**
     * Get mutator for zengin_record attribute.
     *
     * @return \Domain\UserBilling\ZenginDataRecord
     */
    public function getZenginRecordAttribute(): ZenginDataRecord
    {
        return ZenginDataRecord::create([
            'bankCode' => $this->zengin_record_bank_code,
            'bankBranchCode' => $this->zengin_record_bank_branch_code,
            'bankAccountType' => BankAccountType::from($this->zengin_record_bank_account_type),
            'bankAccountNumber' => $this->zengin_record_bank_account_number,
            'bankAccountHolder' => $this->zengin_record_bank_account_holder,
            'amount' => $this->zengin_record_amount,
            'dataRecordCode' => ZenginDataRecordCode::from($this->zengin_record_data_record_code),
            'clientNumber' => $this->zengin_record_client_number,
            'withdrawalResultCode' => WithdrawalResultCode::from($this->zengin_record_withdrawal_result_code),
        ]);
    }

    /**
     * Set mutator for zengin_record attribute.
     *
     * @param \Domain\UserBilling\ZenginDataRecord $x
     * @return void
     */
    public function setZenginRecordAttribute(ZenginDataRecord $x): void
    {
        $this->attributes['zengin_record_bank_code'] = $x->bankCode;
        $this->attributes['zengin_record_bank_branch_code'] = $x->bankBranchCode;
        $this->attributes['zengin_record_bank_account_type'] = $x->bankAccountType->value();
        $this->attributes['zengin_record_bank_account_number'] = $x->bankAccountNumber;
        $this->attributes['zengin_record_bank_account_holder'] = $x->bankAccountHolder;
        $this->attributes['zengin_record_amount'] = $x->amount;
        $this->attributes['zengin_record_data_record_code'] = $x->dataRecordCode->value();
        $this->attributes['zengin_record_client_number'] = $x->clientNumber;
        $this->attributes['zengin_record_withdrawal_result_code'] = $x->withdrawalResultCode->value();
    }
}
