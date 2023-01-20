<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 介護保険サービス：明細書テーブルの居宅サービス計画：事業所IDカラムを nullable に変更する.
 */
class ChangeCarePlanAuthorOfficeIdToNullableOfLtcsBillingStatementTable extends Migration
{
    private const LTCS_BILLING_STATEMENT = 'ltcs_billing_statement';
    private const CARE_PLAN_AUTHOR_OFFICE = 'care_plan_author_office';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::LTCS_BILLING_STATEMENT, function (Blueprint $table) {
            $table->bigInteger(self::CARE_PLAN_AUTHOR_OFFICE . '_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::LTCS_BILLING_STATEMENT, function (Blueprint $table) {
            $table->bigInteger(self::CARE_PLAN_AUTHOR_OFFICE . '_id')->unsigned()->nullable(false)->change();
        });
    }
}
