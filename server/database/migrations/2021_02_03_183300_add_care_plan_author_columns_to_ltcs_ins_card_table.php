<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 介護保険被保険者証に居宅介護支援関連のカラムを追加する.
 */
final class AddCarePlanAuthorColumnsToLtcsInsCardTable extends Migration
{
    private const LTCS_INS_CARD_ATTR = 'ltcs_ins_card_attr';
    private const LTCS_CARE_PLAN_AUTHOR_TYPE = 'ltcs_care_plan_author_type';
    private const OFFICE = 'office';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // デフォルト値なしで実行すると外部キー制約を設定するためエラーとなる
        // そのため一旦デフォルト値を設定した後にそのデフォルト値をなくす
        Schema::table(self::LTCS_INS_CARD_ATTR, function (Blueprint $table) {
            $table->foreignId('care_plan_author_type')
                ->type('integer')
                ->unsigned()
                ->nullable()
                ->after('copay_deactivated_on')
                ->comment('居宅サービス計画作成区分')
                ->constrained(self::LTCS_CARE_PLAN_AUTHOR_TYPE);
            $table->foreignId('care_plan_author_office_id')
                ->nullable()
                ->after('care_plan_author_type')
                ->comment('居宅介護支援事業所 ID')
                ->constrained(self::OFFICE);
        });
        Schema::table(self::LTCS_INS_CARD_ATTR, function (Blueprint $table) {
            $table->unsignedInteger('care_plan_author_type')->nullable(false)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::LTCS_INS_CARD_ATTR, function (Blueprint $table) {
            $table->dropConstrainedForeignId('care_plan_author_office_id');
            $table->dropConstrainedForeignId('care_plan_author_type');
        });
    }
}
