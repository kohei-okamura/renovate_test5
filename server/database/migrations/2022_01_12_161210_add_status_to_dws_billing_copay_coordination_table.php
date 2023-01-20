<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 利用者負担上限額管理結果票テーブルに状態カラムを追加する.
 */
class AddStatusToDwsBillingCopayCoordinationTable extends Migration
{
    private const DWS_BILLING_COPAY_COORDINATION = 'dws_billing_copay_coordination';
    private const DWS_BILLING_STATUS = 'dws_billing_status';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // catalogued だと default なしでカラムを追加できない + 後で default を消せない（やり方次第かもしれない）ので使わない
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            $table->integer('status')->unsigned()->comment('状態')->after('result');
        });
        // 既存データは 確定済 にする（status の id は dws_billing_status から取得した方が堅牢だけど問題なさそうなのでべた書き）
        DB::update('UPDATE ' . self::DWS_BILLING_COPAY_COORDINATION . ' SET status = 30');
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            $table->foreign('status')->references('id')->on(self::DWS_BILLING_STATUS);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            $table->dropForeign(self::DWS_BILLING_COPAY_COORDINATION . '_status_foreign');
            $table->dropColumn('status');
        });
    }
}
