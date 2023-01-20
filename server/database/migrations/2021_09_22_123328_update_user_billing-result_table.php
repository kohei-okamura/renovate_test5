<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 利用者請求：請求結果テーブルにレコードを追加し、ID と 名前の組み合わせを変更する.
 */
class UpdateUserBillingResultTable extends Migration
{
    private const USER_BILLING_RESULT = 'user_billing_result';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::unseedCatalogue(self::USER_BILLING_RESULT, $this->userBillingResultsForDelete());
        Schema::seedCatalogue(self::USER_BILLING_RESULT, $this->userBillingResultsForInsert());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::unseedCatalogue(self::USER_BILLING_RESULT, $this->userBillingResultsForInsert());
        Schema::seedCatalogue(self::USER_BILLING_RESULT, $this->userBillingResultsForDelete());
    }

    /**
     * 追加する利用者請求：請求結果の一覧.
     *
     * @return array
     */
    private function userBillingResultsForInsert(): array
    {
        return [
            [1, '処理中'],
            [2, '入金済'],
            [3, '口座振替未済'],
        ];
    }

    /**
     * 削除する利用者請求：請求結果の一覧.
     *
     * @return array
     */
    private function userBillingResultsForDelete(): array
    {
        return [
            [1, '入金済'],
            [2, '口座振替未済'],
        ];
    }
}
