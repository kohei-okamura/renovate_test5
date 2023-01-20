<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 障害支援区分マスタテーブルを追加する.
 */
final class CreateDwsLevelTable extends Migration
{
    private $dwsLevel = 'dws_level';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->dwsLevel, '障害支援区分', $this->level());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->dwsLevel);
    }

    protected function level()
    {
        return [
            [0, '非該当'],
            [1, '区分1'],
            [2, '区分2'],
            [3, '区分3'],
            [4, '区分4'],
            [5, '区分5'],
            [6, '区分6'],
        ];
    }
}
