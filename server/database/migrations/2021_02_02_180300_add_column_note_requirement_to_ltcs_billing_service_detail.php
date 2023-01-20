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
 * 介護保険サービス：請求：サービス詳細に「摘要欄記載要件」を追加する.
 */
final class AddColumnNoteRequirementToLtcsBillingServiceDetail extends Migration
{
    private const LTCS_BILLING_SERVICE_DETAIL = 'ltcs_billing_service_detail';
    private const LTCS_NOTE_REQUIREMENT = 'ltcs_note_requirement';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // デフォルト値なしで実行すると外部キー制約を設定するためエラーとなる
        // そのため一旦デフォルト値を設定した後にそのデフォルト値をなくす
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table) {
            $table->foreignId('note_requirement')
                ->type('integer')
                ->unsigned()
                ->default(99)
                ->after('building_subtraction')
                ->comment('摘要欄記載要件')
                ->constrained(self::LTCS_NOTE_REQUIREMENT);
        });
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table) {
            $table->unsignedInteger('note_requirement')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table) {
            $table->dropConstrainedForeignId('note_requirement');
        });
    }
}
