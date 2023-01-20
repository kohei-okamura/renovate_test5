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
 * 介護保険サービス：請求：サービス詳細に「支給限度額対象フラグ」を追加する.
 */
final class AddColumnIsLimitedToLtcsBillingServiceDetail extends Migration
{
    private const LTCS_BILLING_SERVICE_DETAIL = 'ltcs_billing_service_detail';

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
            $table->boolean('is_limited')->default(false)->comment('支給限度額対象フラグ')->after('is_addition');
        });
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table) {
            $table->boolean('is_limited')->default(null)->change();
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
            $table->dropColumn('is_limited');
        });
    }
}
