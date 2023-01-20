<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 介護保険サービス サービス詳細・明細書明細テーブルにサービスコード区分を追加する.
 */
class AddColumnServiceCodeCategory extends Migration
{
    private const LTCS_BILLING_SERVICE_DETAIL = 'ltcs_billing_service_detail';
    private const LTCS_SERVICE_CODE_CATEGORY = 'ltcs_service_code_category';
    private const LTCS_BILLING_STATEMENT_ITEM = 'ltcs_billing_statement_item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 後続のマイグレーションで初期値を設定するため一時的に nullable で追加する
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->comment('サービスコード区分')->after('service_category_code')->nullable();
            $table->foreign('service_code_category')->references('id')->on(self::LTCS_SERVICE_CODE_CATEGORY);
        });
        Schema::table(self::LTCS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->comment('サービスコード区分')->after('service_category_code')->nullable();
            $table->foreign('service_code_category')->references('id')->on(self::LTCS_SERVICE_CODE_CATEGORY);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->dropForeign(self::LTCS_BILLING_SERVICE_DETAIL . '_service_code_category_foreign');
            $table->dropColumn('service_code_category');
        });
        Schema::table(self::LTCS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
            $table->dropForeign(self::LTCS_BILLING_STATEMENT_ITEM . '_service_code_category_foreign');
            $table->dropColumn('service_code_category');
        });
    }
}
