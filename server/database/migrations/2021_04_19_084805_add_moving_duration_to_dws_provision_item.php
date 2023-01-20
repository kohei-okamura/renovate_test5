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
 * 障害福祉サービス予実明細テーブルに移動時間を追加する.
 */
class AddMovingDurationToDwsProvisionItem extends Migration
{
    private const DWS_PROVISION_REPORT_ITEM_PLAN = 'dws_provision_report_item_plan';
    private const DWS_PROVISION_REPORT_ITEM_RESULT = 'dws_provision_report_item_result';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::DWS_PROVISION_REPORT_ITEM_PLAN, function (Blueprint $table) {
            $table->unsignedInteger('moving_duration_minutes')->after('headcount')->comment('移動介護時間数')->default(0);
        });
        Schema::table(self::DWS_PROVISION_REPORT_ITEM_RESULT, function (Blueprint $table) {
            $table->unsignedInteger('moving_duration_minutes')->after('headcount')->comment('移動介護時間数')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::DWS_PROVISION_REPORT_ITEM_PLAN, function (Blueprint $table) {
            $table->dropColumn('moving_duration_minutes');
        });
        Schema::table(self::DWS_PROVISION_REPORT_ITEM_RESULT, function (Blueprint $table) {
            $table->dropColumn('moving_duration_minutes');
        });
    }
}
