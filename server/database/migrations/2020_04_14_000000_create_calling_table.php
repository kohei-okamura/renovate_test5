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
 * 出勤確認テーブルを作成する.
 */
class CreateCallingTable extends Migration
{
    private string $calling = 'calling';
    private string $callingLog = 'calling_log';
    private string $callingResponse = 'calling_response';
    private string $callingType = 'calling_type';
    private string $shift = 'shift';
    private string $staff = 'staff';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->callingType, '送信タイプ', $this->callingType());
        Schema::create($this->calling, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('出勤確認ID');
            $table->references($this->staff, 'スタッフ');
            $table->string('token', 60)->charset('binary')->unique()->comment('トークン');
            $table->expiredAt();
            $table->createdAt();

            $table->index('expired_at');
        });
        Schema::createIntermediate($this->calling, $this->shift, '出勤確認', '勤務シフト');
        Schema::create($this->callingResponse, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('出勤確認応答ID');
            $table->references($this->calling, '出勤確認')->onDelete('cascade');
            $table->createdAt();
        });
        Schema::create($this->callingLog, function (Blueprint $table): void {
            // COLUMN
            $table->id()->comment('出勤確認送信履歴ID');
            $table->references($this->calling, '出勤確認')->onDelete('cascade');
            $table->catalogued($this->callingType, '送信タイプ');
            $table->boolean('is_succeeded')->comment('送信成功フラグ');
            $table->createdAt();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIntermediate($this->calling, $this->shift);
        Schema::dropIfExists($this->callingLog);
        Schema::dropIfExists($this->callingResponse);
        Schema::dropIfExists($this->calling);
        Schema::dropIfExists($this->callingType);
    }

    /**
     * 送信タイプの定義一覧.
     *
     * @return array
     */
    private function callingType(): array
    {
        return [
            [1, 'メール'],
            [2, 'SMS'],
            [3, '電話呼び出し'],
            [4, '管理スタッフ電話呼び出し'],
        ];
    }
}
