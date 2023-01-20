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
 * 障害福祉サービス：明細書 テーブルを追加する.
 */
final class CreateDwsBillingStatementTable extends Migration
{
    private const DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS = 'dws_billing_statement_copay_coordination_status';
    private const DWS_GRANTED_SERVICE_CODE = 'dws_granted_service_code';

    private const DWS_BILLING_STATEMENT = 'dws_billing_statement';
    private const DWS_BILLING_STATEMENT_AGGREGATE = 'dws_billing_statement_aggregate';
    private const DWS_BILLING_STATEMENT_CONTRACT = 'dws_billing_statement_contract';
    private const DWS_BILLING_STATEMENT_ITEM = 'dws_billing_statement_item';

    private const COPAY_COORDINATION_RESULT = 'copay_coordination_result';
    private const DWS_BILLING = 'dws_billing';
    private const DWS_BILLING_BUNDLE = 'dws_billing_bundle';
    private const DWS_BILLING_STATUS = 'dws_billing_status';
    private const DWS_CERTIFICATION = 'dws_certification';
    private const OFFICE = 'office';
    private const USER = 'user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::createCatalogue(
            self::DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS,
            '障害福祉サービス：明細書：上限管理区分',
            $this->copayCoordinationstatuses()
        );
        Schema::createStringCatalogue(
            self::DWS_GRANTED_SERVICE_CODE,
            '障害福祉サービス決定サービスコード',
            $this->dwsGrantedServiceCodes()
        );
        Schema::create(self::DWS_BILLING_STATEMENT, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('障害福祉サービス明細書ID');
            $table->references(self::DWS_BILLING, '障害福祉サービス請求');
            $table->references(self::DWS_BILLING_BUNDLE, '障害福祉サービス請求単位');
            $table->string('dws_area_grade_code', 2)->comment('地域区分コード');
            $table->string('dws_area_grade_name', 10)->comment('地域区分名');
            $table->references(self::USER, '利用者');
            $table->references(self::DWS_CERTIFICATION, '障害福祉サービス受給者証', 'user_dws_certification_id');
            $table->string('user_dws_number', 20)->comment('受給者番号');
            $table->structuredName('user_', '利用者：');
            $table->structuredName('user_child_', '利用者：児童：');
            $table->integer('user_copay_limit')->comment('利用者負担上限月額');
            $table->integer('copay_limit')->comment('利用者負担上限月額');
            $keyName = $table->buildForeignKeyName('office_id');
            $table->bigInteger('office_id')->unsigned()->nullable()->comment('事業所ID');
            $table->foreign('office_id', $keyName)->references('id')->on(self::OFFICE);
            $table->string('office_code', 20)->charset('binary')->comment('事業所番号');
            $table->string('office_name', 200)->comment('事業所名');
            $keyName = $table->buildForeignKeyName('result');
            $table->integer('result')->unsigned()->nullable()->comment('管理結果');
            $table->foreign('result', $keyName)->references('id')->on(self::COPAY_COORDINATION_RESULT);
            $table->integer('amount')->nullable()->comment('管理結果額');
            $table->string('subsidy_city_code', 6)->nullable()->comment('助成自治体番号');
            $table->integer('total_score')->comment('請求額集計欄：合計：給付単位数');
            $table->integer('total_fee')->comment('請求額集計欄：合計：総費用額');
            $table->integer('total_capped_copay')->comment('請求額集計欄：合計：上限月額調整');
            $table->integer('total_adjusted_copay')->nullable()->comment('請求額集計欄：合計：調整後利用者負担額');
            $table->integer('total_coordinated_copay')->nullable()->comment('請求額集計欄：合計：上限管理後利用者負担額');
            $table->integer('total_copay')->comment('請求額集計欄：合計：決定利用者負担額');
            $table->integer('total_benefit')->comment('請求額集計欄：合計：請求額：給付費');
            $table->integer('total_subsidy')->nullable()->comment('請求額集計欄：合計：自治体助成分請求額');
            $table->boolean('is_provided')->comment('自社サービス提供有無');
            $table->catalogued(
                self::DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS,
                '上限管理区分',
                'copay_coordination_status'
            );
            $table->catalogued(self::DWS_BILLING_STATUS, '状態', 'status');
            $table->dateTime('fixed_at')->nullable()->comment('確定日時');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::DWS_BILLING_STATEMENT_AGGREGATE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('集計 ID');
            $table->references(self::DWS_BILLING_STATEMENT, '明細書')->onDelete('cascade');
            $table->string('service_division_code', 2)->comment('サービス種類コード');
            $table->date('started_on')->comment('サービス開始年月日');
            $table->date('terminated_on')->nullable()->comment('サービス終了年月日');
            $table->integer('service_days')->comment('サービス利用日数');
            $table->integer('subtotal_score')->comment('給付単位数');
            $table->integer('unit_cost')->comment('単位数単価');
            $table->integer('subtotal_fee')->comment('総費用額');
            $table->integer('unmanaged_copay')->comment('利用者負担額（1割相当額）');
            $table->integer('capped_copay')->comment('上限月額調整');
            $table->integer('adjusted_copay')->nullable()->comment('調整後利用者負担額');
            $table->integer('coordinated_copay')->nullable()->comment('上限額管理後利用者負担額');
            $table->integer('subtotal_copay')->comment('決定利用者負担額');
            $table->integer('subtotal_benefit')->comment('請求額：給付費');
            $table->integer('subtotal_subsidy')->nullable()->comment('自治体助成分請求額');
            $table->sortOrder();
            // KEYS
            $table->unique(
                [self::DWS_BILLING_STATEMENT . '_id', 'sort_order'],
                self::DWS_BILLING_STATEMENT_AGGREGATE . '_sort_order_unique'
            );
        });
        Schema::create(self::DWS_BILLING_STATEMENT_CONTRACT, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('契約 ID');
            $table->references(self::DWS_BILLING_STATEMENT, '明細書')->onDelete('cascade');
            $table->stringCatalogued(self::DWS_GRANTED_SERVICE_CODE, '決定サービスコード');
            $table->integer('granted_amount')->comment('契約支給量（分単位）');
            $table->date('agreed_on')->comment('契約開始年月日');
            $table->date('expired_on')->comment('契約終了年月日');
            $table->integer('index_number')->comment('事業者記入欄番号');
            $table->sortOrder();
            // KEYS
            $table->unique(
                [self::DWS_BILLING_STATEMENT . '_id', 'sort_order'],
                self::DWS_BILLING_STATEMENT_CONTRACT . '_sort_order_unique'
            );
        });
        Schema::create(self::DWS_BILLING_STATEMENT_ITEM, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('明細 ID');
            $table->references(self::DWS_BILLING_STATEMENT, '明細書')->onDelete('cascade');
            $table->serviceCode();
            $table->integer('unit_score')->comment('単位数');
            $table->integer('count')->comment('回数');
            $table->integer('total_score')->comment('サービス単位数');
            $table->sortOrder();
            // KEYS
            $table->unique(
                [self::DWS_BILLING_STATEMENT . '_id', 'sort_order'],
                self::DWS_BILLING_STATEMENT_ITEM . '_sort_order_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::DWS_BILLING_STATEMENT_ITEM);
        Schema::dropIfExists(self::DWS_BILLING_STATEMENT_CONTRACT);
        Schema::dropIfExists(self::DWS_BILLING_STATEMENT_AGGREGATE);
        Schema::dropIfExists(self::DWS_BILLING_STATEMENT);
        Schema::dropIfExists(self::DWS_GRANTED_SERVICE_CODE);
        Schema::dropIfExists(self::DWS_BILLING_STATEMENT_COPAY_COORDINATION_STATUS);
    }

    /**
     * 上限管理結果票・管理結果
     *
     * @return array
     */
    private function copayCoordinationStatuses(): array
    {
        return [
            [1, '不要'],
            [2, '未作成'],
            [3, '未入力'],
            [4, '入力済'],
            [11, '不要（上限管理なし）'],
            [12, '不要（サービス提供なし）'],
            [21, '未作成'],
            [22, '未入力'],
            [31, '入力済'],
        ];
    }

    /**
     * 障害福祉サービス決定サービスコード
     *
     * @return array
     */
    private function dwsGrantedServiceCodes(): array
    {
        return [
            ['000000', 'なし'],
            ['111000', '居宅介護身体介護'],
            ['112000', '居宅介護家事援助'],
            ['113000', '居宅介護通院介助（身体介護伴う）'],
            ['114000', '居宅介護通院介助（身体介護伴わない）'],
            ['121000', '重度訪問介護（重度障害者等包括支援対象者'],
            ['122000', '重度訪問介護（障害支援区分6該当者）'],
            ['123000', '重度訪問介護（その他）'],
            ['120901', '重度訪問介護（移動加算）'],
            ['141000', '重度包括基本'],
        ];
    }
}
