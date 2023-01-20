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
 * 介護保険サービス：予実にカラム「確定日時」を追加する.
 */
final class AddColumnFixedAtToLtcsProvisionReport extends Migration
{
    private const LTCS_PROVISION_REPORT = 'ltcs_provision_report';
    private const LTCS_PROVISION_REPORT_BILLING_INDEX = 'ltcs_provision_report_billing_index';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::LTCS_PROVISION_REPORT, function (Blueprint $table) {
            $table->dateTime('fixed_at')->comment('確定日時')->nullable()->after('status');
            $table->index(
                ['provided_in', 'user_id', 'office_id', 'fixed_at'],
                self::LTCS_PROVISION_REPORT_BILLING_INDEX
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
        Schema::table(self::LTCS_PROVISION_REPORT, function (Blueprint $table) {
            $table->dropIndex(self::LTCS_PROVISION_REPORT_BILLING_INDEX);
            $table->dropColumn('fixed_at');
        });
    }
}
