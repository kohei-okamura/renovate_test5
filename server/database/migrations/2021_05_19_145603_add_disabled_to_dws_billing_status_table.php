<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

class AddDisabledToDwsBillingStatusTable extends Migration
{
    private const DWS_BILLING_STATUS = 'dws_billing_status';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::seedCatalogue(self::DWS_BILLING_STATUS, $this->statuses());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::unseedCatalogue(self::DWS_BILLING_STATUS, $this->statuses());
    }

    /**
     * 追加する区分値の一覧.
     *
     * @return array
     */
    private function statuses(): array
    {
        return [
            [99, '無効'],
        ];
    }
}
