<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 「利用者：自治体助成情報：基準値種別」の「1割相当額」を「決定利用者負担額」に変更する.
 */
class UpdateCopayOfUserSubsidyFactor extends Migration
{
    private const USER_DWS_SUBSIDY_FACTOR = 'user_dws_subsidy_factor';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table(self::USER_DWS_SUBSIDY_FACTOR)
            ->where('id', 2)
            ->update(['name' => '決定利用者負担額']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table(self::USER_DWS_SUBSIDY_FACTOR)
            ->where('id', 2)
            ->update(['name' => '1割相当額']);
    }
}
