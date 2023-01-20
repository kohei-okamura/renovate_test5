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
 * 障害福祉サービス：予実にインデックスを追加する.
 */
final class AddIndexToDwsProvisionReport extends Migration
{
    private const DWS_PROVISION_REPORT = 'dws_provision_report';
    private const DWS_PROVISION_REPORT_BILLING_INDEX = 'dws_provision_report_billing_index';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::DWS_PROVISION_REPORT, function (Blueprint $table) {
            $table->index(
                ['provided_in', 'user_id', 'office_id', 'fixed_at'],
                self::DWS_PROVISION_REPORT_BILLING_INDEX
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::DWS_PROVISION_REPORT, function (Blueprint $table) {
            $table->dropIndex(self::DWS_PROVISION_REPORT_BILLING_INDEX);
        });
    }
}
