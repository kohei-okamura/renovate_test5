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
 * 計画テーブルを作成する.
 */
class CreateProjectTable extends Migration
{
    private const TIMEFRAME = 'timeframe';
    private string $dwsProjectServiceMenu = 'dws_project_service_menu';
    private string $ltcsProjectServiceMenu = 'ltcs_project_service_menu';
    private string $dwsProjectServiceCategory = 'dws_project_service_category';
    private string $ltcsProjectServiceCategory = 'ltcs_project_service_category';
    private string $organization = 'organization';
    private string $contract = 'contract';
    private string $office = 'office';
    private string $user = 'user';
    private string $staff = 'staff';
    private string $dwsProject = 'dws_project';
    private string $dwsProjectAttr = 'dws_project_attr';
    private string $ltcsProject = 'ltcs_project';
    private string $ltcsProjectAttr = 'ltcs_project_attr';
    private string $dwsProjectProgram = 'dws_project_program';
    private string $ltcsProjectProgram = 'ltcs_project_program';
    private string $dwsProjectContent = 'dws_project_content';
    private string $ltcsProjectContent = 'ltcs_project_content';
    private string $ltcsProjectProgramAmount = 'ltcs_project_program_amount';
    private string $ltcsProjectAmountCategory = 'ltcs_project_amount_category';
    private string $recurrence = 'recurrence';
    private string $ownExpenseProgram = 'own_expense_program';
    private string $serviceOption = 'service_option';

