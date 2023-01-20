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
 * 口座振替データに口座振替日カラムを追加する.
 */
final class AddDeductedOnToWithdrawalTransactionTable extends Migration
{
    private const WITHDRAWAL_TRANSACTION = 'withdrawal_transaction';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // デフォルト値無しで追加するとエラーになるため
        // 一旦デフォルト値付きで追加し、その後にデフォルト値を外す
        Schema::table(self::WITHDRAWAL_TRANSACTION, function (Blueprint $table): void {
            $table->date('deducted_on')
                ->comment('口座振替日')
                ->default('2010-01-01 00:00:00')
                ->after('organization_id');
        });
        Schema::table(self::WITHDRAWAL_TRANSACTION, function (Blueprint $table): void {
            $table->date('deducted_on')
                ->comment('口座振替日')
                ->default(null)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::WITHDRAWAL_TRANSACTION, function (Blueprint $table) {
            $table->dropColumn('deducted_on');
        });
    }
}
