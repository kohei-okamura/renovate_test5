<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 事業領域マスタテーブルを追加する.
 */
final class CreateServiceSegmentTable extends Migration
{
    private $serviceSegment = 'service_segment';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->serviceSegment, '事業領域', $this->serviceSegments());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->serviceSegment);
    }

    /**
     * 事業領域の定義一覧.
     *
     * @return array
     */
    private function serviceSegments(): array
    {
        return [
            [1, '障害福祉サービス'],
            [2, '介護保険サービス'],
            [3, '総合事業'],
            [4, '地域生活支援事業'],
            [7, '自費サービス'],
            [9, 'その他'],
        ];
    }
}