    private string $sortOrder = 'sort_order';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->dwsProjectServiceMenu, function (Blueprint $table): void {
            $table->id()->comment('障害福祉サービス：計画：サービス内容ID');
            $table->catalogued($this->dwsProjectServiceCategory, 'サービス区分', 'category');
            $table->string('name')->comment('名称');
            $table->string('display_name')->comment('表示名');
            $table->sortOrder();
            $table->createdAt();
        });
        Schema::create($this->ltcsProjectServiceMenu, function (Blueprint $table): void {
            $table->id()->comment('介護保険サービス：計画：サービス内容ID');
            $table->catalogued($this->ltcsProjectServiceCategory, 'サービス区分', 'category');
            $table->string('name')->comment('名称');
            $table->string('display_name')->comment('表示名');
            $table->sortOrder();
            $table->createdAt();
        });
        Schema::createCatalogue($this->recurrence, '繰り返し周期', $this->recurrences());
        Schema::create($this->dwsProject, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('障害福祉サービス：計画ID');
            $table->references($this->organization, '事業者');
            $table->createdAt();
        });
        Schema::create($this->dwsProjectAttr, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('障害福祉サービス：計画属性ID');
            $table->references($this->dwsProject, '障害福祉サービス：計画');
            $table->references($this->contract, '契約');
            $table->references($this->office, '事業所');
            $table->references($this->user, '利用者');
            $table->references($this->staff, '作成者');
            $table->date('written_on')->comment('作成日');
            $table->date('effectivated_on')->comment('適用日');
            $table->string('request_from_user')->comment('ご本人の希望');
            $table->string('request_from_family')->comment('ご家族の希望');
            $table->string('objective')->comment('援助目標');
            $table->attr($this->dwsProject);
        });
        Schema::createAttrIntermediate($this->dwsProject, '障害福祉サービス：計画');
        Schema::createAttrTriggers($this->dwsProject);
        Schema::create($this->dwsProjectProgram, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('障害福祉サービス：計画：週間サービス計画ID');
            $table->references($this->dwsProjectAttr, '障害福祉サービス：計画属性')->onDelete('cascade');
            $table->unsignedBigInteger("{$this->ownExpenseProgram}_id")->nullable()->comment('自費サービス情報ID');
            $table->sortOrder();
            $table->smallInteger('summary_index')->comment('週間サービス計画番号');
            $table->catalogued($this->dwsProjectServiceCategory, 'サービス区分', 'category');
            $table->catalogued($this->recurrence, '繰り返し周期');
            $table->boolean('mon')->comment('サービス実施曜日 月曜');
            $table->boolean('tue')->comment('サービス実施曜日 火曜');
            $table->boolean('wed')->comment('サービス実施曜日 水曜');
            $table->boolean('thu')->comment('サービス実施曜日 木曜');
            $table->boolean('fri')->comment('サービス実施曜日 金曜');
            $table->boolean('sat')->comment('サービス実施曜日 土曜');
            $table->boolean('sun')->comment('サービス実施曜日 日曜');
            $table->time('slot_start')->comment('時間帯 開始時刻');
            $table->time('slot_end')->comment('時間帯 終了時刻');
            $table->integer('headcount')->comment('提供人数');
            // KEYS
            $table->unique(["{$this->dwsProjectAttr}_id", $this->sortOrder], "{$this->dwsProjectProgram}_{$this->sortOrder}_unique");
            // CONSTRAINTS
            $table->foreign("{$this->ownExpenseProgram}_id")->references('id')->on($this->ownExpenseProgram);
        });
        Schema::createCatalogueIntermediate($this->dwsProjectProgram, $this->serviceOption, '障害福祉サービス：計画：週間サービス計画', 'サービスオプション');
        Schema::create($this->dwsProjectContent, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('障害福祉サービス：計画：サービス詳細ID');
            $table->references($this->dwsProjectProgram, '障害福祉サービス：計画：週間サービス計画')->onDelete('cascade');
            $table->references($this->dwsProjectServiceMenu, 'サービス内容');
            $table->sortOrder();
            $table->integer('duration')->nullable()->comment('所要時間');
            $table->string('content')->comment('サービスの具体的内容');
            $table->string('memo')->comment('留意事項');
            // KEYS
            $table->unique(["{$this->dwsProjectProgram}_id", $this->sortOrder], "{$this->dwsProjectContent}_{$this->sortOrder}_unique");
        });
        Schema::create($this->ltcsProject, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('介護保険サービス：計画ID');
            $table->references($this->organization, '事業者');
            $table->createdAt();
        });
        Schema::create($this->ltcsProjectAttr, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('介護保険サービス：計画属性ID');
            $table->references($this->ltcsProject, '介護保険サービス：計画');
            $table->references($this->contract, '契約');
            $table->references($this->office, '事業所');
            $table->references($this->user, '利用者');
            $table->references($this->staff, '作成者');
            $table->date('written_on')->comment('作成日');
            $table->date('effectivated_on')->comment('適用日');
            $table->string('request_from_user')->comment('ご本人の希望');
            $table->string('request_from_family')->comment('ご家族の希望');
            $table->string('problem')->comment('解決すべき課題');
            $table->date('long_term_objective_term_start')->comment('長期目標 期間 開始日');
            $table->date('long_term_objective_term_end')->comment('長期目標 期間 終了日');
            $table->string('long_term_objective_text')->comment('長期目標 目標');
            $table->date('short_term_objective_term_start')->comment('短期目標 期間 開始日');
            $table->date('short_term_objective_term_end')->comment('短期目標 期間 終了日');
            $table->string('short_term_objective_text')->comment('短期目標 目標');
            $table->attr($this->ltcsProject);
        });
        Schema::createAttrIntermediate($this->ltcsProject, '介護保険サービス：計画');
        Schema::createAttrTriggers($this->ltcsProject);
        Schema::create($this->ltcsProjectProgram, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('介護保険サービス：計画：週間サービス計画ID');
            $table->references($this->ltcsProjectAttr, '介護保険サービス：計画属性')->onDelete('cascade');
            $table->unsignedBigInteger("{$this->ownExpenseProgram}_id")->nullable()->comment('自費サービス情報ID');
            $table->sortOrder();
            $table->smallInteger('program_index')->comment('週間サービス計画番号');
            $table->catalogued($this->ltcsProjectServiceCategory, 'サービス区分', 'category');
            $table->catalogued($this->recurrence, '繰り返し周期');
            $table->boolean('mon')->comment('サービス実施曜日 月曜');
            $table->boolean('tue')->comment('サービス実施曜日 火曜');
            $table->boolean('wed')->comment('サービス実施曜日 水曜');
            $table->boolean('thu')->comment('サービス実施曜日 木曜');
            $table->boolean('fri')->comment('サービス実施曜日 金曜');
            $table->boolean('sat')->comment('サービス実施曜日 土曜');
            $table->boolean('sun')->comment('サービス実施曜日 日曜');
            $table->time('slot_start')->comment('時間帯 開始時刻');
            $table->time('slot_end')->comment('時間帯 終了時刻');
            $table->catalogued(self::TIMEFRAME, '算定時間帯');
            $table->integer('headcount')->comment('提供人数');
            $table->string('service_code', 6)->comment('サービスコード');
            // KEYS
            $table->unique(["{$this->ltcsProjectAttr}_id", $this->sortOrder], "{$this->ltcsProjectProgram}_{$this->sortOrder}_unique");
            // CONSTRAINTS
            $table->foreign("{$this->ownExpenseProgram}_id")->references('id')->on($this->ownExpenseProgram);
        });
        Schema::create($this->ltcsProjectProgramAmount, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('介護保険サービス：計画：週間サービス計画：サービス提供量ID');
            $table->references($this->ltcsProjectProgram, '介護保険サービス：計画：週間サービス計画')->onDelete('cascade');
            $table->sortOrder();
            $table->catalogued($this->ltcsProjectAmountCategory, 'サービス区分', 'category');
            $table->integer('amount')->comment('サービス時間');
            // KEYS
            $table->unique(["{$this->ltcsProjectProgram}_id", $this->sortOrder], "{$this->ltcsProjectProgramAmount}_{$this->sortOrder}_unique");
        });
        Schema::createCatalogueIntermediate($this->ltcsProjectProgram, $this->serviceOption, '介護保険サービス：計画：週間サービス計画', 'サービスオプション');
        Schema::create($this->ltcsProjectContent, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('介護保険サービス：計画：サービス詳細ID');
            $table->references($this->ltcsProjectProgram, '介護保険サービス：計画：週間サービス計画')->onDelete('cascade');
            $table->references($this->ltcsProjectServiceMenu, 'サービス内容');
            $table->sortOrder();
            $table->integer('duration')->nullable()->comment('所要時間');
            $table->string('content')->comment('サービスの具体的内容');
            $table->string('memo')->comment('留意事項');
            // KEYS
            $table->unique(["{$this->ltcsProjectProgram}_id", $this->sortOrder], "{$this->ltcsProjectContent}_{$this->sortOrder}_unique");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->ltcsProjectContent);
        Schema::dropCatalogueIntermediate($this->ltcsProjectProgram, $this->serviceOption);
        Schema::dropIfExists($this->ltcsProjectProgramAmount);
        Schema::dropIfExists($this->ltcsProjectProgram);
        Schema::dropAttrTriggers($this->ltcsProject);
        Schema::dropAttrIntermediate($this->ltcsProject);
        Schema::dropIfExists($this->ltcsProjectAttr);
        Schema::dropIfExists($this->ltcsProject);
        Schema::dropIfExists($this->dwsProjectContent);
        Schema::dropCatalogueIntermediate($this->dwsProjectProgram, $this->serviceOption);
        Schema::dropIfExists($this->dwsProjectProgram);
        Schema::dropAttrTriggers($this->dwsProject);
        Schema::dropAttrIntermediate($this->dwsProject);
        Schema::dropIfExists($this->dwsProjectAttr);
        Schema::dropIfExists($this->dwsProject);
        Schema::dropIfExists($this->recurrence);
        Schema::dropIfExists($this->ltcsProjectServiceMenu);
        Schema::dropIfExists($this->dwsProjectServiceMenu);
    }

    /**
     * 繰り返し周期の定義一覧.
     *
     * @return array
     */
    private function recurrences(): array
    {
        return [
            [11, '毎週'],
            [12, '奇数週'],
            [13, '偶数週'],
            [21, '第1週'],
            [22, '第2週'],
            [23, '第3週'],
            [24, '第4週'],
            [25, '最終週'],
        ];
    }
}
