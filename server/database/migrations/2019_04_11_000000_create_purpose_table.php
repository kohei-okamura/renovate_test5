<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 事業者区分マスタテーブルを追加する.
 */
final class CreatePurposeTable extends Migration
{
    private $purpose = 'purpose';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->purpose, '事業者区分', $this->purposes());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->purpose);
    }

    /**
     * 事業者区分の定義一覧.
     *
     * @return array
     */
    private function purposes(): array
    {
        return [
            [0, '不明'],
            [1, '自社'],
            [2, '他社'],
        ];
    }
}
