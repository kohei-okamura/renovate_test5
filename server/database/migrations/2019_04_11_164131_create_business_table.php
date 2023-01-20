<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 事業内容マスタテーブルを追加する.
 */
final class CreateBusinessTable extends Migration
{
    private $business = 'business';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->business, '事業内容', $this->businesses());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->business);
    }

    /**
     * 事業内容の定義一覧.
     *
     * @return array
     */
    private function businesses(): array
    {
        return [
            [1, '本社業務'],
            [2, '訪問介護'],
            [3, '訪問看護'],
            [4, 'デイサービス'],
            [5, '居宅介護支援'],
            [6, 'カレッジ'],
            [7, 'マッサージ'],
        ];
    }
}
