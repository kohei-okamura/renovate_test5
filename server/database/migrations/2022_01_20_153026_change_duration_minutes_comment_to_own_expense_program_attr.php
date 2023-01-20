<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 自費サービス情報属性テーブルの duration_minutes のコメントを単位時間数に変更.
 */
class ChangeDurationMinutesCommentToOwnExpenseProgramAttr extends Migration
{
    private const OwnExpenseProgramAttr = 'own_expense_program_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::OwnExpenseProgramAttr, function (Blueprint $table): void {
            $table->integer('duration_minutes')->comment('単位時間数')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::OwnExpenseProgramAttr, function (Blueprint $table): void {
            $table->integer('duration_minutes')->comment('提供時間数')->change();
        });
    }
}
