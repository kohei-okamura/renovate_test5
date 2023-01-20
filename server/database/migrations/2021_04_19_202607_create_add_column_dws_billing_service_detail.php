<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 障害福祉サービス：サービス詳細に加算フラグを追加
 */
class CreateAddColumnDwsBillingServiceDetail extends Migration
{
    private const DWS_BILLING_SERVICE_DETAIL = 'dws_billing_service_detail';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // デフォルト値なしで実行すると外部キー制約を設定するためエラーとなる
        // そのため一旦デフォルト値を設定した後にそのデフォルト値をなくす
        Schema::table(self::DWS_BILLING_SERVICE_DETAIL, function (Blueprint $table) {
            $table->boolean('is_addition')->default(false)->comment('加算フラグ')->after('service_category_code');
        });
        Schema::table(self::DWS_BILLING_SERVICE_DETAIL, function (Blueprint $table) {
            $table->boolean('is_addition')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::DWS_BILLING_SERVICE_DETAIL, function (Blueprint $table) {
            $table->dropColumn('is_addition');
        });
    }
}
