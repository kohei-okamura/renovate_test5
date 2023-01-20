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
 * 障害福祉サービス：居宅介護：算定情報テーブルにベースアップ等支援加算を追加する.
 */
class AddDwsBaseIncreaseSupportAdditionToHomeHelpServiceCalcSpecAttr extends Migration
{
    private const DWS_BASE_INCREASE_SUPPORT_ADDITION = 'dws_base_increase_support_addition';
    private const HOME_HELP_SERVICE_CALC_SPEC_ATTR = 'home_help_service_calc_spec_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::HOME_HELP_SERVICE_CALC_SPEC_ATTR, function (Blueprint $table): void {
            $table->catalogued(self::DWS_BASE_INCREASE_SUPPORT_ADDITION, 'ベースアップ等支援加算', 'base_increase_support_addition', 'specified_treatment_improvement_addition');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::HOME_HELP_SERVICE_CALC_SPEC_ATTR, function (Blueprint $table): void {
            $table->dropForeign(self::HOME_HELP_SERVICE_CALC_SPEC_ATTR . '_bisa_foreign');
            $table->dropColumn('base_increase_support_addition');
        });
    }
}
