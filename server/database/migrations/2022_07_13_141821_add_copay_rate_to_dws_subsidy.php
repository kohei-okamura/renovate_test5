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
 * 自治体助成情報に本人負担率を追加する.
 */
class AddCopayRateToDwsSubsidy extends Migration
{
    private const USER_DWS_SUBSIDY_ATTR = 'user_dws_subsidy_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::USER_DWS_SUBSIDY_ATTR, function (Blueprint $table): void {
            $table->unsignedInteger('copay_rate')->after('benefit_rate')->comment('本人負担率[%]');
            $table->renameColumn('copay', 'copay_amount');
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
            $table->renameColumn('copay_amount', 'copay');
            $table->dropColumn('copay_rate');
        });
    }
}
