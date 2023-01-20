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
 * 利用者属性テーブルに利用者：請求先情報のカラムを追加する.
 */
class AddUserBillingDestinationToUserAttrTable extends Migration
{
    private const USER_ATTR = 'user_attr';
    private const BILLING_DESTINATION = 'billing_destination';
    private const PAYMENT_METHOD = 'payment_method';
    private const PREFECTURE = 'prefecture';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::PREFECTURE, $this->prefectures());
        Schema::createCatalogue(self::BILLING_DESTINATION, '請求先', $this->billingDestinations());
        Schema::createCatalogue(self::PAYMENT_METHOD, '支払方法', $this->paymentMethods());
        Schema::table(self::USER_ATTR, function (Blueprint $table): void {
            // COLUMNS
            $table->dropColumn('mbs_customer_code');

            $table->catalogued(self::BILLING_DESTINATION, '請求先', 'billing_destination_destination', 'mbs_customer_code', 0);
            $table->catalogued(self::PAYMENT_METHOD, '支払方法', 'billing_destination_payment_method', 'billing_destination_destination', 0);
            $table->string('billing_destination_contract_number', 10)->charset('binary')->comment('契約者番号')->after('billing_destination_payment_method');
            $table->string('billing_destination_corporation_name', 200)->comment('請求先法人名・団体名')->after('billing_destination_contract_number');
            $table->string('billing_destination_agent_name', 100)->comment('請求先氏名・担当者名')->after('billing_destination_corporation_name');
            $table->addr('billing_destination_', '請求先：', 'billing_destination_agent_name');
            $table->tel('billing_destination_', '請求先：')->after('billing_destination_addr_apartment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::USER_ATTR, function (Blueprint $table): void {
            $table->dropColumn('billing_destination_tel');
            $table->dropColumn('billing_destination_addr_apartment');
            $table->dropColumn('billing_destination_addr_street');
            $table->dropColumn('billing_destination_addr_city');
            $table->dropForeign(self::USER_ATTR . '_billing_destination_addr_prefecture_foreign');
            $table->dropColumn('billing_destination_addr_prefecture');
            $table->dropColumn('billing_destination_addr_postcode');
            $table->dropColumn('billing_destination_agent_name');
            $table->dropColumn('billing_destination_corporation_name');
            $table->dropColumn('billing_destination_contract_number');
            $table->dropForeign(self::USER_ATTR . '_billing_destination_payment_method_foreign');
            $table->dropColumn('billing_destination_payment_method');
            $table->dropForeign(self::USER_ATTR . '_billing_destination_destination_foreign');
            $table->dropColumn('billing_destination_destination');

            $table->string('mbs_customer_code', 20)->charset('binary')->comment('MBS顧客番号');
        });
        Schema::dropIfExists(self::PAYMENT_METHOD);
        Schema::dropIfExists(self::BILLING_DESTINATION);
        Schema::unseedCatalogue(self::PREFECTURE, $this->prefectures());
    }

    /**
     * 都道府県に追加する区分値の一覧.
     *
     * @return array
     */
    private function prefectures(): array
    {
        return [
            [0, '未設定'],
        ];
    }

    /**
     * 請求先の定義一覧.
     *
     * @return array
     */
    private function billingDestinations(): array
    {
        return [
            [0, '未設定'],
            [1, '本人'],
            [2, '本人以外（個人）'],
            [3, '本人以外（法人・団体）'],
        ];
    }

    /**
     * 支払方法の定義一覧.
     *
     * @return array
     */
    private function paymentMethods(): array
    {
        return [
            [0, '未設定'],
            [1, '口座振替'],
            [2, '銀行振込'],
            [3, '集金'],
        ];
    }
}
