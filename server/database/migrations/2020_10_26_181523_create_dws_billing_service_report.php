<?php

declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * サービス提供実績記録票テーブルを追加する.
 */
final class CreateDwsBillingServiceReport extends Migration
{
    private const DWS_BILLING_SERVICE_REPORT_FORMAT = 'dws_billing_service_report_format';
    private const DWS_BILLING_SERVICE_REPORT_PROVIDER_TYPE = 'dws_billing_service_report_provider_type';
    private const DWS_BILLING_SERVICE_REPORT_SITUATION = 'dws_billing_service_report_situation';
    private const DWS_BILLING_SERVICE_REPORT_AGGREGATE_GROUP = 'dws_billing_service_report_aggregate_group';
    private const DWS_BILLING_SERVICE_REPORT_AGGREGATE_CATEGORY = 'dws_billing_service_report_aggregate_category';

    private const DWS_BILLING_SERVICE_REPORT = 'dws_billing_service_report';
    private const DWS_BILLING_SERVICE_REPORT_ITEM = 'dws_billing_service_report_item';
    private const DWS_BILLING_SERVICE_REPORT_PLAN = 'dws_billing_service_report_plan';
    private const DWS_BILLING_SERVICE_REPORT_RESULT = 'dws_billing_service_report_result';

