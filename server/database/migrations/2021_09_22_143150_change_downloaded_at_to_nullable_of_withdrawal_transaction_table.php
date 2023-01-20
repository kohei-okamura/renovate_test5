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
 * 口座振替データテーブルの最終ダウンロード日時を nullable に変更する.
 */
class ChangeDownloadedAtToNullableOfWithdrawalTransactionTable extends Migration
{
    private const WITHDRAWAL_TRANSACTION = 'withdrawal_transaction';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::WITHDRAWAL_TRANSACTION, function (Blueprint $table): void {
            $table->dateTime('downloaded_at')->nullable()->comment('最終ダウンロード日時')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::WITHDRAWAL_TRANSACTION, function (Blueprint $table): void {
            $table->dateTime('downloaded_at')->comment('最終ダウンロード日時')->change();
        });
    }
}
