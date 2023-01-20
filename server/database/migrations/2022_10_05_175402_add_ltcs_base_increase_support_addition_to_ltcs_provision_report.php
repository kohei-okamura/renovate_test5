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
 * 介護保険サービス：予実テーブルにベースアップ等支援加算を追加する.
 */
class AddLtcsBaseIncreaseSupportAdditionToLtcsProvisionReport extends Migration
{
    private const LTCS_BASE_INCREASE_SUPPORT_ADDITION = 'ltcs_base_increase_support_addition';
    private const LTCS_PROVISION_REPORT = 'ltcs_provision_report';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::LTCS_PROVISION_REPORT, function (Blueprint $table): void {
            $table->catalogued(self::LTCS_BASE_INCREASE_SUPPORT_ADDITION, 'ベースアップ等支援加算', 'base_increase_support_addition', 'location_addition');
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
            $table->dropForeign('ltcs_provision_report_base_increase_support_addition_foreign');
            $table->dropColumn('base_increase_support_addition');
        });
    }
}
