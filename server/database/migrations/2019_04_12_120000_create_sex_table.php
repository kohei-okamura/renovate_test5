<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 性別マスタテーブルを追加する.
 */
final class CreateSexTable extends Migration
{
    private $sex = 'sex';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->sex, '性別', $this->sexes());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->sex);
    }

    /**
     * 性別の定義一覧.
     *
     * @return array
     */
    private function sexes(): array
    {
        return [
            [0, '不明'],
            [1, '男性'],
            [2, '女性'],
            [9, '該当なし'],
        ];
    }
}
