<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 介護保険サービス：訪問介護：算定情報テーブルの地域加算カラムをリネームする.
 */
final class RenameColumnOfficeLocationAdditionToLocationAddition extends Migration
{
    private string $homeVisitLongTermCareCalcSpecAttr = 'home_visit_long_term_care_calc_spec_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->homeVisitLongTermCareCalcSpecAttr, function (Blueprint $table): void {
            $table->renameColumn('office_location_addition', 'location_addition');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->homeVisitLongTermCareCalcSpecAttr, function (Blueprint $table): void {
            $table->renameColumn('location_addition', 'office_location_addition');
        });
    }
}
