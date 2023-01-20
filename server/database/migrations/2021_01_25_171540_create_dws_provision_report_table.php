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
 * 障害福祉サービス：予実テーブルを追加する.
 */
final class CreateDwsProvisionReportTable extends Migration
{
    private string $dwsProvisionReport = 'dws_provision_report';
    private string $dwsProvisionReportItemPlan = 'dws_provision_report_item_plan';
    private string $dwsProvisionReportItemResult = 'dws_provision_report_item_result';
    private string $dwsProvisionReportStatus = 'dws_provision_report_status';
    private string $dwsProjectServiceCategory = 'dws_project_service_category';
    private string $serviceOption = 'service_option';
    private string $dwsPlan = 'dws_plan';
    private string $dwsResult = 'dws_result';
    private string $user = 'user';
    private string $office = 'office';
    private string $contract = 'contract';
    private string $sortOrder = 'sort_order';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->dwsProvisionReportStatus, '障害福祉サービス：予実：状態', $this->dwsProvisionReportStatuses());
        Schema::createCatalogue($this->dwsProjectServiceCategory, '障害福祉サービス：計画：サービス区分', $this->dwsProjectServiceCategories());
        Schema::create($this->dwsProvisionReport, function (Blueprint $table): void {
            $table->id()->comment('障害福祉サービス：予実ID');
            $table->references($this->user, '利用者');
            $table->references($this->office, '事業所');
            $table->references($this->contract, '契約');
            $table->date('provided_in')->comment('サービス提供年月');
            $table->catalogued($this->dwsProvisionReportStatus, '状態', 'status');
            $table->dateTime('fixed_at')->nullable()->comment('確定日時');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create($this->dwsProvisionReportItemPlan, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('障害福祉サービス：予実：要素：予定');
            $table->references($this->dwsProvisionReport, '障害福祉サービス：予実')->onDelete('cascade');
            $table->sortOrder();
            $table->datetime('schedule_start')->comment('開始日時');
            $table->datetime('schedule_end')->comment('終了日時');
            $table->date('schedule_date')->comment('勤務日');
            $table->catalogued($this->dwsProjectServiceCategory, 'サービス区分', 'category');
            $table->tinyInteger('headcount')->comment('提供人数');
            $table->string('note')->comment('備考');
            // KEYS
            $table->unique(["{$this->dwsProvisionReport}_id", $this->sortOrder], "{$this->dwsProvisionReportItemPlan}_{$this->sortOrder}_unique");
        });
        Schema::create($this->dwsProvisionReportItemResult, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('障害福祉サービス：予実：要素：実績');
            $table->references($this->dwsProvisionReport, '障害福祉サービス：予実')->onDelete('cascade');
            $table->sortOrder();
            $table->datetime('schedule_start')->comment('開始日時');
            $table->datetime('schedule_end')->comment('終了日時');
            $table->date('schedule_date')->comment('勤務日');
            $table->catalogued($this->dwsProjectServiceCategory, 'サービス区分', 'category');
            $table->tinyInteger('headcount')->comment('提供人数');
            $table->string('note')->comment('備考');
            // KEYS
            $table->unique(["{$this->dwsProvisionReport}_id", $this->sortOrder], "{$this->dwsProvisionReportItemResult}_{$this->sortOrder}_unique");
        });
        Schema::createCatalogueIntermediate($this->dwsProvisionReportItemPlan, $this->serviceOption, '障害福祉サービス：予実：要素：予定', 'サービスオプション');
        Schema::createCatalogueIntermediate($this->dwsProvisionReportItemResult, $this->serviceOption, '障害福祉サービス：予実：要素：実績', 'サービスオプション');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->dwsResult);
        Schema::dropIfExists($this->dwsPlan);
        Schema::dropCatalogueIntermediate($this->dwsProvisionReportItemPlan, $this->serviceOption);
        Schema::dropCatalogueIntermediate($this->dwsProvisionReportItemResult, $this->serviceOption);
        Schema::dropIfExists($this->dwsProvisionReportItemPlan);
        Schema::dropIfExists($this->dwsProvisionReportItemResult);
        Schema::dropIfExists($this->dwsProvisionReport);
        Schema::dropIfExists($this->dwsProjectServiceCategory);
        Schema::dropIfExists($this->dwsProvisionReportStatus);
    }

    /**
     * 障害福祉サービス：予実：状態の定義一覧.
     *
     * @return array
     */
    private function dwsProvisionReportStatuses(): array
    {
        return [
            [1, '未作成'],
            [2, '作成中'],
            [3, '確定済'],
        ];
    }

    /**
     * 障害福祉サービス：予実：サービス区分の定義一覧.
     *
     * @return array
     */
    private function dwsProjectServiceCategories(): array
    {
        return [
            [11, '居宅：身体介護'],
            [12, '居宅：家事援助'],
            [13, '居宅：通院等介助（身体を伴う）'],
            [14, '居宅：通院等介助（身体を伴わない）'],
            [21, '重度訪問介護'],
            [91, '自費'],
        ];
    }
}
