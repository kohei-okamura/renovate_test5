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
 * 利用者請求テーブル明細書IDカラムを nullable に変更する.
 */
class ChangeStatementIdToNullableOfUserBillingTable extends Migration
{
    private const USER_BILLING = 'user_billing';
    private const DWS_BILLING_STATEMENT = 'dws_billing_statement';
    private const LTCS_BILLING_STATEMENT = 'ltcs_billing_statement';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::USER_BILLING, function (Blueprint $table): void {
            $table->bigInteger(self::DWS_BILLING_STATEMENT . '_id')->unsigned()->nullable()->comment('障害福祉明細書ID')->change();
            $table->bigInteger(self::LTCS_BILLING_STATEMENT . '_id')->unsigned()->nullable()->comment('介護保険明細書ID')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::USER_BILLING, function (Blueprint $table): void {
            $table->bigInteger(self::DWS_BILLING_STATEMENT . '_id')->unsigned()->comment('障害福祉明細書ID')->change();
            $table->bigInteger(self::LTCS_BILLING_STATEMENT . '_id')->unsigned()->comment('介護保険明細書ID')->change();
        });
    }
}
