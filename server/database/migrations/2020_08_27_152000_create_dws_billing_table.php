<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 障害福祉サービス請求テーブルを追加する.
 */
final class CreateDwsBillingTable extends Migration
{
    private const COPAY_COORDINATION_RESULT = 'copay_coordination_result';
    private const DWS_BILLING_PAYMENT_CATEGORY = 'dws_billing_payment_cate_gory';
    private const DWS_BILLING_STATUS = 'dws_billing_status';

    private const DWS_BILLING = 'dws_billing';
    private const DWS_BILLING_FILE = 'dws_billing_file';
    private const DWS_BILLING_BUNDLE = 'dws_billing_bundle';
    private const DWS_BILLING_INVOICE = 'dws_billing_invoice';
    private const DWS_BILLING_INVOICE_ITEM = 'dws_billing_invoice_item';
    private const DWS_BILLING_SERVICE_DETAIL = 'dws_billing_service_detail';
    private const DWS_BILLING_COPAY_COORDINATION = 'dws_billing_copay_coordination';
    private const DWS_BILLING_COPAY_COORDINATION_ITEM = 'dws_billing_copay_coordination_item';

    private const DWS_CERTIFICATION = 'dws_certification';
    private const OFFICE = 'office';
    private const ORGANIZATION = 'organization';
    private const USER = 'user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();

