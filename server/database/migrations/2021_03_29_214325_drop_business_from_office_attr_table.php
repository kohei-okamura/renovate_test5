<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 事業所属性テーブルから事業内容カラムを削除する.
 */
final class DropBusinessFromOfficeAttrTable extends Migration
{
    private const OFFICE_ATTR = 'office_attr';
    private const BUSINESS = 'business';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::OFFICE_ATTR, function (Blueprint $table) {
            $table->dropForeign(self::OFFICE_ATTR . '_' . self::BUSINESS . '_foreign');
            $table->dropColumn(self::BUSINESS);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn(self::OFFICE_ATTR, self::BUSINESS)) {
            Schema::table(self::OFFICE_ATTR, function (Blueprint $table) {
                $table->catalogued(self::BUSINESS, '事業内容');
            });
        }
    }
}
