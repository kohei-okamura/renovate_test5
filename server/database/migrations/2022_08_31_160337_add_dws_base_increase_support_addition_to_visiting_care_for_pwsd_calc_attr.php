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
 * 障害福祉サービス：ベースアップ等支援加算テーブルを追加する.
 */
class AddDwsBaseIncreaseSupportAdditionToVisitingCareForPwsdCalcAttr extends Migration
{
    private const DWS_BASE_INCREASE_SUPPORT_ADDITION = 'dws_base_increase_support_addition';
    private const VISITING_CARE_FOR_PWSD_CALC_ATTR = 'visiting_care_for_pwsd_calc_spec_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::createCatalogue(self::DWS_BASE_INCREASE_SUPPORT_ADDITION, '障害福祉サービス：ベースアップ等支援加算', $this->additions());
        Schema::table(self::VISITING_CARE_FOR_PWSD_CALC_ATTR, function (Blueprint $table): void {
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
        Schema::table(self::VISITING_CARE_FOR_PWSD_CALC_ATTR, function (Blueprint $table): void {
            $table->dropForeign(self::VISITING_CARE_FOR_PWSD_CALC_ATTR . '_bisa_foreign');
            $table->dropColumn('base_increase_support_addition');
        });
        Schema::dropIfExists(self::DWS_BASE_INCREASE_SUPPORT_ADDITION);
    }

    /**
     * 追加する区分値の一覧.
     *
     * @return array
     */
    private function additions(): array
    {
        return [
            [0, 'なし'],
            [1, '福祉・介護職員等ベースアップ等支援加算'],
        ];
    }
}
