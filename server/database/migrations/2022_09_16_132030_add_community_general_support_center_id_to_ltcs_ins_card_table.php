<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 介護保険被保険者証に地域包括支援センターIDを追加する.
 */
class AddCommunityGeneralSupportCenterIdToLtcsInsCardTable extends Migration
{
    private const LTCS_INS_CARD_ATTR = 'ltcs_ins_card_attr';
    private const OFFICE = 'office';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::LTCS_INS_CARD_ATTR, function (Blueprint $table): void {
            $table->foreignId('community_general_support_center_id')
                ->nullable()
                ->after('care_plan_author_office_id')
                ->comment('地域包括支援センター ID')
                ->constrained(self::OFFICE);
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
            $table->dropConstrainedForeignId('community_general_support_center_id');
        });
    }
}
