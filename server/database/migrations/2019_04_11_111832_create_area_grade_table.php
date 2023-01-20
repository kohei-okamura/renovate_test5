<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 地域区分マスタテーブルを追加する.
 */
final class CreateAreaGradeTable extends Migration
{
    private $dwsAreaGrade = 'dws_area_grade';
    private $ltcsAreaGrade = 'ltcs_area_grade';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->dwsAreaGrade, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('障害地域区分ID');
            $table->code(2)->comment('障害地域区分コード');
            $table->string('name', 10)->comment('障害地域区分名');
        });
        Schema::create($this->ltcsAreaGrade, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('介保地域区分ID');
            $table->code(2)->comment('介保地域区分コード');
            $table->string('name', 10)->comment('介保地域区分名');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->ltcsAreaGrade);
        Schema::dropIfExists($this->dwsAreaGrade);
    }
}
