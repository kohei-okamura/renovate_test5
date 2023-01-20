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
 * 勤務実績テーブルを作成する.
 */
class CreateAttendanceTable extends Migration
{
    private string $activity = 'activity';
    private string $attendanceAssignee = 'attendance_assignee';
    private string $attendanceDuration = 'attendance_duration';
    private string $organization = 'organization';
    private string $office = 'office';
    private string $contract = 'contract';
    private string $attendance = 'attendance';
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
        Schema::create($this->attendance, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('勤務実績ID');
            $table->references($this->organization, '事業者');
            $table->references($this->office, '事業所');
            $table->bigInteger($this->contract . '_id')->unsigned()->nullable()->comment('契約ID');
            $table->foreign($this->contract . '_id')->references('id')->on($this->contract);
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
        });
        Schema::createCatalogueIntermediate($this->attendance, $this->serviceOption, '勤務実績', 'サービスオプション（予定・勤務実績）');
        Schema::create($this->attendanceDuration, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('勤務実績勤務時間ID');
            $table->references($this->attendance, '勤務実績ID')->onDelete('cascade');
            $table->catalogued($this->activity, '勤務内容');
            $table->integer('duration')->comment('所要時間（分）');
        });
        Schema::create($this->attendanceAssignee, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('勤務実績担当スタッフID');
            $table->references($this->attendance, '勤務実績')->onDelete('cascade');
            $table->sortOrder();
            $table->bigInteger($this->staff . '_id')->unsigned()->nullable()->comment('勤務実績担当スタッフID');
            $table->foreign($this->staff . '_id')->references('id')->on($this->staff);
            $table->boolean('is_training')->comment('研修フラグ');
            // KEYS
            $table->unique(
                ["{$this->attendance}_id", $this->sortOrder],
                "{$this->attendanceAssignee}_{$this->sortOrder}_unique"
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
        Schema::dropCatalogueIntermediate($this->attendance, $this->serviceOption);
        Schema::dropIfExists($this->attendanceAssignee);
        Schema::dropIfExists($this->attendanceDuration);
        Schema::dropIfExists($this->attendance);
        Schema::dropIfExists($this->schedule);
    }
}
