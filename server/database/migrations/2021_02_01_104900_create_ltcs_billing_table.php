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
 * 介護保険サービス：請求関連のテーブルを作成する.
 */
final class CreateLtcsBillingTable extends Migration
{
    private const LTCS_BILLING_STATUS = 'ltcs_billing_status';
    private const LTCS_BILLING_SERVICE_DETAIL_DISPOSITION = 'ltcs_billing_service_detail_disposition';
    private const LTCS_BUILDING_SUBTRACTION = 'ltcs_building_subtraction';
    private const LTCS_CARE_PLAN_AUTHOR_TYPE = 'ltcs_care_plan_author_type';
    private const LTCS_EXPIRED_REASON = 'ltcs_expired_reason';

    private const LTCS_BILLING = 'ltcs_billing';
    private const LTCS_BILLING_FILE = 'ltcs_billing_file';
    private const LTCS_BILLING_BUNDLE = 'ltcs_billing_bundle';
    private const LTCS_BILLING_SERVICE_DETAIL = 'ltcs_billing_service_detail';
    private const LTCS_BILLING_INVOICE = 'ltcs_billing_invoice';
    private const LTCS_BILLING_STATEMENT = 'ltcs_billing_statement';
    private const LTCS_BILLING_STATEMENT_SUBSIDY = 'ltcs_billing_statement_subsidy';
    private const LTCS_BILLING_STATEMENT_ITEM = 'ltcs_billing_statement_item';
    private const LTCS_BILLING_STATEMENT_ITEM_SUBSIDY = 'ltcs_billing_statement_item_subsidy';
    private const LTCS_BILLING_STATEMENT_AGGREGATE = 'ltcs_billing_statement_aggregate';
    private const LTCS_BILLING_STATEMENT_AGGREGATE_SUBSIDY = 'ltcs_billing_statement_aggregate_subsidy';

    private const DEFRAYER_CATEGORY = 'defrayer_category';
    private const LTCS_INS_CARD = 'ltcs_ins_card';
    private const LTCS_LEVEL = 'ltcs_level';
    private const ORGANIZATION = 'organization';
    private const OFFICE = 'office';
    private const USER = 'user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();

        Schema::createCatalogue(self::LTCS_BILLING_STATUS, '介護保険サービス：請求：状態');
        Schema::createCatalogue(self::LTCS_BILLING_SERVICE_DETAIL_DISPOSITION, '介護保険サービス：請求：サービス詳細区分');
        Schema::createCatalogue(self::LTCS_BUILDING_SUBTRACTION, '介護保険サービス：同一建物減算区分');
        Schema::createCatalogue(self::LTCS_CARE_PLAN_AUTHOR_TYPE, '介護保険サービス：請求：居宅サービス計画作成区分');
        Schema::createCatalogue(self::LTCS_EXPIRED_REASON, '介護保険サービス：明細書：中止理由');

        Schema::seedCatalogue(self::LTCS_BILLING_STATUS, $this->status());
        Schema::seedCatalogue(self::LTCS_BILLING_SERVICE_DETAIL_DISPOSITION, $this->dispositions());
        Schema::seedCatalogue(self::LTCS_BUILDING_SUBTRACTION, $this->subtractions());
        Schema::seedCatalogue(self::LTCS_CARE_PLAN_AUTHOR_TYPE, $this->carePlanAuthorTypes());
        Schema::seedCatalogue(self::LTCS_EXPIRED_REASON, $this->expiredReasons());

