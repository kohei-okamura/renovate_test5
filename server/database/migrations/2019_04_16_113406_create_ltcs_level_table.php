<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 介護保険要介護度マスタテーブルを追加する.
 */
final class CreateLtcsLevelTable extends Migration
{
    private $ltcsLevel = 'ltcs_level';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->ltcsLevel, '要介護度', $this->ltcsLevels());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->ltcsLevel);
    }

    /**
     * 要介護度（要介護状態区分等）の定義一覧.
     *
     * @return array
     */
    protected function ltcsLevels(): array
    {
        return [
            [6, 'target'],
            [12, 'supportLevel1'],
            [13, 'supportLevel2'],
            [21, 'careLevel1'],
            [22, 'careLevel2'],
            [23, 'careLevel3'],
            [24, 'careLevel4'],
            [25, 'careLevel5'],
        ];
    }
}
