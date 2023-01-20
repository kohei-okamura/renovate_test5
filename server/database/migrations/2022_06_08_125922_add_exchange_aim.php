<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddExchangeAim extends Migration
{
    private const DWS_BILLING_COPAY_COORDINATION = 'dws_billing_copay_coordination';
    private const DWS_BILLING_COPAY_COORDINATION_EXCHANGE_AIM = 'dws_billing_copay_coordination_exchange_aim';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::createCatalogue(
            self::DWS_BILLING_COPAY_COORDINATION_EXCHANGE_AIM,
            '利用者負担上限額管理結果票：作成区分',
            $this->exchangeAim()
        );
        // デフォルト値なしで実行すると外部キー制約を設定するためエラーとなる
        // そのため一旦デフォルト値を設定した後にそのデフォルト値をなくす
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            $table->catalogued(
                self::DWS_BILLING_COPAY_COORDINATION_EXCHANGE_AIM,
                '作成区分',
                'exchange_aim',
                'result',
                1 // 既存データには 1（新規）を設定
            );
        });
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            $table->unsignedInteger('exchange_aim')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            $table->dropForeign(self::DWS_BILLING_COPAY_COORDINATION . '_exchange_aim_foreign');
            $table->dropColumn('exchange_aim');
        });
        Schema::dropIfExists(self::DWS_BILLING_COPAY_COORDINATION_EXCHANGE_AIM);
    }

    /**
     * 利用者負担上限額管理結果票：作成区分の定義一覧.
     *
     * @return array
     */
    private function exchangeAim(): array
    {
        return [
            [1, '新規'],
            [2, '修正'],
            [3, '取り消し'],
        ];
    }
}
