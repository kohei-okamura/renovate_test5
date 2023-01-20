<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 事業者別設定テーブルを追加する.
 */
final class CreateOrganizationSettingTable extends Migration
{
    private const ORGANIZATION_SETTING = 'organization_setting';
    private const ORGANIZATION = 'organization';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create(self::ORGANIZATION_SETTING, function (Blueprint $table): void {
            $table->id()->comment('事業者別設定ID');
            $table->references(self::ORGANIZATION, '事業者');
            $table->json('setting')->comment('設定');
            $table->createdAt();
            $table->updatedAt();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::ORGANIZATION_SETTING);
    }
}