        Schema::createCatalogue(
            self::DWS_BILLING_PAYMENT_CATEGORY,
            '障害福祉サービス請求：給付種別',
            $this->dwsBillingPaymentCategories()
        );
        Schema::createCatalogue(
            self::COPAY_COORDINATION_RESULT,
            '利用者負担上限額管理結果',
            $this->copayCoordinationResults()
        );
        Schema::createCatalogue(self::DWS_BILLING_STATUS, '障害福祉サービス請求状態', $this->dwsBillingStatuses());
        Schema::create(self::DWS_BILLING, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('障害福祉サービス請求ID');
            $table->references(self::ORGANIZATION, '事業者');
            $table->references(self::OFFICE, '事業所');
            $table->string('office_code', 20)->charset('binary')->comment('事業所番号');
            $table->string('office_name', 200)->comment('事業所名');
            $table->date('transacted_in')->comment('処理対象年月');
            $table->catalogued(self::DWS_BILLING_STATUS, '状態', 'status');
            $table->dateTime('fixed_at')->nullable()->comment('確定日時');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::DWS_BILLING_FILE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('ファイル ID');
            $table->references(self::DWS_BILLING, '障害福祉サービス請求');
            $table->string('name', 100)->comment('ファイル名');
            $table->string('path', 100)->comment('パス');
            $table->string('token', 60)->charset('binary')->unique()->comment('トークン');
            $table->string('mime_type', 20)->comment('MimeType');
            $table->createdAt()->comment('作成日時');
            $table->dateTime('downloaded_at')->nullable()->comment('最終ダウンロード日時');
            $table->sortOrder();
            // KEYS
            $table->unique([self::DWS_BILLING . '_id', 'sort_order']);
        });
        Schema::create(self::DWS_BILLING_BUNDLE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('請求単位 ID');
            $table->references(self::DWS_BILLING, '障害福祉サービス請求');
            $table->date('provided_in')->comment('サービス提供年月');
            $table->string('city_code', 6)->comment('市町村番号');
            $table->string('city_name', 20)->comment('市町村名');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::DWS_BILLING_SERVICE_DETAIL, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('サービス詳細 ID');
            $table->references(self::DWS_BILLING_BUNDLE, '障害福祉サービス請求単位')->onDelete('cascade');
            $table->references(self::USER, '利用者');
            $table->date('provided_on')->comment('サービス提供年月日');
            $table->serviceCode();
            $table->integer('unit_score')->comment('単位数');
            $table->integer('count')->comment('回数');
            $table->integer('total_score')->comment('サービス単位数');
            $table->sortOrder();
            // KEYS
            $table->unique(
                [self::DWS_BILLING_BUNDLE . '_id', 'sort_order'],
                self::DWS_BILLING_SERVICE_DETAIL . '_sort_order_unique'
            );
        });
        Schema::create(self::DWS_BILLING_INVOICE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('請求書 ID');
            $table->references(self::DWS_BILLING_BUNDLE, '障害福祉サービス請求単位');
            $table->integer('claim_amount')->comment('請求金額');
            $table->integer('subtotal_detail_count')->comment('小計：介護給付費等・特別介護給付費等：件数');
            $table->integer('subtotal_score')->comment('小計：介護給付費等・特別介護給付費等：単位数');
            $table->integer('subtotal_fee')->comment('小計：介護給付費等・特別介護給付費等：費用合計');
            $table->integer('subtotal_benefit')->comment('小計：介護給付費等・特別介護給付費等：給付費請求額');
            $table->integer('subtotal_copay')->comment('小計：介護給付費等・特別介護給付費等：利用者負担額');
            $table->integer('subtotal_subsidy')->comment('小計：介護給付費等・特別介護給付費等：自治体助成額');
            $table->integer('high_cost_subtotal_detail_count')->comment('小計：特定障害者特別給付費・高額障害福祉サービス費：件数');
            $table->integer('high_cost_subtotal_fee')->comment('小計：特定障害者特別給付費・高額障害福祉サービス費：費用合計');
            $table->integer('high_cost_subtotal_benefit')->comment('小計：特定障害者特別給付費・高額障害福祉サービス費：給付費請求額');
            $table->integer('total_count')->comment('合計：件数');
            $table->integer('total_score')->comment('合計：単位数');
            $table->integer('total_fee')->comment('合計：費用合計');
            $table->integer('total_benefit')->comment('合計：給付費請求額');
            $table->integer('total_copay')->comment('合計：利用者負担額');
            $table->integer('total_subsidy')->comment('合計：自治体助成額');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::DWS_BILLING_INVOICE_ITEM, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('明細 ID');
            $table->references(self::DWS_BILLING_INVOICE, '障害福祉サービス請求書')->onDelete('cascade');
            $table->catalogued(self::DWS_BILLING_PAYMENT_CATEGORY, '給付種別', 'payment_category');
            $table->string('service_division_code', 2)->comment('サービス種類コード');
            $table->integer('subtotal_count')->comment('件数');
            $table->integer('subtotal_score')->comment('単位数');
            $table->integer('subtotal_fee')->comment('費用合計');
            $table->integer('subtotal_benefit')->comment('給付費請求額');
            $table->integer('subtotal_copay')->comment('利用者負担額')->nullable();
            $table->integer('subtotal_subsidy')->comment('自治体助成額')->nullable();
            $table->sortOrder();
            // KEYS
            $table->unique(
                [self::DWS_BILLING_INVOICE . '_id', 'sort_order'],
                self::DWS_BILLING_INVOICE_ITEM . '_sort_order_unique'
            );
        });
        Schema::create(self::DWS_BILLING_COPAY_COORDINATION, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('利用者負担上限額管理結果票ID');
            $table->references(self::DWS_BILLING, '障害福祉サービス請求');
            $table->references(self::DWS_BILLING_BUNDLE, '障害福祉サービス請求単位');
            $table->references(self::OFFICE, '事業所');
            $table->string('office_code', 20)->charset('binary')->comment('事業所番号');
            $table->string('office_name', 200)->comment('事業所名');
            $table->references(self::USER, '利用者');
            $table->references(self::DWS_CERTIFICATION, '障害福祉サービス受給者証', 'user_dws_certification_id');
            $table->string('user_dws_number', 20)->comment('受給者番号');
            $table->structuredName('user_', '利用者：');
            $table->structuredName('user_child_', '利用者：児童：');
            $table->integer('user_copay_limit')->comment('利用者負担上限月額');
            $table->integer('total_fee')->comment('総費用額');
            $table->integer('total_copay')->comment('利用者負担額');
            $table->integer('total_coordinated_copay')->comment('管理結果後利用者負担額');
            $table->catalogued(self::COPAY_COORDINATION_RESULT, '利用者負担上限額管理結果', 'result');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::DWS_BILLING_COPAY_COORDINATION_ITEM, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('明細 ID');
            $table->references(self::DWS_BILLING_COPAY_COORDINATION, '利用者負担上限額管理結果票')->onDelete('cascade');
            $table->references(self::OFFICE, '事業所');
            $table->string('office_code', 20)->charset('binary')->comment('事業所番号');
            $table->string('office_name', 200)->comment('事業所名');
            $table->integer('subtotal_fee')->comment('総費用額');
            $table->integer('subtotal_copay')->comment('利用者負担額');
            $table->integer('subtotal_coordinated_copay')->comment('管理結果後利用者負担額');
            $table->integer('item_number')->comment('項番');
            $table->sortOrder();
            // KEYS
            $table->unique(
                [self::DWS_BILLING_COPAY_COORDINATION . '_id', 'sort_order'],
                self::DWS_BILLING_COPAY_COORDINATION_ITEM . '_sort_order_unique'
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
        Schema::dropIfExists(self::DWS_BILLING_COPAY_COORDINATION_ITEM);
        Schema::dropIfExists(self::DWS_BILLING_COPAY_COORDINATION);
        Schema::dropIfExists(self::DWS_BILLING_INVOICE_ITEM);
        Schema::dropIfExists(self::DWS_BILLING_INVOICE);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_DETAIL);
        Schema::dropIfExists(self::DWS_BILLING_BUNDLE);
        Schema::dropIfExists(self::DWS_BILLING_FILE);
        Schema::dropIfExists(self::DWS_BILLING);
        Schema::dropIfExists(self::DWS_BILLING_STATUS);
        Schema::dropIfExists(self::COPAY_COORDINATION_RESULT);
        Schema::dropIfExists(self::DWS_BILLING_PAYMENT_CATEGORY);
    }

    /**
     * 障害福祉サービス請求：給付種別の定義一覧.
     *
     * @return array
     */
    private function dwsBillingPaymentCategories(): array
    {
        return [
            [1, '介護給付費・訓練等給付費・地域相談支援給付費・特例介護給付費・特例訓練等給付費'],
            [2, '特定障害者特別給付費・高額障害者福祉サービス費'],
        ];
    }

    /**
     * 上限管理結果票・管理結果
     *
     * @return array
     */
    private function copayCoordinationResults(): array
    {
        return [
            [1, '管理事業所で利用者負担額を充当したため、他事業所の利用者負担は発生しない'],
            [2, '利用者負担額の合計額が、負担上限月額以下のため、調整事務は行わない'],
            [3, '利用者負担額の合計額が、負担上限月額を超過するため、下記のとおり調整した'],
        ];
    }

    /**
     * 障害福祉サービス請求状態.
     *
     * @return array|array[]
     */
    private function dwsBillingStatuses(): array
    {
        return [
            [10, '入力中'],
            [20, '未確定'],
            [30, '確定済'],
        ];
    }
}
