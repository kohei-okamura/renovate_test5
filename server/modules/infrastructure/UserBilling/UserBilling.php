<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\BankAccount\BankAccountType;
use Domain\Common\Addr;
use Domain\Common\ConsumptionTaxRate;
use Domain\Common\Contact as DomainContact;
use Domain\Common\Decimal;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\UserBillingDestination;
use Domain\UserBilling\UserBilling as DomainUserBilling;
use Domain\UserBilling\UserBillingBankAccount;
use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingLtcsItem;
use Domain\UserBilling\UserBillingOffice;
use Domain\UserBilling\UserBillingOtherItem as DomainUserBillingOtherItem;
use Domain\UserBilling\UserBillingUser;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 利用者請求 Eloquent モデル.
 *
 * @property int $id 利用者請求 ID
 * @property int $organization_id 事業者 ID
 * @property int $user_id 利用者 ID
 * @property int $office_id 事業所 ID
 * @property null|int $dws_billing_statement_id 障害福祉明細書 ID
 * @property null|int $ltcs_billing_statement_id 介護保険明細書 ID
 * @property string $user_family_name 利用者：姓
 * @property string $user_given_name 利用者：名
 * @property string $user_phonetic_family_name 利用者：フリガナ：姓
 * @property string $user_phonetic_given_name 利用者：フリガナ：名
 * @property string $user_addr_postcode 利用者：郵便番号
 * @property int $user_addr_prefecture 利用者：都道府県
 * @property string $user_addr_city 利用者：市区町村
 * @property string $user_addr_street 利用者：町名・番地
 * @property string $user_addr_apartment 利用者：建物名など
 * @property int $user_billing_destination_destination 利用者：請求先情報：請求先
 * @property int $user_billing_destination_payment_method 利用者：請求先情報：支払方法
 * @property int $user_billing_destination_contract_number 利用者：請求先情報：契約者番号
 * @property string $user_billing_destination_corporation_name 利用者：請求先情報：請求先法人名・団体名
 * @property string $user_billing_destination_agent_name 利用者：請求先情報：請求先氏名・担当者名
 * @property string $user_billing_destination_addr_postcode 利用者：請求先情報：郵便番号
 * @property int $user_billing_destination_addr_prefecture 利用者：請求先情報：都道府県
 * @property string $user_billing_destination_addr_city 利用者：請求先情報：市区町村
 * @property string $user_billing_destination_addr_street 利用者：請求先情報：町名・番地
 * @property string $user_billing_destination_addr_apartment 利用者：請求先情報：建物名など
 * @property string $user_billing_destination_tel 利用者：請求先情報：電話番号
 * @property string $user_bank_name 利用者：銀行名
 * @property string $user_bank_code 利用者：銀行コード
 * @property string $user_bank_branch_name 利用者：支店名
 * @property string $user_bank_branch_code 利用者：支店コード
 * @property int $user_bank_account_type 利用者：種別
 * @property string $user_bank_account_number 利用者：口座番号
 * @property string $user_bank_account_holder 利用者：名義
 * @property string $office_name 事業所：事業所名
 * @property string $office_corporation_name 事業所：法人名
 * @property string $office_addr_postcode 事業所：郵便番号
 * @property int $office_addr_prefecture 事業所：都道府県
 * @property string $office_addr_city 事業所：市区町村
 * @property string $office_addr_street 事業所：町名・番地
 * @property string $office_addr_apartment 事業所：建物名など
 * @property string $office_tel 事業所：電話番号
 * @property null|int $dws_item_score 障害福祉サービス明細：単位数
 * @property null|int $dws_item_unit_cost 障害福祉サービス明細：単価
 * @property null|int $dws_item_subtotal_cost 障害福祉サービス明細：小計
 * @property int $dws_item_tax 障害福祉サービス明細：消費税
 * @property null|int $dws_item_medical_deduction_amount 障害福祉サービス明細：医療費控除対象額
 * @property null|int $dws_item_benefit_amount 障害福祉サービス明細：介護給付額
 * @property null|int $dws_item_subsidy_amount 障害福祉サービス明細：自治体助成額
 * @property null|int $dws_item_total_amount 障害福祉サービス明細：合計
 * @property null|int $dws_item_copay_without_tax 障害福祉サービス明細：自己負担額（税抜）
 * @property null|int $dws_item_copay_with_tax 障害福祉サービス明細：自己負担額（税込）
 * @property null|int $ltcs_item_score 介護保険サービス明細：単位数
 * @property null|int $ltcs_item_unit_cost 介護保険サービス明細：単価
 * @property null|int $ltcs_item_subtotal_cost 介護保険サービス明細：小計
 * @property int $ltcs_item_tax 介護保険サービス明細：消費税
 * @property null|int $ltcs_item_medical_deduction_amount 介護保険サービス明細：医療費控除対象額
 * @property null|int $ltcs_item_benefit_amount 介護保険サービス明細：介護給付額
 * @property null|int $ltcs_item_subsidy_amount 介護保険サービス明細：公費負担額
 * @property null|int $ltcs_item_total_amount 介護保険サービス明細：合計
 * @property null|int $ltcs_item_copay_without_tax 介護保険サービス明細：自己負担額（税抜）
 * @property null|int $ltcs_item_copay_with_tax 介護保険サービス明細：自己負担額（税込）
 * @property \Domain\UserBilling\UserBillingResult $result 請求結果
 * @property int $carried_over_amount 繰越金額
 * @property null|\Domain\UserBilling\WithdrawalResultCode $withdrawal_result_code 振替結果コード
 * @property \Domain\Common\Carbon $provided_in サービス提供年月
 * @property null|\Domain\Common\Carbon $issued_on 発行日
 * @property null|\Domain\Common\Carbon $deposited_at 入金日時
 * @property null|\Domain\Common\Carbon $transacted_at 処理日時
 * @property null|\Domain\Common\Carbon $deducted_on 口座振替日
 * @property \Domain\Common\Carbon $due_date お支払期限日
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\UserBilling\UserBillingContact[] $userContacts 利用者：連絡先電話番号
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\UserBilling\UserBillingOtherItem[] $otherItems その他サービス明細
 */
