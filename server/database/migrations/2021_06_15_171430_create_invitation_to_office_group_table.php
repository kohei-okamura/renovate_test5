<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * 招待ー事業所グループ テーブルを追加する.
 */
final class CreateInvitationToOfficeGroupTable extends Migration
{
    private const INVITAION = 'invitation';
    private const OFFICE_GROUP = 'office_group';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::createIntermediate(self::INVITAION, self::OFFICE_GROUP, '招待', '事業所グループ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIntermediate(self::INVITAION, self::OFFICE_GROUP);
    }
}
