<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 利用者請求関連テーブルを追加する.
 */
class CreateUserBillingTable extends Migration
{
    private const USER_BILLING = 'user_billing';
    private const ORGANIZATION = 'organization';
    private const USER = 'user';
    private const OFFICE = 'office';
    private const CONSUMPTION_TAX_RATE = 'consumption_tax_rate';
    private const WITHDRAWAL_RESULT_CODE = 'withdrawal_result_code';
    private const USER_BILLING_RESULT = 'user_billing_result';
    private const USER_BILLING_CONTACT = 'user_billing_contact';
    private const USER_BILLING_OTHER_ITEM = 'user_billing_other_item';
    private const CONTACT_RELATIONSHIP = 'contact_relationship';
    private const BILLING_DESTINATION = 'billing_destination';
    private const PAYMENT_METHOD = 'payment_method';
    private const DWS_BILLING_STATEMENT = 'dws_billing_statement';
    private const LTCS_BILLING_STATEMENT = 'ltcs_billing_statement';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue(self::CONSUMPTION_TAX_RATE, '消費税', $this->consumptionTaxRates());
        Schema::createCatalogue(self::WITHDRAWAL_RESULT_CODE, '利用者請求：振替結果コード', $this->withdrawalResultCodes());
        Schema::createCatalogue(self::USER_BILLING_RESULT, '利用者請求：請求結果', $this->userBillingResults());
        Schema::create(self::USER_BILLING, function (Blueprint $table): void {
            $table->id()->comment('利用者請求ID');
            $table->references(self::ORGANIZATION, '事業者');
            $table->references(self::USER, '利用者');
            $table->references(self::OFFICE, '事業所');
            $table->references(self::DWS_BILLING_STATEMENT, '障害福祉明細書');
            $table->references(self::LTCS_BILLING_STATEMENT, '介護保険明細書');

            // 利用者
            $table->structuredName('user_', '利用者：');
            $table->addr('user_', '利用者：');
            // 利用者：請求先情報
            $table->catalogued(self::BILLING_DESTINATION, '利用者：請求先情報：請求先', 'user_billing_destination_destination');
            $table->catalogued(self::PAYMENT_METHOD, '利用者：請求先情報：支払方法', 'user_billing_destination_payment_method');
            $table->string('user_billing_destination_contract_number', 10)->charset('binary')->comment('利用者：請求先情報：契約者番号');
            $table->string('user_billing_destination_corporation_name', 200)->comment('利用者：請求先情報：請求先法人名・団体名');
            $table->string('user_billing_destination_agent_name', 100)->comment('利用者：請求先情報：請求先氏名・担当者名');
            $table->addr('user_billing_destination_', '利用者：請求先情報：');
            $table->tel('user_billing_destination_', '利用者：請求先情報：');
            // 利用者：銀行口座
            $table->string('user_bank_name', 100)->comment('利用者：銀行名');
            $table->string('user_bank_code', 4)->charset('binary')->comment('利用者：銀行コード');
            $table->string('user_bank_branch_name', 100)->comment('利用者：支店名');
            $table->string('user_bank_branch_code', 3)->charset('binary')->comment('利用者：支店コード');
            $table->catalogued('bank_account_type', '利用者：種別', 'user_bank_account_type');
            $table->string('user_bank_account_number', 10)->charset('binary')->comment('利用者：口座番号');
            $table->string('user_bank_account_holder', 100)->comment('利用者：名義');

            // 事業所
            $table->string('office_name', 200)->comment('事業所：事業所名');
            $table->string('office_corporation_name', 200)->comment('事業所：法人名');
            $table->addr('office_', '事業所：');
            $table->tel('office_', '事業所：');

            // 障害福祉サービス明細
            $table->integer('dws_item_score')->nullable()->comment('障害福祉サービス明細：単位数');
            $table->integer('dws_item_unit_cost')->nullable()->comment('障害福祉サービス明細：単価');
            $table->integer('dws_item_subtotal_cost')->nullable()->comment('障害福祉サービス明細：小計');
            $table->catalogued(self::CONSUMPTION_TAX_RATE, '障害福祉サービス明細：消費税', 'dws_item_tax');
            $table->integer('dws_item_medical_deduction_amount')->nullable()->comment('障害福祉サービス明細：医療費控除対象額');
            $table->integer('dws_item_benefit_amount')->nullable()->comment('障害福祉サービス明細：介護給付額');
            $table->integer('dws_item_subsidy_amount')->nullable()->comment('障害福祉サービス明細：自治体助成額');
            $table->integer('dws_item_total_amount')->nullable()->comment('障害福祉サービス明細：合計');
            $table->integer('dws_item_copay_without_tax')->nullable()->comment('障害福祉サービス明細：自己負担額（税抜）');
            $table->integer('dws_item_copay_with_tax')->nullable()->comment('障害福祉サービス明細：自己負担額（税込）');

            // 介護保険サービス明細
            $table->integer('ltcs_item_score')->nullable()->comment('介護保険サービス明細：単位数');
            $table->integer('ltcs_item_unit_cost')->nullable()->comment('介護保険サービス明細：単価');
            $table->integer('ltcs_item_subtotal_cost')->nullable()->comment('介護保険サービス明細：小計');
            $table->catalogued(self::CONSUMPTION_TAX_RATE, '介護保険サービス明細：消費税', 'ltcs_item_tax');
            $table->integer('ltcs_item_medical_deduction_amount')->nullable()->comment('介護保険サービス明細：医療費控除対象額');
            $table->integer('ltcs_item_benefit_amount')->nullable()->comment('介護保険サービス明細：介護給付額');
            $table->integer('ltcs_item_subsidy_amount')->nullable()->comment('介護保険サービス明細：公費負担額');
            $table->integer('ltcs_item_total_amount')->nullable()->comment('介護保険サービス明細：合計');
            $table->integer('ltcs_item_copay_without_tax')->nullable()->comment('介護保険サービス明細：自己負担額（税抜）');
            $table->integer('ltcs_item_copay_with_tax')->nullable()->comment('介護保険サービス明細：自己負担額（税込）');

            $table->catalogued(self::USER_BILLING_RESULT, '請求結果', 'result');
            $table->integer('carried_over_amount')->comment('繰越金額');
            $table->catalogued(self::WITHDRAWAL_RESULT_CODE, '振替結果コード');
            $table->date('provided_in')->comment('サービス提供年月');
            $table->date('issued_on')->nullable()->comment('発行日');
            $table->dateTime('deposited_at')->nullable()->comment('入金日時');
            $table->dateTime('transacted_at')->nullable()->comment('処理日時');
            $table->date('deducted_on')->nullable()->comment('口座振替日');
            $table->date('due_date')->nullable()->comment('お支払期限日');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::USER_BILLING_CONTACT, function (Blueprint $table): void {
            $table->id()->comment('利用者請求：利用者：連絡先電話番号ID');
            $table->references(self::USER_BILLING, '利用者請求')->onDelete('cascade');
            $table->sortOrder();
            $table->tel();
            $table->catalogued(self::CONTACT_RELATIONSHIP, '続柄・関係', 'relationship');
            $table->string('name')->comment('名前');
        });
        Schema::create(self::USER_BILLING_OTHER_ITEM, function (Blueprint $table): void {
            $table->id()->comment('利用者請求：その他サービス明細ID');
            $table->references(self::USER_BILLING, '利用者請求')->onDelete('cascade');
            $table->sortOrder();
            $table->integer('score')->comment('単位数');
            $table->integer('unit_cost')->comment('単価');
            $table->integer('subtotal_cost')->comment('小計');
            $table->catalogued(self::CONSUMPTION_TAX_RATE, '消費税', 'tax');
            $table->integer('medical_deduction_amount')->comment('医療費控除対象額');
            $table->integer('total_amount')->comment('合計');
            $table->integer('copay_without_tax')->comment('自己負担額（税抜）');
            $table->integer('copay_with_tax')->comment('自己負担額（税込）');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(self::USER_BILLING_OTHER_ITEM);
        Schema::dropIfExists(self::USER_BILLING_CONTACT);
        Schema::dropIfExists(self::USER_BILLING);
        Schema::dropIfExists(self::USER_BILLING_RESULT);
        Schema::dropIfExists(self::WITHDRAWAL_RESULT_CODE);
        Schema::dropIfExists(self::CONSUMPTION_TAX_RATE);
    }

    /**
     * 消費税の定義一覧.
     *
     * @return array
     */
    private function consumptionTaxRates(): array
    {
        return [
            [0, '0%'],
            [8, '8%'],
            [10, '10%'],
        ];
    }

    /**
     * 利用者請求：振替結果コードの定義一覧.
     *
     * @return array
     */
    private function withdrawalResultCodes(): array
    {
        return [
            [0, '振替済'],
            [1, '資金不足'],
            [2, '取引なし'],
            [3, '預金者都合'],
            [4, '依頼書なし'],
            [8, '委託者都合'],
            [9, 'その他'],
            [99, '未処理'],
        ];
    }

    /**
     * 利用者請求：請求結果の定義一覧.
     *
     * @return array
     */
    private function userBillingResults(): array
    {
        return [
            [0, '未処理'],
            [1, '入金済'],
            [2, '口座振替未済'],
        ];
    }
}
