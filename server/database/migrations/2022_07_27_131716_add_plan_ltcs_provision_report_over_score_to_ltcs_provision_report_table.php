<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 介護保険サービス：予実テーブルに超過単位を追加する.
 */
class AddPlanLtcsProvisionReportOverScoreToLtcsProvisionReportTable extends Migration
{
    private const LTCS_PROVISION_REPORT = 'ltcs_provision_report';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::LTCS_PROVISION_REPORT, function (Blueprint $table): void {
            $table->integer('plan_over_max_benefit_score')
                ->after('location_addition')
                ->comment('超過単位（予定）：区分支給限度基準を超える単位数');
            $table->integer('plan_over_max_benefit_quota_score')
                ->after('plan_over_max_benefit_score')
                ->comment('超過単位（予定）：種類支給限度基準を超える単位数');
            $table->integer('result_over_max_benefit_score')
                ->after('plan_over_max_benefit_quota_score')
                ->comment('超過単位（実績）：区分支給限度基準を超える単位数');
            $table->integer('result_over_max_benefit_quota_score')
                ->after('result_over_max_benefit_score')
                ->comment('超過単位（実績）：種類支給限度基準を超える単位数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::LTCS_PROVISION_REPORT, function (Blueprint $table): void {
            $table->dropColumn('plan_over_max_benefit_score');
            $table->dropColumn('plan_over_max_benefit_quota_score');
            $table->dropColumn('result_over_max_benefit_score');
            $table->dropColumn('result_over_max_benefit_quota_score');
        });
    }
}
