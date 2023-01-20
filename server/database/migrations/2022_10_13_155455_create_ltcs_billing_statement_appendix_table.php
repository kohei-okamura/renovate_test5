<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 介護保険サービス：明細書：サービス提供票別表テーブルを作成する.
 *
 * @noinspection PhpUnused
 */
final class CreateLtcsBillingStatementAppendixTable extends Migration
{
    private const LTCS_BILLING_STATEMENT = 'ltcs_billing_statement';
    private const LTCS_BILLING_STATEMENT_APPENDIX = 'ltcs_billing_statement_appendix';
    private const LTCS_BILLING_STATEMENT_APPENDIX_ENTRY = 'ltcs_billing_statement_appendix_entry';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create(self::LTCS_BILLING_STATEMENT_APPENDIX, function (Blueprint $table): void {
            $table->id()->comment('サービス提供票別表 ID');
            $table
                ->references(self::LTCS_BILLING_STATEMENT, '介護保険サービス：明細書', 'statement_id')
                ->onDelete('cascade');
            $table->date('provided_in')->comment('サービス提供年月');
            $table->string('ins_number', 10)->charset('ascii')->comment('被保険者証番号');
            $table->string('user_name', 200)->comment('利用者氏名');
            $table->integer('max_benefit')->comment('区分支給限度基準額');
            $table->integer('insurance_claim_amount')->comment('保険請求分');
            $table->integer('subsidy_claim_amount')->comment('公費請求額');
            $table->integer('copay_amount')->comment('利用者請求額');
            $table->integer('unit_cost')->comment('単位数単価');
        });
        Schema::create(self::LTCS_BILLING_STATEMENT_APPENDIX_ENTRY, function (Blueprint $table): void {
            $table->id()->comment('サービス情報 ID');
            $table
                ->references(
                    self::LTCS_BILLING_STATEMENT_APPENDIX,
                    '介護保険サービス：明細書：サービス提供票別表',
                    'statement_appendix_id'
                )
                ->onDelete('cascade');
            $table->integer('entry_type')->comment('サービス情報区分');
            $table->sortOrder();
            $table->string('office_name', 200)->comment('事業所名');
            $table->string('office_code', 10)->charset('ascii')->comment('事業所番号');
            $table->string('service_name', 100)->comment('サービス内容/種類');
            $table->string('service_code', 6)->charset('ascii')->comment('サービスコード');
            $table->integer('unit_score')->comment('単位数');
            $table->integer('count')->comment('回数');
            $table->integer('whole_score')->comment('総単位数');
            $table->integer('max_benefit_quota_excess_score')->comment('種類支給限度基準を超える単位数');
            $table->integer('max_benefit_excess_score')->comment('区分支給限度基準を超える単位数');
            $table->integer('unit_cost')->comment('単位数単価');
            $table->integer('benefit_rate')->comment('給付率');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(self::LTCS_BILLING_STATEMENT_APPENDIX_ENTRY);
        Schema::dropIfExists(self::LTCS_BILLING_STATEMENT_APPENDIX);
    }
}
