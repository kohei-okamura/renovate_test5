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
 * 招待テーブルを追加する.
 */
class CreateInvitationTable extends Migration
{
    private string $invitation = 'invitation';
    private string $staff = 'staff';
    private string $role = 'role';
    private string $office = 'office';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create($this->invitation, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('招待ID');
            $table->unsignedBigInteger("{$this->staff}_id")->nullable()->comment('スタッフID');
            $table->email();
            $table->string('token', 60)->charset('binary')->unique()->comment('トークン');
            $table->expiredAt();
            $table->createdAt();
            // CONSTRAINTS
            $table->foreign("{$this->staff}_id")->references('id')->on($this->staff);
        });
        Schema::createIntermediate($this->invitation, $this->role, '招待', 'ロール');
        Schema::createIntermediate($this->invitation, $this->office, '招待', '事業所');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIntermediate($this->invitation, $this->office);
        Schema::dropIntermediate($this->invitation, $this->role);
        Schema::dropIfExists($this->invitation);
    }
}
