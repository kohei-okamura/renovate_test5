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
 * スタッフメールアドレス検証テーブルを追加する.
 */
final class CreateStaffEmailVerificationTable extends Migration
{
    private $tables = [
        'staff_email_verification' => 'スタッフメールアドレス検証',
        'staff_password_reset' => 'スタッフパスワード再設定',
        'staff_email_update' => 'スタッフメールアドレス変更',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        foreach ($this->tables as $table => $comment) {
            Schema::create($table, function (Blueprint $table) use ($comment): void {
                // COLUMNS
                $table->id()->comment("{$comment}ID");
                $table->references('staff', 'スタッフ');
                $table->email();
                $table->string('token', 60)->charset('binary')->unique()->comment('トークン');
                $table->expiredAt();
                $table->createdAt();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach ($this->tables as $table => $comment) {
            Schema::dropIfExists($table);
        }
    }
}
