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
 * 障害福祉サービス サービス詳細・明細書明細テーブルのサービスコード区分に値を設定し、属性を not null に変更する.
 */
class AddInitialValueToDwsServiceCodeCategory extends Migration
{
    private const DWS_BILLING_SERVICE_DETAIL = 'dws_billing_service_detail';
    private const DWS_BILLING_STATEMENT_ITEM = 'dws_billing_statement_item';

    private const HOME_HELP_SERVICE_DETAIL_UPDATE_QUERY = <<<'EOD'
UPDATE
    dws_billing_service_detail AS a
    JOIN dws_billing_bundle AS b ON b.id = a.dws_billing_bundle_id
    JOIN dws_home_help_service_dictionary_entry AS c ON c.service_code = a.service_code
    JOIN dws_home_help_service_dictionary AS d ON d.id = c.dws_home_help_service_dictionary_id
SET
    a.service_code_category = c.category
WHERE
    a.service_division_code = '11' AND
    d.effectivated_on = (
        SELECT
            MAX(effectivated_on)
        FROM
            dws_home_help_service_dictionary
        WHERE
            effectivated_on <= b.provided_in
    )
;
EOD;

    private const HOME_HELP_SERVICE_ITEM_UPDATE_QUERY = <<<'EOD'
UPDATE
    dws_billing_statement_item AS a
    JOIN dws_billing_statement as b ON b.id = a.dws_billing_statement_id
    JOIN dws_billing_bundle AS c ON c.id = b.dws_billing_bundle_id
    JOIN dws_home_help_service_dictionary_entry AS d ON d.service_code = a.service_code
    JOIN dws_home_help_service_dictionary AS e ON e.id = d.dws_home_help_service_dictionary_id
SET
    a.service_code_category = d.category
WHERE
    a.service_division_code = '11' AND
    e.effectivated_on = (
        SELECT
            MAX(effectivated_on)
        FROM
            dws_home_help_service_dictionary
        WHERE
            effectivated_on <= c.provided_in
    )
;
EOD;

    private const VISITING_CARE_FOR_PWSD_DETAIL_UPDATE_QUERY = <<<'EOD'
UPDATE
    dws_billing_service_detail AS a
    JOIN dws_billing_bundle AS b ON b.id = a.dws_billing_bundle_id
    JOIN dws_visiting_care_for_pwsd_dictionary_entry AS c ON c.service_code = a.service_code
    JOIN dws_visiting_care_for_pwsd_dictionary AS d ON d.id = c.dws_visiting_care_for_pwsd_dictionary_id
SET
    a.service_code_category = c.category
WHERE
    a.service_division_code = '12' AND
    d.effectivated_on = (
        SELECT
            MAX(effectivated_on)
        FROM
            dws_visiting_care_for_pwsd_dictionary
        WHERE
            effectivated_on <= b.provided_in
    )
;
EOD;

    private const VISITING_CARE_FOR_PWSD_ITEM_UPDATE_QUERY = <<<'EOD'
UPDATE
    dws_billing_statement_item AS a
    JOIN dws_billing_statement as b ON b.id = a.dws_billing_statement_id
    JOIN dws_billing_bundle AS c ON c.id = b.dws_billing_bundle_id
    JOIN dws_visiting_care_for_pwsd_dictionary_entry AS d ON d.service_code = a.service_code
    JOIN dws_visiting_care_for_pwsd_dictionary AS e ON e.id = d.dws_visiting_care_for_pwsd_dictionary_id
SET
    a.service_code_category = d.category
WHERE
    a.service_division_code = '12' AND
    e.effectivated_on = (
        SELECT
            MAX(effectivated_on)
        FROM
            dws_visiting_care_for_pwsd_dictionary
        WHERE
            effectivated_on <= c.provided_in
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
        DB::update(self::HOME_HELP_SERVICE_DETAIL_UPDATE_QUERY);
        DB::update(self::VISITING_CARE_FOR_PWSD_DETAIL_UPDATE_QUERY);
        Schema::table(self::DWS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->nullable(false)->change();
        });
        DB::update(self::HOME_HELP_SERVICE_ITEM_UPDATE_QUERY);
        DB::update(self::VISITING_CARE_FOR_PWSD_ITEM_UPDATE_QUERY);
        Schema::table(self::DWS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
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
        Schema::table(self::DWS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->nullable()->change();
        });
        DB::update('update dws_billing_service_detail set service_code_category = NULL');
        Schema::table(self::DWS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
            $table->integer('service_code_category')->unsigned()->nullable()->change();
        });
        DB::update('update dws_billing_statement_item set service_code_category = NULL');
    }
}
