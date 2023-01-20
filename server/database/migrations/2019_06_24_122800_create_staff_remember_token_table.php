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
 * スタッフリメンバートークンテーブルを追加する.
 */
final class CreateStaffRememberTokenTable extends Migration
{
    private $staff = 'staff';
    private $staffRememberToken = 'staff_remember_token';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->staffRememberToken, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('スタッフリメンバートークンID');
            $table->references($this->staff, 'スタッフ');
            $table->string('token', 60)->charset('binary')->unique()->comment('トークン');
            $table->expiredAt();
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
        Schema::dropIfExists($this->staffRememberToken);
    }
}
