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
 * 介護保険サービス：予実テーブルの超過単位の物理名を変更する.
 */
class FixColumnNameOfLtcsProvisionReportTable extends Migration
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
            $table->renameColumn('plan_over_max_benefit_score', 'plan_max_benefit_excess_score');
            $table->renameColumn('plan_over_max_benefit_quota_score', 'plan_max_benefit_quota_excess_score');
            $table->renameColumn('result_over_max_benefit_score', 'result_max_benefit_excess_score');
            $table->renameColumn('result_over_max_benefit_quota_score', 'result_max_benefit_quota_excess_score');
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
            $table->renameColumn('plan_max_benefit_excess_score', 'plan_over_max_benefit_score');
            $table->renameColumn('plan_max_benefit_quota_excess_score', 'plan_over_max_benefit_quota_score');
            $table->renameColumn('result_max_benefit_excess_score', 'result_over_max_benefit_score');
            $table->renameColumn('result_max_benefit_quota_excess_score', 'result_over_max_benefit_quota_score');
        });
    }
}