final class UserBilling extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'user_billing';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'user_id',
        'office_id',
        'dws_billing_statement_id',
        'ltcs_billing_statement_id',
        'user',
        'office',
        'dws_item',
        'ltcs_item',
        'result',
        'withdrawal_result_code',
        'carried_over_amount',
        'provided_in',
        'issued_on',
        'deposited_at',
        'transacted_at',
        'deducted_on',
        'due_date',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'result' => CastsUserBillingResult::class,
        'withdrawal_result_code' => CastsWithdrawalResultCode::class,
        'provided_in' => 'date',
        'issued_on' => 'date',
        'deposited_at' => 'datetime',
        'transacted_at' => 'datetime',
        'deducted_on' => 'date',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'userContacts',
        'otherItems',
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainUserBilling
    {
        $hasGetMutatorAttrs = [
            'user',
            'office',
            'dwsItem',
            'ltcsItem',
            'otherItems',
        ];
        return DomainUserBilling::create($this->only($hasGetMutatorAttrs) + $this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\UserBilling\UserBilling $domain
     * @return \Infrastructure\UserBilling\UserBilling
     */
    public static function fromDomain(DomainUserBilling $domain): self
    {
        $keys = [
            'id',
            'organization_id',
            'user_id',
            'office_id',
            'user',
            'office',
            'dws_item',
            'ltcs_item',
            'result',
            'withdrawal_result_code',
            'carried_over_amount',
            'provided_in',
            'issued_on',
            'deposited_at',
            'transacted_at',
            'deducted_on',
            'due_date',
            'created_at',
            'updated_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }

    /**
     * HasMany: {@link \Infrastructure\UserBilling\UserBillingContact}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userContacts(): HasMany
    {
        return $this->hasMany(UserBillingContact::class);
    }

    /**
     * HasMany: {@link \Infrastructure\UserBilling\UserBillingOtherItem}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function otherItems(): HasMany
    {
        return $this->hasMany(UserBillingOtherItem::class);
    }

    /**
     * Get mutator for user attribute.
     *
     * @return \Domain\UserBilling\UserBillingUser
     * @noinspection PhpUnused
     */
    public function getUserAttribute(): UserBillingUser
    {
        return UserBillingUser::create([
            'name' => new StructuredName(
                familyName: $this->user_family_name,
                givenName: $this->user_given_name,
                phoneticFamilyName: $this->user_phonetic_family_name,
                phoneticGivenName: $this->user_phonetic_given_name,
            ),
            'addr' => new Addr(
                postcode: $this->user_addr_postcode,
                prefecture: Prefecture::from($this->user_addr_prefecture),
                city: $this->user_addr_city,
                street: $this->user_addr_street,
                apartment: $this->user_addr_apartment,
            ),
            'contacts' => $this->mapSortRelation(
                'userContacts',
                'sort_order',
                fn (UserBillingContact $x): DomainContact => $x->toDomain()
            ),
            'billingDestination' => UserBillingDestination::create([
                'destination' => BillingDestination::from($this->user_billing_destination_destination),
                'paymentMethod' => PaymentMethod::from($this->user_billing_destination_payment_method),
                'contractNumber' => $this->user_billing_destination_contract_number,
                'corporationName' => $this->user_billing_destination_corporation_name,
                'agentName' => $this->user_billing_destination_agent_name,
                'addr' => $this->user_billing_destination_destination === BillingDestination::theirself()->value()
                    ? null
                    : new Addr(
                        postcode: $this->user_billing_destination_addr_postcode,
                        prefecture: Prefecture::from($this->user_billing_destination_addr_prefecture),
                        city: $this->user_billing_destination_addr_city,
                        street: $this->user_billing_destination_addr_street,
                        apartment: $this->user_billing_destination_addr_apartment,
                    ),
                'tel' => $this->user_billing_destination_tel,
            ]),
            'bankAccount' => UserBillingBankAccount::create([
                'bankName' => $this->user_bank_name,
                'bankCode' => $this->user_bank_code,
                'bankBranchName' => $this->user_bank_branch_name,
                'bankBranchCode' => $this->user_bank_branch_code,
                'bankAccountType' => BankAccountType::from($this->user_bank_account_type),
                'bankAccountNumber' => $this->user_bank_account_number,
                'bankAccountHolder' => $this->user_bank_account_holder,
            ]),
        ]);
    }

    /**
     * Set mutator for user attribute.
     *
     * @param \Domain\UserBilling\UserBillingUser $x
     * @return void
     * @noinspection PhpUnused
     */
    public function setUserAttribute(UserBillingUser $x): void
    {
        $this->attributes['user_family_name'] = $x->name->familyName;
        $this->attributes['user_given_name'] = $x->name->givenName;
        $this->attributes['user_phonetic_family_name'] = $x->name->phoneticFamilyName;
        $this->attributes['user_phonetic_given_name'] = $x->name->phoneticGivenName;
        $this->attributes['user_addr_postcode'] = $x->addr->postcode;
        $this->attributes['user_addr_prefecture'] = $x->addr->prefecture->value();
        $this->attributes['user_addr_city'] = $x->addr->city;
        $this->attributes['user_addr_street'] = $x->addr->street;
        $this->attributes['user_addr_apartment'] = $x->addr->apartment;
        $this->attributes['user_billing_destination_destination'] = $x->billingDestination->destination->value();
        $this->attributes['user_billing_destination_payment_method'] = $x->billingDestination->paymentMethod->value();
        $this->attributes['user_billing_destination_contract_number'] = $x->billingDestination->contractNumber;
        $this->attributes['user_billing_destination_corporation_name'] = $x->billingDestination->corporationName;
        $this->attributes['user_billing_destination_agent_name'] = $x->billingDestination->agentName;
        if ($x->billingDestination->destination === BillingDestination::theirself()) {
            $this->attributes['user_billing_destination_addr_postcode'] = '';
            $this->attributes['user_billing_destination_addr_prefecture'] = Prefecture::none();
            $this->attributes['user_billing_destination_addr_city'] = '';
            $this->attributes['user_billing_destination_addr_street'] = '';
            $this->attributes['user_billing_destination_addr_apartment'] = '';
        } else {
            $this->attributes['user_billing_destination_addr_postcode'] = $x->billingDestination->addr->postcode;
            $this->attributes['user_billing_destination_addr_prefecture'] = $x->billingDestination->addr->prefecture->value();
            $this->attributes['user_billing_destination_addr_city'] = $x->billingDestination->addr->city;
            $this->attributes['user_billing_destination_addr_street'] = $x->billingDestination->addr->street;
            $this->attributes['user_billing_destination_addr_apartment'] = $x->billingDestination->addr->apartment;
        }
        $this->attributes['user_billing_destination_tel'] = $x->billingDestination->tel;
        $this->attributes['user_bank_name'] = $x->bankAccount->bankName;
        $this->attributes['user_bank_code'] = $x->bankAccount->bankCode;
        $this->attributes['user_bank_branch_name'] = $x->bankAccount->bankBranchName;
        $this->attributes['user_bank_branch_code'] = $x->bankAccount->bankBranchCode;
        $this->attributes['user_bank_account_type'] = $x->bankAccount->bankAccountType->value();
        $this->attributes['user_bank_account_number'] = $x->bankAccount->bankAccountNumber;
        $this->attributes['user_bank_account_holder'] = $x->bankAccount->bankAccountHolder;
    }

    /**
     * Get mutator for office attribute.
     *
     * @return \Domain\UserBilling\UserBillingOffice
     * @noinspection PhpUnused
     */
    public function getOfficeAttribute(): UserBillingOffice
    {
        return UserBillingOffice::create([
            'name' => $this->office_name,
            'corporationName' => $this->office_corporation_name,
            'addr' => new Addr(
                postcode: $this->office_addr_postcode,
                prefecture: Prefecture::from($this->office_addr_prefecture),
                city: $this->office_addr_city,
                street: $this->office_addr_street,
                apartment: $this->office_addr_apartment,
            ),
            'tel' => $this->office_tel,
        ]);
    }

    /**
     * Set mutator for office attribute.
     *
     * @param \Domain\UserBilling\UserBillingOffice $x
     * @return void
     * @noinspection PhpUnused
     */
    public function setOfficeAttribute(UserBillingOffice $x): void
    {
        $this->attributes['office_name'] = $x->name;
        $this->attributes['office_corporation_name'] = $x->corporationName;
        $this->attributes['office_addr_postcode'] = $x->addr->postcode;
        $this->attributes['office_addr_prefecture'] = $x->addr->prefecture->value();
        $this->attributes['office_addr_city'] = $x->addr->city;
        $this->attributes['office_addr_street'] = $x->addr->street;
        $this->attributes['office_addr_apartment'] = $x->addr->apartment;
        $this->attributes['office_tel'] = $x->tel;
    }

    /**
     * Get mutator for dws_item attribute.
     *
     * @return null|\Domain\UserBilling\UserBillingDwsItem
     * @noinspection PhpUnused
     */
    public function getDwsItemAttribute(): ?UserBillingDwsItem
    {
        if (!isset($this->dws_billing_statement_id)) {
            return null;
        }
        return UserBillingDwsItem::create([
            'dwsStatementId' => $this->dws_billing_statement_id,
            'score' => $this->dws_item_score,
            'unitCost' => Decimal::fromInt($this->dws_item_unit_cost),
            'subtotalCost' => $this->dws_item_subtotal_cost,
            'tax' => ConsumptionTaxRate::from($this->dws_item_tax),
            'medicalDeductionAmount' => $this->dws_item_medical_deduction_amount,
            'benefitAmount' => $this->dws_item_benefit_amount,
            'subsidyAmount' => $this->dws_item_subsidy_amount,
            'totalAmount' => $this->dws_item_total_amount,
            'copayWithoutTax' => $this->dws_item_copay_without_tax,
            'copayWithTax' => $this->dws_item_copay_with_tax,
        ]);
    }

    /**
     * Set mutator for dws_item attribute.
     *
     * @param null|\Domain\UserBilling\UserBillingDwsItem $x
     * @return void
     * @noinspection PhpUnused
     */
    public function setDwsItemAttribute(?UserBillingDwsItem $x): void
    {
        if ($x !== null) {
            $this->attributes['dws_billing_statement_id'] = $x->dwsStatementId;
            $this->attributes['dws_item_score'] = $x->score;
            $this->attributes['dws_item_unit_cost'] = $x->unitCost->toInt();
            $this->attributes['dws_item_subtotal_cost'] = $x->subtotalCost;
            $this->attributes['dws_item_tax'] = $x->tax->value();
            $this->attributes['dws_item_medical_deduction_amount'] = $x->medicalDeductionAmount;
            $this->attributes['dws_item_benefit_amount'] = $x->benefitAmount;
            $this->attributes['dws_item_subsidy_amount'] = $x->subsidyAmount;
            $this->attributes['dws_item_total_amount'] = $x->totalAmount;
            $this->attributes['dws_item_copay_without_tax'] = $x->copayWithoutTax;
            $this->attributes['dws_item_copay_with_tax'] = $x->copayWithTax;
        } else {
            $this->attributes['dws_billing_statement_id'] = null;
            $this->attributes['dws_item_score'] = null;
            $this->attributes['dws_item_unit_cost'] = null;
            $this->attributes['dws_item_subtotal_cost'] = null;
            $this->attributes['dws_item_tax'] = ConsumptionTaxRate::zero()->value();
            $this->attributes['dws_item_medical_deduction_amount'] = null;
            $this->attributes['dws_item_benefit_amount'] = null;
            $this->attributes['dws_item_subsidy_amount'] = null;
            $this->attributes['dws_item_total_amount'] = null;
            $this->attributes['dws_item_copay_without_tax'] = null;
            $this->attributes['dws_item_copay_with_tax'] = null;
        }
    }

    /**
     * Get mutator for ltcs_item attribute.
     *
     * @return null|\Domain\UserBilling\UserBillingLtcsItem
     * @noinspection PhpUnused
     */
    public function getLtcsItemAttribute(): ?UserBillingLtcsItem
    {
        if (!isset($this->ltcs_billing_statement_id)) {
            return null;
        }
        return UserBillingLtcsItem::create([
            'ltcsStatementId' => $this->ltcs_billing_statement_id,
            'score' => $this->ltcs_item_score,
            'unitCost' => Decimal::fromInt($this->ltcs_item_unit_cost),
            'subtotalCost' => $this->ltcs_item_subtotal_cost,
            'tax' => ConsumptionTaxRate::from($this->ltcs_item_tax),
            'medicalDeductionAmount' => $this->ltcs_item_medical_deduction_amount,
            'benefitAmount' => $this->ltcs_item_benefit_amount,
            'subsidyAmount' => $this->ltcs_item_subsidy_amount,
            'totalAmount' => $this->ltcs_item_total_amount,
            'copayWithoutTax' => $this->ltcs_item_copay_without_tax,
            'copayWithTax' => $this->ltcs_item_copay_with_tax,
        ]);
    }

    /**
     * Set mutator for ltcs_item attribute.
     *
     * @param null|\Domain\UserBilling\UserBillingLtcsItem $x
     * @return void
     * @noinspection PhpUnused
     */
    public function setLtcsItemAttribute(?UserBillingLtcsItem $x): void
    {
        if ($x !== null) {
            $this->attributes['ltcs_billing_statement_id'] = $x->ltcsStatementId;
            $this->attributes['ltcs_item_score'] = $x->score;
            $this->attributes['ltcs_item_unit_cost'] = $x->unitCost->toInt();
            $this->attributes['ltcs_item_subtotal_cost'] = $x->subtotalCost;
            $this->attributes['ltcs_item_tax'] = $x->tax->value();
            $this->attributes['ltcs_item_medical_deduction_amount'] = $x->medicalDeductionAmount;
            $this->attributes['ltcs_item_benefit_amount'] = $x->benefitAmount;
            $this->attributes['ltcs_item_subsidy_amount'] = $x->subsidyAmount;
            $this->attributes['ltcs_item_total_amount'] = $x->totalAmount;
            $this->attributes['ltcs_item_copay_without_tax'] = $x->copayWithoutTax;
            $this->attributes['ltcs_item_copay_with_tax'] = $x->copayWithTax;
        } else {
            $this->attributes['ltcs_billing_statement_id'] = null;
            $this->attributes['ltcs_item_score'] = null;
            $this->attributes['ltcs_item_unit_cost'] = null;
            $this->attributes['ltcs_item_subtotal_cost'] = null;
            $this->attributes['ltcs_item_tax'] = ConsumptionTaxRate::zero()->value();
            $this->attributes['ltcs_item_medical_deduction_amount'] = null;
            $this->attributes['ltcs_item_benefit_amount'] = null;
            $this->attributes['ltcs_item_subsidy_amount'] = null;
            $this->attributes['ltcs_item_total_amount'] = null;
            $this->attributes['ltcs_item_copay_without_tax'] = null;
            $this->attributes['ltcs_item_copay_with_tax'] = null;
        }
    }

    /**
     * Get mutator for other_items.
     *
     * @noinspection PhpUnused
     */
    protected function getOtherItemsAttribute(): array
    {
        return $this->mapSortRelation(
            'otherItems',
            'sort_order',
            fn (UserBillingOtherItem $x): DomainUserBillingOtherItem => $x->toDomain()
        );
    }
}
