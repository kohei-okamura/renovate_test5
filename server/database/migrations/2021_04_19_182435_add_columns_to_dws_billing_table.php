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
 * 障害福祉サービス：請求関連のテーブルに事業所関連のカラムを追加する.
 */
final class AddColumnsToDwsBillingTable extends Migration
{
    private const DWS_BILLING = 'dws_billing';
    private const DWS_BILLING_COPAY_COORDINATION = 'dws_billing_copay_coordination';
    private const DWS_BILLING_COPAY_COORDINATION_ITEM = 'dws_billing_copay_coordination_item';
    private const DWS_BILLING_STATEMENT = 'dws_billing_statement';
    private const PREFECTURE = 'prefecture';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::DWS_BILLING, function (Blueprint $table): void {
            // COLUMNS
            $this->addDwsBillingOfficeColumns($table);
        });
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            // COLUMNS
            $this->addDwsBillingOfficeColumns($table);
        });
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION_ITEM, function (Blueprint $table): void {
            // COLUMNS
            $this->addDwsBillingOfficeColumns($table);
        });
        Schema::table(self::DWS_BILLING_STATEMENT, function (Blueprint $table): void {
            // COLUMNS
            $this->addDwsBillingOfficeColumns($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::DWS_BILLING_STATEMENT, function (Blueprint $table): void {
            $this->dropDwsBillingOfficeColumns($table);
        });
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION_ITEM, function (Blueprint $table): void {
            $this->dropDwsBillingOfficeColumns($table);
        });
        Schema::table(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            $this->dropDwsBillingOfficeColumns($table);
        });
        Schema::table(self::DWS_BILLING, function (Blueprint $table): void {
            $this->dropDwsBillingOfficeColumns($table);
        });
    }

    /**
     * 障害福祉サービス請求：事業所関連のカラムを追加する.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    private function addDwsBillingOfficeColumns(Blueprint $table): void
    {
        $table->string('office_abbr', 200)
            ->comment('事業所：略称')
            ->after('office_name');
        $table->string('office_addr_postcode', 8)
            ->charset('binary')
            ->comment('事業所：所在地：郵便番号')
            ->after('office_abbr');
        $table->integer('office_addr_prefecture')
            ->unsigned()
            ->nullable()
            ->comment('事業所：所在地：都道府県')
            ->after('office_addr_postcode');
        $table->foreign('office_addr_prefecture', $table->buildForeignKeyName('office_addr_prefecture'))
            ->references('id')
            ->on(self::PREFECTURE);
        $table->string('office_addr_city', 200)
            ->comment('事業所：所在地：市区町村')
            ->after('office_addr_prefecture');
        $table->string('office_addr_street', 200)
            ->comment('事業所：所在地：町名・番地')
            ->after('office_addr_city');
        $table->string('office_addr_apartment', 200)
            ->comment('事業所：所在地：建物名など')
            ->after('office_addr_street');
        $table->string('office_tel', 13)
            ->comment('事業所：電話番号')
            ->after('office_addr_apartment');
    }

    /**
     * 障害福祉サービス請求：事業所関連のカラムを削除する.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    private function dropDwsBillingOfficeColumns(Blueprint $table): void
    {
        $table->dropColumn('office_tel');
        $table->dropColumn('office_addr_apartment');
        $table->dropColumn('office_addr_street');
        $table->dropColumn('office_addr_city');
        $table->dropForeign($table->buildForeignKeyName('office_addr_prefecture') ?? $table->getTable() . '_' . 'office_addr_prefecture_foreign');
        $table->dropColumn('office_addr_prefecture');
        $table->dropColumn('office_addr_postcode');
        $table->dropColumn('office_abbr');
    }
}
