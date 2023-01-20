<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 契約に提供期間のカラムを追加したりする.
 */
final class AddPeriodColumnsToContract extends Migration
{
    private const CONTRACT_ATTR = 'contract_attr';
    private const CONTRACT_ATTR_DWS_PERIOD = 'contract_attr_dws_period';

    private const LTCS_EXPIRED_REASON = 'ltcs_expired_reason';
    private const DWS_SERVICE_DIVISION_CODE = 'dws_service_division_code';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::createStringCatalogue(
            self::DWS_SERVICE_DIVISION_CODE,
            '障害福祉サービス：請求：サービス種類コード',
            self::dwsServiceDivisionCodes()
        );
        Schema::table(self::CONTRACT_ATTR, function (Blueprint $table) {
            $table->date('ltcs_period_start')->nullable()->after('terminated_on')->comment('介護保険サービス提供期間：初回サービス提供日');
            $table->date('ltcs_period_end')->nullable()->after('ltcs_period_start')->comment('介護保険サービス提供期間：最終サービス提供日');

            // `catalogued` を使うと down 時にどう足掻いてもインデックスが削除できなかったので
            // 手動でカラム＆外部キー制約を設定する
            $table->unsignedInteger('expired_reason')->after('ltcs_period_end')->comment('介護保険サービス中止理由');
            $table->foreign(['expired_reason'])->references('id')->on(self::LTCS_EXPIRED_REASON);

            $table->string('note')->after('expired_reason')->comment('備考');
            $table->dropColumn('terminated_reason');
        });
        Schema::create(self::CONTRACT_ATTR_DWS_PERIOD, function (Blueprint $table) {
            $table->id()->comment('ID');
            $table->references(self::CONTRACT_ATTR, '属性')->onDelete('cascade');
            $table->stringCatalogued(self::DWS_SERVICE_DIVISION_CODE, 'サービス種類コード', 'service_division_code');
            $table->date('start')->nullable()->comment('初回サービス提供日');
            $table->date('end')->nullable()->comment('最終サービス提供日');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(self::CONTRACT_ATTR_DWS_PERIOD);
        Schema::table(self::CONTRACT_ATTR, function (Blueprint $table) {
            $table->string('terminated_reason')->after('note')->comment('解約理由');
            $table->dropColumn('note');
            $table->dropForeign(['expired_reason']);
            $table->dropColumn('expired_reason');
            $table->dropColumn('ltcs_period_end');
            $table->dropColumn('ltcs_period_start');
        });
        Schema::dropIfExists(self::DWS_SERVICE_DIVISION_CODE);
    }

    /**
     * 障害福祉サービス：請求：サービス種類コード.
     *
     * @return array
     */
    private static function dwsServiceDivisionCodes(): array
    {
        return [
            ['11', '居宅介護'],
            ['12', '重度訪問介護'],
        ];
    }
}
