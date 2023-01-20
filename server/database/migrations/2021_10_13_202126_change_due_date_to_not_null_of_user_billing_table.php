<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

/**
 * 利用者請求テーブルのお支払い期限日を not null に変更する.
 */
class ChangeDueDateToNotNullOfUserBillingTable extends Migration
{
    private const USER_BILLING = 'user_billing';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update('update user_billing set due_date = LAST_DAY(ADDDATE(provided_in, INTERVAL 1 MONTH)) where due_date is NULL');
        Schema::table(self::USER_BILLING, function (Blueprint $table) {
            $table->dateTime('due_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::USER_BILLING, function (Blueprint $table) {
            $table->dateTime('due_date')->nullable()->change();
        });
    }
}
