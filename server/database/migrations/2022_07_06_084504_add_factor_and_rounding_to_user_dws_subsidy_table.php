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
 * 利用者：自治体助成情報に基準値種別と端数処理区分を追加する.
 */
class AddFactorAndRoundingToUserDwsSubsidyTable extends Migration
{
    private const USER_DWS_SUBSIDY_FACTOR = 'user_dws_subsidy_factor';
    private const ROUNDING = 'rounding';
    private const USER_DWS_SUBSIDY_ATTR = 'user_dws_subsidy_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::createCatalogue(
            self::USER_DWS_SUBSIDY_FACTOR,
            '利用者：自治体助成情報：基準値種別',
            $this->userDwsSubsidyFactors()
        );
        Schema::createCatalogue(
            self::ROUNDING,
            '端数処理区分',
            $this->roundings()
        );
        // デフォルト値なしで実行すると外部キー制約を設定するためエラーとなる
        // そのため一旦デフォルト値を設定した後にそのデフォルト値をなくす
        Schema::table(self::USER_DWS_SUBSIDY_ATTR, function (Blueprint $table): void {
            $table->catalogued(
                self::USER_DWS_SUBSIDY_FACTOR,
                '基準値種別',
                'factor',
                'dws_subsidy_type',
                0
            );
        });
        Schema::table(self::USER_DWS_SUBSIDY_ATTR, function (Blueprint $table): void {
            $table->unsignedInteger('factor')->default(null)->change();
        });
        // デフォルト値なしで実行すると外部キー制約を設定するためエラーとなる
        // そのため一旦デフォルト値を設定した後にそのデフォルト値をなくす
        Schema::table(self::USER_DWS_SUBSIDY_ATTR, function (Blueprint $table): void {
            $table->catalogued(
                self::ROUNDING,
                '端数処理区分',
                'rounding',
                'benefit_rate',
                0
            );
        });
        Schema::table(self::USER_DWS_SUBSIDY_ATTR, function (Blueprint $table): void {
            $table->unsignedInteger('rounding')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::USER_DWS_SUBSIDY_ATTR, function (Blueprint $table): void {
            $table->dropForeign(self::USER_DWS_SUBSIDY_ATTR . '_rounding_foreign');
            $table->dropColumn('rounding');
        });
        Schema::table(self::USER_DWS_SUBSIDY_ATTR, function (Blueprint $table): void {
            $table->dropForeign(self::USER_DWS_SUBSIDY_ATTR . '_factor_foreign');
            $table->dropColumn('factor');
        });
        Schema::dropIfExists(self::ROUNDING);
        Schema::dropIfExists(self::USER_DWS_SUBSIDY_FACTOR);
    }

    /**
     * 利用者：自治体助成情報：基準値種別の定義一覧.
     *
     * @return array
     */
    private function userDwsSubsidyFactors(): array
    {
        return [
            [0, '未設定'],
            [1, '総費用額'],
            [2, '1割相当額'],
        ];
    }

    /**
     * 端数処理区分の定義一覧.
     *
     * @return array
     */
    private function roundings(): array
    {
        return [
            [0, '未設定'],
            [1, '切り捨て'],
            [2, '切り上げ'],
            [3, '四捨五入'],
        ];
    }
}
