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
 * 介護保険サービス：訪問介護：算定情報テーブルにベースアップ等支援加算を追加する.
 */
class AddLtcsBaseIncreaseSupportAdditionToHomeVisitLongTermCareCalcSpecAttr extends Migration
{
    private const LTCS_BASE_INCREASE_SUPPORT_ADDITION = 'ltcs_base_increase_support_addition';
    private const HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ATTR = 'home_visit_long_term_care_calc_spec_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::createCatalogue(self::LTCS_BASE_INCREASE_SUPPORT_ADDITION, '介護保険サービス：ベースアップ等支援加算', $this->additions());
        Schema::table(self::HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ATTR, function (Blueprint $table): void {
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
        Schema::table(self::HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ATTR, function (Blueprint $table): void {
            $table->dropForeign(self::HOME_VISIT_LONG_TERM_CARE_CALC_SPEC_ATTR . '_bisa_foreign');
            $table->dropColumn('base_increase_support_addition');
        });
        Schema::dropIfExists(self::LTCS_BASE_INCREASE_SUPPORT_ADDITION);
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
