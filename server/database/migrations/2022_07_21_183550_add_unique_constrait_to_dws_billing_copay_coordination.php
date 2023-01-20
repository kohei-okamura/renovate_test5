<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 利用者負担上限額管理結果票テーブルの 請求単位 ID, 利用者 ID にユニークキー制約を追加する
 */
class AddUniqueConstraitToDwsBillingCopayCoordination extends Migration
{
    private const DWS_BILLING_COPAY_COORDINATION = 'dws_billing_copay_coordination';
    private const COPAY_COORDINATION_UNIQUE_KEY_IDENTIFIER = 'user_id_bundle_id_unique';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table) {
            $table->unique(['user_id', 'dws_billing_bundle_id'], self::COPAY_COORDINATION_UNIQUE_KEY_IDENTIFIER);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table) {
            $table->dropUnique(self::COPAY_COORDINATION_UNIQUE_KEY_IDENTIFIER);
        });
    }
}
