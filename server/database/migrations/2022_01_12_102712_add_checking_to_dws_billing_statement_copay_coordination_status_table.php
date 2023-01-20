<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 「障害福祉サービス：明細書：上限管理区分」に「入力中」を追加する（ついでに削除し忘れていた不要な区分値を削除）.
 */
final class AddCheckingToDwsBillingStatementCopayCoordinationStatusTable extends Migration
{
    private const DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS = 'dws_billing_statement_copay_coordination_status';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::unseedCatalogue(self::DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS, $this->statusesForDelete());
        Schema::seedCatalogue(self::DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS, $this->statusesForInsert());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS, $this->statusesForInsert());
        Schema::seedCatalogue(self::DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS, $this->statusesForDelete());
    }

    /**
     * 追加する区分値の一覧.
     *
     * @return array
     */
    private function statusesForInsert(): array
    {
        return [
            [23, '入力中'],
        ];
    }

    /**
     * 削除する区分値の一覧.
     *
     * @return array
     */
    private function statusesForDelete(): array
    {
        return [
            [1, '不要'],
            [2, '未作成'],
            [3, '未入力'],
            [4, '入力済'],
        ];
    }
}
