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
 * 計画：週間サービス計画に「備考（note）」を追加する.
 */
final class AddNoteToProjectProgramTable extends Migration
{
    private const DWS_PROJECT_PROGRAM = 'dws_project_program';
    private const LTCS_PROJECT_PROGRAM = 'ltcs_project_program';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::DWS_PROJECT_PROGRAM, function (Blueprint $table) {
            $table->string('note')->comment('備考');
        });
        Schema::table(self::LTCS_PROJECT_PROGRAM, function (Blueprint $table) {
            $table->string('note')->comment('備考');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::DWS_PROJECT_PROGRAM, function (Blueprint $table) {
            $table->dropColumn('note');
        });
        Schema::table(self::LTCS_PROJECT_PROGRAM, function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
}
