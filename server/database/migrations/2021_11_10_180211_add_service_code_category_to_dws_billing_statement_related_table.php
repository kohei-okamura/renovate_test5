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
 * 障害福祉サービス サービス詳細・明細書明細テーブルにサービスコード区分を追加する.
 */
class AddServiceCodeCategoryToDwsBillingStatementRelatedTable extends Migration
{
    private const DWS_BILLING_SERVICE_DETAIL = 'dws_billing_service_detail';
    private const DWS_SERVICE_CODE_CATEGORY = 'dws_service_code_category';
    private const DWS_BILLING_STATEMENT_ITEM = 'dws_billing_statement_item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // 後続のマイグレーションで初期値を設定するため一時的に nullable で追加する
        Schema::table(self::DWS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->comment('サービスコード区分')->after('service_category_code')->nullable();
            $table->foreign('service_code_category')->references('id')->on(self::DWS_SERVICE_CODE_CATEGORY);
        });
        Schema::table(self::DWS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->comment('サービスコード区分')->after('service_category_code')->nullable();
            $table->foreign('service_code_category')->references('id')->on(self::DWS_SERVICE_CODE_CATEGORY);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::DWS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->dropForeign(self::DWS_BILLING_SERVICE_DETAIL . '_service_code_category_foreign');
            $table->dropColumn('service_code_category');
        });
        Schema::table(self::DWS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
            $table->dropForeign(self::DWS_BILLING_STATEMENT_ITEM . '_service_code_category_foreign');
            $table->dropColumn('service_code_category');
        });
    }
}
