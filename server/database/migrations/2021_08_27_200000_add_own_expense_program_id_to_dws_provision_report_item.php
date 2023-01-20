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
 * 障害福祉サービス：予実：要素に自費サービス ID を追加する.
 */
final class AddOwnExpenseProgramIdToDwsProvisionReportItem extends Migration
{
    private const DWS_PROVISION_REPORT_ITEM_PLAN = 'dws_provision_report_item_plan';
    private const DWS_PROVISION_REPORT_ITEM_RESULT = 'dws_provision_report_item_result';
    private const OWN_EXPENSE_PROGRAM = 'own_expense_program';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $tables = [
            self::DWS_PROVISION_REPORT_ITEM_PLAN,
            self::DWS_PROVISION_REPORT_ITEM_RESULT,
        ];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->unsignedBigInteger(self::OWN_EXPENSE_PROGRAM . '_id')->nullable()->after('moving_duration_minutes')->comment('自費サービス情報 ID');
                $table->foreign(self::OWN_EXPENSE_PROGRAM . '_id')->references('id')->on(self::OWN_EXPENSE_PROGRAM);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $tableNames = [
            self::DWS_PROVISION_REPORT_ITEM_PLAN,
            self::DWS_PROVISION_REPORT_ITEM_RESULT,
        ];
        foreach ($tableNames as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropForeign($tableName . '_' . self::OWN_EXPENSE_PROGRAM . '_id_foreign');
                $table->dropColumn(self::OWN_EXPENSE_PROGRAM . '_id');
            });
        }
    }
}