    private const DWS_BILLING = 'dws_billing';
    private const DWS_BILLING_BUNDLE = 'dws_billing_bundle';
    private const DWS_BILLING_STATUS = 'dws_billing_status';
    private const DWS_CERTIFICATION = 'dws_certification';
    private const DWS_GRANTED_SERVICE_CODE = 'dws_granted_service_code';
    private const USER = 'user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        Schema::createStringCatalogue(
            self::DWS_BILLING_SERVICE_REPORT_FORMAT,
            'サービス提供実績記録票：様式種別番号',
            $this->formats()
        );
        Schema::createCatalogue(
            self::DWS_BILLING_SERVICE_REPORT_PROVIDER_TYPE,
            'サービス提供実績記録票：ヘルパー資格',
            $this->providerTypes()
        );
        Schema::createCatalogue(
            self::DWS_BILLING_SERVICE_REPORT_SITUATION,
            'サービス提供実績記録票：サービス提供の状況',
            $this->situations()
        );
        Schema::createCatalogue(
            self::DWS_BILLING_SERVICE_REPORT_AGGREGATE_GROUP,
            'サービス提供実績記録票：合計区分グループ',
            $this->aggregateGroups()
        );
        Schema::createCatalogue(
            self::DWS_BILLING_SERVICE_REPORT_AGGREGATE_CATEGORY,
            'サービス提供実績記録票：合計区分カテゴリー',
            $this->aggregateCategories()
        );
        Schema::create(self::DWS_BILLING_SERVICE_REPORT, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('サービス提供実績記録票 ID');
            $table->references(self::DWS_BILLING, '障害福祉サービス請求');
            $table->references(self::DWS_BILLING_BUNDLE, '障害福祉サービス請求単位');
            $table->references(self::USER, '利用者');
            $table->references(self::DWS_CERTIFICATION, '障害福祉サービス受給者証', 'user_dws_certification_id');
            $table->string('user_dws_number', 20)->comment('受給者番号');
            $table->structuredName('user_');
            $table->structuredName('user_child_', '児童：');
            $table->integer('user_copay_limit')->comment('利用者負担上限月額');
            $table->stringCatalogued(
                self::DWS_BILLING_SERVICE_REPORT_FORMAT,
                'サービス提供実績記録票：様式種別番号',
                'format'
            );
            $table->integer('emergency_count')->comment('提供実績の合計2：緊急時対応加算（回）');
            $table->integer('first_time_count')->comment('提供実績の合計2：初回加算（回）');
            $table->integer('welfare_specialist_cooperation_count')->comment('提供実績の合計2：福祉専門職員等連携加算（回）');
            $table->integer('behavioral_disorder_support_cooperation_count')->comment('提供実績の合計2：行動障害支援連携加算（回）');
            $table->integer('moving_care_support_count')->comment('提供実績の合計3：移動介護緊急時支援加算（回）');
            $table->catalogued(self::DWS_BILLING_STATUS, '状態', 'status');
            $table->dateTime('fixed_at')->comment('確定日時')->nullable();
            $table->createdAt()->comment('登録日時');
            $table->updatedAt()->comment('更新日時');
        });
        Schema::create(self::DWS_BILLING_SERVICE_REPORT_ITEM, function (Blueprint $table) {
            // COLUMNS
            $table->id()->comment('明細 ID');
            $table->references(self::DWS_BILLING_SERVICE_REPORT, 'サービス提供実績記録票')->onDelete('cascade');
            $table->integer('serial_number')->comment('提供通番');
            $table->date('provided_on')->comment('日付');
            $table->stringCatalogued(self::DWS_GRANTED_SERVICE_CODE, 'サービス内容', 'service_type');
            $table->catalogued(self::DWS_BILLING_SERVICE_REPORT_PROVIDER_TYPE, 'ヘルパー資格', 'provider_type');
            $table->catalogued(self::DWS_BILLING_SERVICE_REPORT_SITUATION, 'サービス提供の状況', 'situation');
            $table->date('plan_period_start')->comment('予定：開始時間')->nullable();
            $table->date('plan_period_end')->comment('予定：終了時間')->nullable();
            $table->integer('plan_service_duration_hours')->comment('予定：算定時間数')->nullable();
            $table->integer('plan_moving_duration_hours')->comment('予定：移動')->nullable();
            $table->date('result_period_start')->comment('実績：開始時間')->nullable();
            $table->date('result_period_end')->comment('実績：終了時間')->nullable();
            $table->integer('result_service_duration_hours')->comment('実績：算定時間数')->nullable();
            $table->integer('result_moving_duration_hours')->comment('実績：移動')->nullable();
            $table->integer('service_count')->comment('サービス提供回数');
            $table->integer('headcount')->comment('派遣人数');
            $table->boolean('is_coaching')->comment('同行支援');
            $table->boolean('is_first_time')->comment('初回加算');
            $table->boolean('is_emergency')->comment('緊急時対応加算');
            $table->boolean('is_welfare_specialist_cooperation')->comment('福祉専門職員等連携加算');
            $table->boolean('is_behavioral_disorder_support_cooperation')->comment('行動障害支援連携加算');
            $table->boolean('is_moving_care_support')->comment('移動介護緊急時支援加算');
            $table->boolean('is_driving')->comment('運転フラグ');
            $table->boolean('is_previous_month')->comment('前月からの継続サービス');
            $table->string('note')->comment('備考');
            $table->sortOrder();
            // KEYS
            $table->unique(
                [self::DWS_BILLING_SERVICE_REPORT . '_id', 'sort_order'],
                self::DWS_BILLING_SERVICE_REPORT_ITEM . '_sort_order_unique'
            );
        });
        $durations = [
            self::DWS_BILLING_SERVICE_REPORT_PLAN,
            self::DWS_BILLING_SERVICE_REPORT_RESULT,
        ];
        foreach ($durations as $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName): void {
                // COLUMNS
                $table->id()->comment('集計 ID');
                $table->references(self::DWS_BILLING_SERVICE_REPORT, 'サービス提供実績記録票')->onDelete('cascade');
                $table->catalogued(
                    self::DWS_BILLING_SERVICE_REPORT_AGGREGATE_GROUP,
                    'サービス提供実績記録票：合計区分グループ',
                    'group'
                );
                $table->catalogued(
                    self::DWS_BILLING_SERVICE_REPORT_AGGREGATE_CATEGORY,
                    'サービス提供実績記録票：合計区分カテゴリー',
                    'category'
                );
                $table->integer('value')->comment('合計時間');
                $table->sortOrder();
                // KEYS
                $table->unique(
                    [self::DWS_BILLING_SERVICE_REPORT . '_id', 'sort_order'],
                    "{$tableName}_sort_order_unique"
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT_RESULT);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT_PLAN);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT_ITEM);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT_FORMAT);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT_PROVIDER_TYPE);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT_SITUATION);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT_AGGREGATE_GROUP);
        Schema::dropIfExists(self::DWS_BILLING_SERVICE_REPORT_AGGREGATE_CATEGORY);
    }

    /**
     * サービス提供実績記録票：様式種別番号
     *
     * @return array
     */
    private function formats(): array
    {
        return [
            ['0101', '様式1（居宅介護サービス提供実績記録票情報）'],
            ['0301', '様式3-1（重度訪問介護サービス提供実績記録票）'],
        ];
    }

    /**
     * サービス提供実績記録票：ヘルパー資格.
     *
     * @return array
     */
    private function providerTypes(): array
    {
        return [
            [0, '未設定'],
            [11, '初任者等'],
            [12, '基礎等'],
            [13, '重訪'],
        ];
    }

    /**
     * サービス提供の状況.
     *
     * @return array
     */
    private function situations(): array
    {
        return [
            [0, '未設定'],
            [1, '入院'],
            [2, '入院（長期）'],
        ];
    }

    /**
     * サービス提供実績記録票：合計区分グループ.
     *
     * @return array
     */
    private function aggregateGroups(): array
    {
        return [
            [11, '居宅介護：合計1「身体介護」'],
            [12, '居宅介護：合計2「通院等介助（身体を伴う）」'],
            [13, '居宅介護：合計3「家事援助」'],
            [14, '居宅介護：合計4「通院等介助（身体を伴わない）」'],
            [15, '居宅介護：合計5「通院等乗降介助」'],
            [21, '重度訪問介護'],
            [22, '重度訪問介護：移動介護分'],
        ];
    }

    /**
     * サービス提供実績記録票：合計区分カテゴリー.
     *
     * @return array
     */
    private function aggregateCategories(): array
    {
        return [
            [1, '内訳 100%'],
            [2, '内訳 90%'],
            [3, '内訳 70%'],
            [4, '内訳 重訪'],
            [5, '合計 算定時間数計'],
        ];
    }
}
