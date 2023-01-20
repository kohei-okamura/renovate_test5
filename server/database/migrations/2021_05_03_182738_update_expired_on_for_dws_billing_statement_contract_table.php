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
 * 障害福祉サービス：明細書：契約 テーブルの 契約終了年月日 を nullable に変更する.
 */
final class UpdateExpiredOnForDwsBillingStatementContractTable extends Migration
{
    private const DWS_BILLING_STATEMENT_CONTRACT = 'dws_billing_statement_contract';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::table(self::DWS_BILLING_STATEMENT_CONTRACT, function (Blueprint $table): void {
            // COLUMNS
            $table->date('expired_on')->nullable()->comment('契約終了年月日')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::DWS_BILLING_STATEMENT_CONTRACT, function (Blueprint $table): void {
            $table->date('expired_on')->comment('契約終了年月日')->change();
        });
    }
}
