<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 指定区分マスタテーブルを追加する.
 */
final class CreateDesignationTable extends Migration
{
    private $designation = 'designation';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->designation, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('指定区分ID');
            $table->catalogued('business', '事業内容');
            $table->code(100)->unique()->comment('指定区分コード');
            $table->string('name', 100)->comment('指定区分名');
            $table->string('display_name', 100)->comment('表示名');
            $table->sortOrder();
            $table->createdAt();
            // KEYS
            $table->unique(['business', 'sort_order'], 'designation_sort_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->designation);
    }
}
