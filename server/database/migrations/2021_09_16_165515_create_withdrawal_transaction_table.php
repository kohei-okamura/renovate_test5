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
 * 口座振替データ関連テーブルを追加する.
 */
class CreateWithdrawalTransactionTable extends Migration
{
    private const WITHDRAWAL_TRANSACTION = 'withdrawal_transaction';
    private const WITHDRAWAL_TRANSACTION_ITEM = 'withdrawal_transaction_item';
    private const ZENGIN_DATA_RECORD_CODE = 'zengin_data_record_code';
    private const ORGANIZATION = 'organization';
    private const USER_BILLING = 'user_billing';
    private const BANK_ACCOUNT_TYPE = 'bank_account_type';
    private const WITHDRAWAL_RESULT_CODE = 'withdrawal_result_code';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::createCatalogue(self::ZENGIN_DATA_RECORD_CODE, '全銀レコード：データレコード：新規コード', $this->zenginDataRecordCode());
        Schema::create(self::WITHDRAWAL_TRANSACTION, function (Blueprint $table): void {
            $table->id()->comment('口座振替データID');
            $table->references(self::ORGANIZATION, '事業者');
            $table->dateTime('downloaded_at')->comment('最終ダウンロード日時');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::WITHDRAWAL_TRANSACTION_ITEM, function (Blueprint $table): void {
            $table->id()->comment('口座振替データ：明細ID');
            $table->references(self::WITHDRAWAL_TRANSACTION, '口座振替データ')->onDelete('cascade');
            $table->string('zengin_record_bank_code', 4)->charset('binary')->comment('全銀データ：引落銀行番号');
            $table->string('zengin_record_bank_branch_code', 3)->charset('binary')->comment('全銀データ：引落支店番号');
            $table->catalogued(self::BANK_ACCOUNT_TYPE, '全銀データ：預金種目', 'zengin_record_' . self::BANK_ACCOUNT_TYPE);
            $table->string('zengin_record_bank_account_number', 7)->charset('binary')->comment('全銀データ：口座番号');
            $table->string('zengin_record_bank_account_holder', 100)->comment('全銀データ：預金者名');
            $table->integer('zengin_record_amount')->comment('全銀データ：引落金額');
            $table->catalogued(self::ZENGIN_DATA_RECORD_CODE, '全銀データ：新規コード', 'zengin_record_data_record_code');
            $table->string('zengin_record_client_number', 20)->charset('binary')->comment('全銀データ：顧客番号');
            $table->catalogued(self::WITHDRAWAL_RESULT_CODE, '全銀データ：振替結果コード', 'zengin_record_' . self::WITHDRAWAL_RESULT_CODE);
            $table->sortOrder();
            // KEYS
            $table->unique(
                ['withdrawal_transaction_id', 'sort_order'],
                'withdrawal_transaction_item_sort_order_unique'
            );
        });
        Schema::createIntermediate(self::WITHDRAWAL_TRANSACTION_ITEM, self::USER_BILLING, '口座振替データ：明細', '利用者請求');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIntermediate(self::WITHDRAWAL_TRANSACTION_ITEM, self::USER_BILLING);
        Schema::dropIfExists(self::WITHDRAWAL_TRANSACTION_ITEM);
        Schema::dropIfExists(self::WITHDRAWAL_TRANSACTION);
        Schema::dropIfExists(self::ZENGIN_DATA_RECORD_CODE);
    }

    /**
     * 全銀レコード：データレコード：新規コードの定義一覧.
     *
     * @return array
     */
    private function zenginDataRecordCode(): array
    {
        return [
            [1, '初回'],
            [2, '変更'],
            [0, 'その他'],
        ];
    }
}
