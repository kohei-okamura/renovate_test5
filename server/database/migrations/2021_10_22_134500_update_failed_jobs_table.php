<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

/**
 * failed_jobs テーブルを Laravel 8.x の形式に更新する.
 */
class UpdateFailedJobsTable extends Migration
{
    private const FAILED_JOBS = 'failed_jobs';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = self::FAILED_JOBS;
        DB::delete("DELETE FROM {$table}");
        Schema::table(self::FAILED_JOBS, function (Blueprint $table): void {
            $table->string('uuid')->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::FAILED_JOBS, function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
}
