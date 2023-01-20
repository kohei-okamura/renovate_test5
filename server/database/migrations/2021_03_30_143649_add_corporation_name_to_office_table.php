<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 事業所テーブルに「法人名（corporation_name）」を追加する.
 */
final class AddCorporationNameToOfficeTable extends Migration
{
    private const OFFICE_ATTR = 'office_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::OFFICE_ATTR, function (Blueprint $table) {
            $table->string('corporation_name', 200)->after('phonetic_name')->comment('法人名');
            $table->string('phonetic_corporation_name', 200)->after('corporation_name')->comment('法人名：フリガナ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::OFFICE_ATTR, function (Blueprint $table) {
            $table->dropColumn('phonetic_corporation_name');
            $table->dropColumn('corporation_name');
        });
    }
}