        Schema::create(self::LTCS_BILLING, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('請求 ID');
            $table->references(self::ORGANIZATION, '事業者');
            $table->references(self::OFFICE, '事業所');
            $table->string('office_code', 20)->comment('事業所：事業所番号');
            $table->string('office_name', 200)->comment('事業所：事業所名');
            $table->string('office_abbr', 200)->comment('事業所：略称');
            $table->addr('office_', '事業所：所在地：');
            $table->string('office_tel', 13)->comment('事業所：電話番号');
            $table->date('transacted_in')->comment('処理対象年月');
            $table->catalogued(self::LTCS_BILLING_STATUS, '状態', 'status');
            $table->fixedAt();
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::LTCS_BILLING_FILE, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('ファイル ID');
            $table->references(self::LTCS_BILLING, '請求 ID', 'billing_id')->cascadeOnDelete();
            $table->string('name', 200)->comment('ファイル名');
            $table->string('path', 100)->comment('パス');
            $table->string('token', 60)->charset('binary')->comment('トークン');
            $table->string('mime_type', 20)->comment('MimeType');
            $table->dateTime('created_at')->comment('作成日時');
            $table->dateTime('downloaded_at')->nullable()->comment('最終ダウンロード日時');
            $table->sortOrder();
            // KEYS
            $table->unique(['billing_id', 'token']);
            $table->unique(['billing_id', 'sort_order']);
        });

        Schema::create(self::LTCS_BILLING_BUNDLE, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('請求単位 ID');
            $table->references(self::LTCS_BILLING, '請求 ID', 'billing_id')->cascadeOnDelete();
            $table->date('provided_in')->comment('サービス提供年月');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::LTCS_BILLING_SERVICE_DETAIL, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('サービス詳細 ID');
            $table->references(self::LTCS_BILLING_BUNDLE, '請求単位 ID', 'bundle_id')->cascadeOnDelete();
            $table->references(self::USER, '利用者');
            $table->catalogued(self::LTCS_BILLING_SERVICE_DETAIL_DISPOSITION, '区分', 'disposition');
            $table->date('provided_on')->comment('サービス提供年月日');
            $table->serviceCode();
            $table->catalogued(self::LTCS_BUILDING_SUBTRACTION, '同一建物減算区分', 'building_subtraction');
            $table->integer('duration_minutes')->comment('所要時間');
            $table->integer('unit_score')->comment('単位数');
            $table->integer('count')->comment('回数');
            $table->integer('total_score')->comment('サービス単位数');
            $table->sortOrder();
            // KEYS
            $table->unique(['bundle_id', 'sort_order']);
        });

        Schema::create(self::LTCS_BILLING_INVOICE, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('請求書 ID');
            $table->references(self::LTCS_BILLING, '請求 ID', 'billing_id');
            $table->references(self::LTCS_BILLING_BUNDLE, '請求単位 ID', 'bundle_id')->cascadeOnDelete();
            $table->boolean('is_subsidy')->comment('公費フラグ');
            $table->foreignId('defrayer_category')
                ->type('integer')
                ->nullable()
                ->comment('公費制度（法別番号）')
                ->constrained(self::DEFRAYER_CATEGORY);
            $table->integer('statement_count')->comment('サービス費用：件数');
            $table->integer('total_score')->comment('サービス費用：単位数');
            $table->integer('total_fee')->comment('サービス費用：費用合計');
            $table->integer('insurance_amount')->comment('サービス費用：保険請求額');
            $table->integer('subsidy_amount')->comment('サービス費用：公費請求額');
            $table->integer('copay_amount')->comment('サービス費用：利用者負担');
            $table->createdAt();
            $table->updatedAt();
        });

        Schema::create(self::LTCS_BILLING_STATEMENT, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('明細書 ID');
            $table->references(self::LTCS_BILLING, '請求 ID', 'billing_id');
            $table->references(self::LTCS_BILLING_BUNDLE, '請求単位 ID', 'bundle_id')->cascadeOnDelete();
            $table->string('insurer_number', 6)->comment('保険者番号');
            $table->string('insurer_name', 100)->comment('保険者名');
            $table->references(self::USER, '被保険者：利用者 ID');
            $table->references(self::LTCS_INS_CARD, '被保険者：介護保険被保険者証 ID', 'user_ltcs_ins_card_id');
            $table->string('user_ins_number', 10)->comment('被保険者：被保険者証番号');
            $table->structuredName('user_', '被保険者：');
            $table->sex('user_', '被保険者：');
            $table->birthday('user_', '被保険者：');
            $table->catalogued(self::LTCS_LEVEL, '被保険者：要介護状態区分', 'user_ltcs_level');
            $table->date('user_activated_on')->comment('被保険者：認定の有効期間（開始）');
            $table->date('user_deactivated_on')->comment('被保険者：認定の有効期間（終了）');
            $table->catalogued(self::LTCS_CARE_PLAN_AUTHOR_TYPE, '居宅サービス計画：作成区分', 'care_plan_author_type');
            $table->references(self::OFFICE, '居宅サービス計画：事業所 ID', 'care_plan_author_office_id');
            $table->string('care_plan_author_code', 20)->comment('居宅サービス計画：事業所番号');
            $table->string('care_plan_author_name', 100)->comment('居宅サービス計画：事業所名');
            $table->date('agreed_on')->comment('開始年月日')->nullable();
            $table->date('expired_on')->comment('中止年月日')->nullable();
            $table->catalogued(self::LTCS_EXPIRED_REASON, '中止理由', 'expired_reason');
            $table->integer('insurance_benefit_rate')->comment('保険請求内容：給付率');
            $table->integer('insurance_total_score')->comment('保険請求内容：サービス単位数');
            $table->integer('insurance_claim_amount')->comment('保険請求内容：請求額');
            $table->integer('insurance_copay_amount')->comment('保険請求内容：利用者負担額');
            $table->catalogued(self::LTCS_BILLING_STATUS, '状態', 'status');
            $table->fixedAt();
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create(self::LTCS_BILLING_STATEMENT_SUBSIDY, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('公費請求内容 ID');
            $table->references(self::LTCS_BILLING_STATEMENT, '明細書 ID', 'statement_id')->cascadeOnDelete();
            $table->foreignId('defrayer_category')
                ->type('integer')
                ->nullable()
                ->comment('公費制度（法別番号）')
                ->constrained(self::DEFRAYER_CATEGORY);
            $table->string('defrayer_number', 8)->comment('負担者番号');
            $table->string('recipient_number', 7)->comment('受給者番号');
            $table->integer('benefit_rate')->nullable()->comment('給付率');
            $table->integer('total_score')->comment('サービス単位数');
            $table->integer('claim_amount')->comment('請求額');
            $table->integer('copay_amount')->comment('利用者負担額');
            $table->sortOrder();
            // KEYS
            $table->unique(['statement_id', 'sort_order']);
        });

        Schema::create(self::LTCS_BILLING_STATEMENT_ITEM, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('明細 ID');
            $table->references(self::LTCS_BILLING_STATEMENT, '明細書 ID', 'statement_id')->cascadeOnDelete();
            $table->serviceCode();
            $table->integer('unit_score')->comment('単位数');
            $table->integer('count')->comment('日数・回数');
            $table->integer('total_score')->comment('サービス単位数');
            $table->string('note', 100)->comment('摘要');
            $table->sortOrder();
            // KEYS
            $table->unique(['statement_id', 'sort_order']);
        });
        Schema::create(self::LTCS_BILLING_STATEMENT_ITEM_SUBSIDY, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('公費 ID');
            $table->references(self::LTCS_BILLING_STATEMENT_ITEM, '明細 ID', 'statement_item_id')->cascadeOnDelete();
            $table->integer('count')->comment('日数・回数');
            $table->integer('total_score')->comment('サービス単位数');
            $table->sortOrder();
            // KEYS
            $table->unique(
                ['statement_item_id', 'sort_order'],
                'ltcs_billing_statement_item_subsidy_sort_order_unique'
            );
        });

        Schema::create(self::LTCS_BILLING_STATEMENT_AGGREGATE, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('集計 ID');
            $table->references(self::LTCS_BILLING_STATEMENT, '明細書 ID', 'statement_id')->cascadeOnDelete();
            $table->string('service_division_code', 2)->comment('サービス種類コード');
            $table->integer('service_days')->comment('サービス実日数');
            $table->integer('planned_score')->comment('計画単位数');
            $table->integer('managed_score')->comment('限度額管理対象単位数');
            $table->integer('unmanaged_score')->comment('限度額管理対象外単位数');
            $table->integer('insurance_total_score')->comment('保険集計結果：単位数合計');
            $table->integer('insurance_unit_cost')->comment('保険集計結果：単位数単価');
            $table->integer('insurance_claim_amount')->comment('保険集計結果：請求額');
            $table->integer('insurance_copay_amount')->comment('保険集計結果：利用者負担額');
            $table->sortOrder();
            // KEYS
            $table->unique(['statement_id', 'sort_order']);
        });
        Schema::create(self::LTCS_BILLING_STATEMENT_AGGREGATE_SUBSIDY, function (Blueprint $table) {
            $table->id()->comment('集計 ID');
            $table
                ->references(self::LTCS_BILLING_STATEMENT_AGGREGATE, '明細書 ID', 'statement_aggregate_id')
                ->cascadeOnDelete();
            $table->integer('total_score')->comment('サービス単位数');
            $table->integer('claim_amount')->comment('請求額');
            $table->integer('copay_amount')->comment('利用者負担額');
            $table->sortOrder();
            // KEYS
            $table->unique(
                ['statement_aggregate_id', 'sort_order'],
                'ltcs_billing_statement_aggregate_subsidy_sort_order_unique'
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
        Schema::dropIfExists(self::LTCS_BILLING_STATEMENT_AGGREGATE_SUBSIDY);
        Schema::dropIfExists(self::LTCS_BILLING_STATEMENT_AGGREGATE);

        Schema::dropIfExists(self::LTCS_BILLING_STATEMENT_ITEM_SUBSIDY);
        Schema::dropIfExists(self::LTCS_BILLING_STATEMENT_ITEM);

        Schema::dropIfExists(self::LTCS_BILLING_STATEMENT_SUBSIDY);
        Schema::dropIfExists(self::LTCS_BILLING_STATEMENT);

        Schema::dropIfExists(self::LTCS_BILLING_INVOICE);

        Schema::dropIfExists(self::LTCS_BILLING_SERVICE_DETAIL);
        Schema::dropIfExists(self::LTCS_BILLING_BUNDLE);

        Schema::dropIfExists(self::LTCS_BILLING_FILE);
        Schema::dropIfExists(self::LTCS_BILLING);

        Schema::dropIfExists(self::LTCS_BILLING_STATUS);
        Schema::dropIfExists(self::LTCS_BILLING_SERVICE_DETAIL_DISPOSITION);
        Schema::dropIfExists(self::LTCS_BUILDING_SUBTRACTION);
        Schema::dropIfExists(self::LTCS_CARE_PLAN_AUTHOR_TYPE);
        Schema::dropIfExists(self::LTCS_EXPIRED_REASON);
    }

    /**
     * 介護保険サービス：請求：状態の定義.
     *
     * @return array|array[]
     */
    private function status(): array
    {
        return [
            [10, '入力中'],
            [20, '未確定'],
            [30, '確定済'],
            [99, '無効'],
        ];
    }

    /**
     * 介護保険サービス：請求：サービス詳細区分の定義.
     *
     * @return array|array[]
     */
    private function dispositions(): array
    {
        return [
            [1, '予定'],
            [2, '実績'],
        ];
    }

    /**
     * 介護保険サービス：同一建物減算区分の定義.
     *
     * @return array|array[]
     */
    private function subtractions(): array
    {
        return [
            [0, 'なし'],
            [1, '同一建物減算1'],
            [2, '同一建物減算2'],
        ];
    }

    /**
     * 介護保険サービス：請求：居宅サービス計画作成区分の定義.
     *
     * @return array|array[]
     */
    private function carePlanAuthorTypes(): array
    {
        return [
            [1, '居宅介護支援事業所作成'],
            [2, '自己作成'],
            [3, '介護予防支援事業所・地域包括支援センター作成'],
        ];
    }

    /**
     * 介護保険サービス：明細書：中止理由の定義.
     *
     * @return array|array[]
     */
    private function expiredReasons(): array
    {
        return [
            [0, '未設定'],
            [1, '非該当'],
            [3, '医療機関入院'],
            [4, '死亡'],
            [5, 'その他'],
            [6, '介護老人福祉施設入所'],
            [7, '介護老人保健施設入所'],
            [8, '介護療養型医療施設入所'],
            [9, '介護医療院入所'],
        ];
    }
}
