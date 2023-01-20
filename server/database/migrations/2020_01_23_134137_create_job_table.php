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
 * ジョブテーブルを作成する.
 */
class CreateJobTable extends Migration
{
    private $jobStatus = 'job_status';
    private $organization = 'organization';
    private $staff = 'staff';
    private $job = 'job';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->jobStatus, 'ジョブ状態', $this->jobStatus());
        Schema::create($this->job, function (Blueprint $table) {
            // COLUMN
            $table->bigIncrements('id')->comment('ジョブID');
            $table->references($this->organization, '事業者');
            $table->bigInteger($this->staff . '_id')->nullable()->unsigned()->comment('スタッフID');
            $table->foreign($this->staff . '_id')->references('id')->on($this->staff);
            $table->json('data')->nullable()->comment('データ');
            $table->catalogued($this->jobStatus, 'ジョブ状態', 'status');
            $table->string('token')->unique()->comment('トークン');
            $table->createdAt();
            $table->updatedAt();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->job);
        Schema::dropIfExists($this->jobStatus);
    }

    /**
     * ジョブ状態の定義一覧.
     *
     * @return array
     */
    private function jobStatus(): array
    {
        return [
            [1, 'Waiting'],
            [2, 'InProgress'],
            [3, 'Success'],
            [9, 'Failure'],
        ];
    }
}
