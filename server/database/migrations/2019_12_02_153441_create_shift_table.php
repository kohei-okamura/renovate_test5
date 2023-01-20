<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 勤務シフトテーブルを作成する.
 */
class CreateShiftTable extends Migration
{
    private string $activity = 'activity';
    private string $shiftAssignee = 'shift_assignee';
    private string $shiftDuration = 'shift_duration';
    private string $organization = 'organization';
    private string $office = 'office';
    private string $contract = 'contract';
    private string $shift = 'shift';
    private string $shiftImport = 'shift_import';
    private string $schedule = 'schedule';
    private string $serviceOption = 'service_option';
    private string $staff = 'staff';
    private string $task = 'task';
    private string $user = 'user';
    private string $sortOrder = 'sort_order';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->activity, '勤務内容', $this->activities());
        Schema::createCatalogue($this->serviceOption, 'サービスオプション（勤務シフト・勤務実績）', $this->serviceOptions());
        Schema::createCatalogue($this->task, '勤務区分', $this->tasks());
        Schema::create($this->shiftImport, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('勤務シフトインポートID');
            $table->references($this->organization, '事業者');
            $table->references($this->staff, 'インポートスタッフID');
            $table->createdAt();
        });
        Schema::create($this->shift, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('勤務シフトID');
            $table->references($this->organization, '事業者');
            $table->references($this->office, '事業所');
            $table->bigInteger($this->contract . '_id')->unsigned()->nullable()->comment('契約ID');
            $table->foreign($this->contract . '_id')->references('id')->on($this->contract);
            $table->bigInteger($this->shiftImport . '_id')->unsigned()->nullable()->comment('勤務シフトインポートID');
            $table->foreign($this->shiftImport . '_id')->references('id')->on($this->shiftImport);
            $table->bigInteger($this->user . '_id')->unsigned()->nullable()->comment('利用者ID');
            $table->foreign($this->user . '_id')->references('id')->on($this->user);
            $table->bigInteger('assigner_id')->unsigned()->comment('管理スタッフ');
            $table->foreign('assigner_id')->references('id')->on($this->staff);
            $table->catalogued($this->task, '勤務区分');
            $table->serviceCode();
            $table->datetime('schedule_start')->comment('開始日時');
            $table->datetime('schedule_end')->comment('終了日時');
            $table->date('schedule_date')->comment('勤務日');
            $table->tinyInteger('headcount')->unsigned()->comment('頭数');
            $table->text('note')->comment('備考');
            $table->boolean('is_confirmed')->comment('確定フラグ');
            $table->boolean('is_canceled')->comment('キャンセルフラグ');
            $table->string('reason')->comment('キャンセル理由');
            $table->updatedAt();
            $table->createdAt();

            $table->index('schedule_date');
            $table->index('schedule_start');
        });
        Schema::createCatalogueIntermediate($this->shift, $this->serviceOption, '勤務シフト', 'サービスオプション（勤務シフト・勤務実績）');
        Schema::create($this->shiftDuration, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('勤務シフト勤務時間ID');
            $table->references($this->shift, '勤務シフトID')->onDelete('cascade');
            $table->catalogued($this->activity, '勤務内容');
            $table->integer('duration')->comment('所要時間（分）');
        });
        Schema::create($this->shiftAssignee, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('勤務シフト担当スタッフID');
            $table->references($this->shift, '勤務シフト')->onDelete('cascade');
            $table->sortOrder();
            $table->bigInteger($this->staff . '_id')->unsigned()->nullable()->comment('勤務シフト担当スタッフID');
            $table->foreign($this->staff . '_id')->references('id')->on($this->staff);
            $table->boolean('is_training')->comment('研修フラグ');
            // KEYS
            $table->unique(["{$this->shift}_id", $this->sortOrder], "{$this->shiftAssignee}_{$this->sortOrder}_unique");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropCatalogueIntermediate($this->shift, $this->serviceOption);
        Schema::dropIfExists($this->shiftAssignee);
        Schema::dropIfExists($this->shiftDuration);
        Schema::dropIfExists($this->shift);
        Schema::dropIfExists($this->shiftImport);
        Schema::dropIfExists($this->schedule);
        Schema::dropIfExists($this->task);
        Schema::dropIfExists($this->serviceOption);
        Schema::dropIfExists($this->activity);
    }

    /**
     * 勤務区分の定義一覧.
     *
     * @return array
     */
    private function tasks(): array
    {
        return [
            [101101, '居宅：身体'],
            [101102, '居宅：家事'],
            [101103, '居宅：通院・身体'],
            [101104, '居宅：通院'],
            [101201, '重度訪問介護'],
            [201101, '介保：身体'],
            [201102, '介保：生活'],
            [201103, '介保：身体・生活'],
            [111101, '移動支援・身体'],
            [111102, '移動支援'],
            [211101, '総合事業'],
            [701101, '自費'],
            [801101, '実地研修'],
            [801102, 'アセスメント'],
            [899999, 'その他往訪'],
            [901101, '事務'],
            [901102, '営業'],
            [901103, 'ミーティング'],
            [988888, 'その他'],
        ];
    }

    /**
     * サービスオプション（勤務シフト・勤務実績）の定義一覧
     *
     * @return array
     */
    private function serviceOptions(): array
    {
        return [
            [100001, '通知'],
            [100002, '単発'],
            [200001, '2人目'],
            [300001, '初回'],
            [300002, '緊急時対応'],
            [300003, '喀痰吸引'],
            [301101, '福祉専門職員等連携'],
            [301102, '初計'],
            [301103, '基礎研修課程修了者等'],
            [301104, '重研'],
            [301105, '同一建物減算'],
            [301106, '同一建物減算（大規模）'],
            [301201, '重訪行動障害支援連携'],
            [301202, '入院'],
            [301203, '入院（長期）'],
            [301204, '熟練同行'],
        ];
    }

    /**
     * 勤務内容 の定義一覧
     *
     * @return array
     */
    private function activities(): array
    {
        return [
            [101101, '居宅：身体'],
            [101102, '居宅：家事'],
            [101103, '居宅：通院・身体あり'],
            [101104, '居宅：通院・身体なし'],
            [101201, '重訪'],
            [101202, '重訪（移動加算）'],
            [201101, '介保：身体'],
            [201102, '介保：生活'],
            [111101, '総合事業'],
            [111102, '移動支援・身体あり'],
            [211101, '移動支援・身体なし'],
            [711101, '自費'],
            [811101, '実地研修'],
            [811102, 'アセスメント'],
            [899999, 'その他往訪'],
            [911101, '事務'],
            [911102, '営業'],
            [911103, 'ミーティング'],
            [988888, 'その他'],
            [999999, '休憩'],
        ];
    }
}
