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
 * サービス提供実績記録票 予定・開始時間の型をdatetimeに変更する.
 */
class ModifyDwsBillingServiceReportItemTable extends Migration
{
    private const DWS_BILLING_SERVICE_REPORT_ITEM = 'dws_billing_service_report_item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::DWS_BILLING_SERVICE_REPORT_ITEM, function (Blueprint $table) {
            $table->datetime('plan_period_start')->nullable()->change();
            $table->datetime('plan_period_end')->nullable()->change();
            $table->datetime('result_period_start')->nullable()->change();
            $table->datetime('result_period_end')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::DWS_BILLING_SERVICE_REPORT_ITEM, function (Blueprint $table) {
            $table->date('plan_period_start')->nullable()->change();
            $table->date('plan_period_end')->nullable()->change();
            $table->date('result_period_start')->nullable()->change();
            $table->date('result_period_end')->nullable()->change();
        });
    }
}
