<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 介護保険サービス サービス詳細に総サービス単位数・種類支給限度基準を超える単位数・区分支給限度基準を超える単位数を追加する.
 */
class AddColumnLtcsBillingServiceDetail extends Migration
{
    private const LTCS_BILLING_SERVICE_DETAIL = 'ltcs_billing_service_detail';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 初期値は0にしたいのでデフォルト値を0にしておいて後でdefault値を消す
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->integer('whole_score')->unsigned()->default(0)->comment('総サービス単位数')->after('count');
            $table->integer('max_benefit_quota_excess_score')->unsigned()->default(0)->comment('種類支給限度基準を超える単位数')->after('whole_score');
            $table->integer('max_benefit_excess_score')->unsigned()->default(0)->comment('区分支給限度基準を超える単位数')->after('max_benefit_quota_excess_score');
        });
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->integer('whole_score')->default(null)->change();
            $table->integer('max_benefit_quota_excess_score')->default(null)->change();
            $table->integer('max_benefit_excess_score')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->dropColumn('whole_score');
            $table->dropColumn('max_benefit_quota_excess_score');
            $table->dropColumn('max_benefit_excess_score');
        });
    }
}
