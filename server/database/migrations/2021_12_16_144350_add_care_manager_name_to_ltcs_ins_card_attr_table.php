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
 * 介護保険被保険者証テーブルに居宅介護支援事業所：担当者カラムを追加する.
 */
class AddCareManagerNameToLtcsInsCardAttrTable extends Migration
{
    private const LTCS_INS_CARD_ATTR = 'ltcs_ins_card_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::LTCS_INS_CARD_ATTR, function (Blueprint $table): void {
            $table->string('care_manager_name', 100)
                ->comment('居宅介護支援事業所：担当者')
                ->after('copay_deactivated_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::LTCS_INS_CARD_ATTR, function (Blueprint $table): void {
            $table->dropColumn('care_manager_name');
        });
    }
}
