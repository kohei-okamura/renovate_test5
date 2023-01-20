<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 障害福祉サービス：請求：明細書集計 利用者負担額カラム を追加する.
 */
final class AddManageCopayColumnToDwsBillingStatementAggregateTable extends Migration
{
    private const DWS_BILLING_STATEMENT_AGGREGATE = 'dws_billing_statement_aggregate';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // デフォルト値無しで追加するとエラーになるため
        // 一旦デフォルト値付きで追加し、その後にデフォルト値を外す
        Schema::table(self::DWS_BILLING_STATEMENT_AGGREGATE, function (Blueprint $table) {
            $table->integer('managed_copay')->comment('利用者負担額')->default(0)->after('unmanaged_copay');
        });
        Schema::table(self::DWS_BILLING_STATEMENT_AGGREGATE, function (Blueprint $table) {
            $table->integer('unmanaged_copay')->comment('1割相当額')->change();
            $table->integer('managed_copay')->comment('利用者負担額')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::DWS_BILLING_STATEMENT_AGGREGATE, function (Blueprint $table) {
            $table->dropColumn('managed_copay');
            $table->integer('unmanaged_copay')->comment('利用者負担額（1割相当額）')->change();
        });
    }
}
