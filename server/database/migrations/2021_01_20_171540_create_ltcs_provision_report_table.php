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
 * 介護保険サービス：予実テーブルを追加する.
 */
final class CreateLtcsProvisionReportTable extends Migration
{
    private string $ltcsProvisionReport = 'ltcs_provision_report';
    private string $ltcsProvisionReportEntry = 'ltcs_provision_report_entry';
    private string $ltcsProjectAmount = 'ltcs_project_amount';
    private string $ltcsProvisionReportStatus = 'ltcs_provision_report_status';
    private string $timeframe = 'timeframe';
    private string $ltcsProjectServiceCategory = 'ltcs_project_service_category';
    private string $ltcsProjectAmountCategory = 'ltcs_project_amount_category';
    private string $ownExpenseProgram = 'own_expense_program';
    private string $serviceOption = 'service_option';
    private string $ltcsProvisionReportEntryPlan = 'ltcs_provision_report_entry_plan';
    private string $ltcsProvisionReportEntryResult = 'ltcs_provision_report_entry_result';
    private string $user = 'user';
    private string $office = 'office';
    private string $contract = 'contract';
    private string $homeVisitLongTermCareSpecifiedOfficeAddition = 'home_visit_long_term_care_specified_office_addition';
    private string $ltcsTreatmentImprovementAddition = 'ltcs_treatment_improvement_addition';
    private string $ltcsSpecifiedTreatmentImprovementAddition = 'ltcs_specified_treatment_improvement_addition';
    private string $ltcsOfficeLocationAddition = 'ltcs_office_location_addition';
    private string $sortOrder = 'sort_order';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue(
            $this->ltcsProvisionReportStatus,
            '介護保険サービス：予実：状態',
            $this->ltcsProvisionReportStatuses()
        );
        Schema::createCatalogue(
            $this->ltcsProjectServiceCategory,
            '介護保険サービス：計画：サービス区分',
            $this->ltcsProjectServiceCategories()
        );
        Schema::createCatalogue(
            $this->ltcsProjectAmountCategory,
            '介護保険サービス：計画：サービス提供量区分',
            $this->ltcsProjectAmountCategories()
        );
        Schema::create($this->ltcsProvisionReport, function (Blueprint $table): void {
            $table->id()->comment('介護保険サービス：予実ID');
            $table->references($this->user, '利用者');
            $table->references($this->office, '事業所');
            $table->references($this->contract, '契約');
            $table->date('provided_in')->comment('サービス提供年月');
            $table->catalogued(
                $this->homeVisitLongTermCareSpecifiedOfficeAddition,
                '特定事業所加算',
                'specified_office_addition'
            );
            $table->catalogued($this->ltcsTreatmentImprovementAddition, '処遇改善加算', 'treatment_improvement_addition');
            $table->catalogued(
                $this->ltcsSpecifiedTreatmentImprovementAddition,
                '特定処遇改善加算',
                'specified_treatment_improvement_addition'
            );
            $table->catalogued($this->ltcsOfficeLocationAddition, '地域加算', 'location_addition');
            $table->catalogued($this->ltcsProvisionReportStatus, '状態', 'status');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create($this->ltcsProvisionReportEntry, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('介護保険サービス：予実：サービス情報ID');
            $table->references($this->ltcsProvisionReport, '介護保険サービス：予実')->onDelete('cascade');
            $table->unsignedBigInteger("{$this->ownExpenseProgram}_id")->nullable()->comment('自費サービス情報ID');
            $table->sortOrder();
            $table->time('slot_start')->comment('時間帯 開始時刻');
            $table->time('slot_end')->comment('時間帯 終了時刻');
            $table->catalogued($this->timeframe, '算定時間帯', 'timeframe');
            $table->catalogued($this->ltcsProjectServiceCategory, 'サービス区分', 'category');
            $table->integer('headcount')->comment('提供人数');
            $table->serviceCode();
            $table->string('note')->comment('備考');
            // KEYS
            $table->unique(
                ["{$this->ltcsProvisionReport}_id", $this->sortOrder],
                "{$this->ltcsProvisionReportEntry}_{$this->sortOrder}_unique"
            );
            // CONSTRAINTS
            $table->foreign("{$this->ownExpenseProgram}_id")->references('id')->on($this->ownExpenseProgram)->onDelete('cascade');
        });
        Schema::createCatalogueIntermediate(
            $this->ltcsProvisionReportEntry,
            $this->serviceOption,
            '介護保険サービス：予実：サービス情報',
            'サービスオプション'
        );
        Schema::create($this->ltcsProvisionReportEntryPlan, function (Blueprint $table): void {
            $table->id()->comment('介護保険サービス：予実：予定年月日');
            $table->references($this->ltcsProvisionReportEntry, '介護保険サービス：予実：サービス情報')->onDelete('cascade');
            $table->sortOrder();
            $table->date('date')->comment('年月日');
            // KEYS
            $table->unique(
                ["{$this->ltcsProvisionReportEntry}_id", $this->sortOrder],
                "{$this->ltcsProvisionReportEntryPlan}_{$this->sortOrder}_unique"
            );
        });
        Schema::create($this->ltcsProvisionReportEntryResult, function (Blueprint $table): void {
            $table->id()->comment('介護保険サービス：予実：実績年月日');
            $table->references($this->ltcsProvisionReportEntry, '介護保険サービス：予実：サービス情報')->onDelete('cascade');
            $table->sortOrder();
            $table->date('date')->comment('年月日');
            // KEYS
            $table->unique(
                ["{$this->ltcsProvisionReportEntry}_id", $this->sortOrder],
                "{$this->ltcsProvisionReportEntryResult}_{$this->sortOrder}_unique"
            );
        });
        Schema::create($this->ltcsProjectAmount, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('介護保険サービス：計画：サービス提供量ID');
            $table->references($this->ltcsProvisionReportEntry, '介護保険サービス：予実：サービス情報')->onDelete('cascade');
            $table->sortOrder();
            $table->catalogued($this->ltcsProjectAmountCategory, 'サービス区分', 'category');
            $table->integer('amount')->comment('サービス時間');
            // KEYS
            $table->unique(
                ["{$this->ltcsProvisionReportEntry}_id", $this->sortOrder],
                "{$this->ltcsProjectAmount}_{$this->sortOrder}_unique"
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
        Schema::dropIfExists($this->ltcsProjectAmount);
        Schema::dropIfExists($this->ltcsProvisionReportEntryResult);
        Schema::dropIfExists($this->ltcsProvisionReportEntryPlan);
        Schema::dropCatalogueIntermediate($this->ltcsProvisionReportEntry, $this->serviceOption);
        Schema::dropIfExists($this->ltcsProvisionReportEntry);
        Schema::dropIfExists($this->ltcsProvisionReport);
        Schema::dropIfExists($this->ltcsProjectAmountCategory);
        Schema::dropIfExists($this->ltcsProjectServiceCategory);
        Schema::dropIfExists($this->ltcsProvisionReportStatus);
    }

    /**
     * 介護保険サービス：予実：状態の定義一覧.
     *
     * @return array
     */
    private function ltcsProvisionReportStatuses(): array
    {
        return [
            [1, '未作成'],
            [2, '作成中'],
            [3, '確定済'],
        ];
    }

    /**
     * 介護保険サービス：計画：サービス区分の定義一覧.
     *
     * @return array
     */
    private function ltcsProjectServiceCategories(): array
    {
        return [
            [11, '身体介護'],
            [12, '生活援助'],
            [13, '身体・生活'],
            [91, '自費'],
        ];
    }

    /**
     * 介護保険サービス：計画：サービス提供量区分の定義一覧.
     *
     * @return array
     */
    private function ltcsProjectAmountCategories(): array
    {
        return [
            [11, '身体介護'],
            [12, '生活援助'],
            [91, '自費'],
        ];
    }
}
