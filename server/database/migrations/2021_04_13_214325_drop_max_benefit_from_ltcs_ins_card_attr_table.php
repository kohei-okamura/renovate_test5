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
 * 介護保険被保険者証属性テーブルから区分支給限度基準額を削除する.
 */
final class DropMaxBenefitFromLtcsInsCardAttrTable extends Migration
{
    private const LTCS_INS_CARD_ATTR = 'ltcs_ins_card_attr';
    private const MAX_BENEFIT = 'max_benefit';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::LTCS_INS_CARD_ATTR, function (Blueprint $table) {
            $table->dropColumn(self::MAX_BENEFIT);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn(self::LTCS_INS_CARD_ATTR, self::MAX_BENEFIT)) {
            Schema::table(self::LTCS_INS_CARD_ATTR, function (Blueprint $table) {
                $table->integer(self::MAX_BENEFIT, '区分支給限度基準額')->default(0)->after('insurer_name');
            });
        }
    }
}
