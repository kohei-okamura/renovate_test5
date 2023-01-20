<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 「利用者請求：請求結果」に「請求なし」を追加する
 */
class AddNoneToUserBillingResultTable extends Migration
{
    private const USER_BILLING_RESULT = 'user_billing_result';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::USER_BILLING_RESULT, $this->results());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::USER_BILLING_RESULT, $this->results());
    }

    /**
     * 追加する請求結果の一覧.
     *
     * @return array
     */
    private function results(): array
    {
        return [
            [4, '請求なし'],
        ];
    }
}
