<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 介護保険サービス サービス詳細・明細書明細テーブルのサービスコード区分に値を設定し、属性を not null に変更する.
 */
class AddInitialValueToServiceCodeCategory extends Migration
{
    private const LTCS_BILLING_SERVICE_DETAIL = 'ltcs_billing_service_detail';
    private const LTCS_BILLING_STATEMENT_ITEM = 'ltcs_billing_statement_item';

    private const DETAIL_UPDATE_QUERY = <<<'EOD'
UPDATE
    ltcs_billing_service_detail AS a
    JOIN ltcs_billing_bundle AS b ON b.id = a.bundle_id
    JOIN ltcs_home_visit_long_term_care_dictionary_entry AS c ON c.service_code = a.service_code
    JOIN ltcs_home_visit_long_term_care_dictionary AS d ON d.id = c.dictionary_id
SET
    a.service_code_category = c.category
WHERE
    d.effectivated_on = (
        SELECT
            MAX(effectivated_on)
        FROM
            ltcs_home_visit_long_term_care_dictionary
        WHERE
            effectivated_on < b.provided_in
    )
;
EOD;

    private const ITEM_UPDATE_QUERY = <<<'EOD'
UPDATE
    ltcs_billing_statement_item AS a
    JOIN ltcs_billing_statement as b on b.id = a.statement_id
    JOIN ltcs_billing_bundle AS c ON c.id = b.bundle_id
    JOIN ltcs_home_visit_long_term_care_dictionary_entry AS d ON d.service_code = a.service_code
    JOIN ltcs_home_visit_long_term_care_dictionary AS e ON e.id = d.dictionary_id
SET
    a.service_code_category = d.category
WHERE
    e.effectivated_on = (
        SELECT
            MAX(effectivated_on)
        FROM
            ltcs_home_visit_long_term_care_dictionary
        WHERE
            effectivated_on < c.provided_in
    )
;
EOD;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update(self::DETAIL_UPDATE_QUERY);
        Schema::table(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->nullable(false)->change();
        });
        DB::update(self::ITEM_UPDATE_QUERY);
        Schema::table(self::LTCS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->nullable(false)->change();
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
            $table->integer('service_code_category')->unsigned()->nullable()->change();
        });
        DB::update('update ltcs_billing_service_detail set service_code_category = NULL');
        Schema::table(self::LTCS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->nullable()->change();
        });
        DB::update('update ltcs_billing_statement_item set service_code_category = NULL');
    }
}
