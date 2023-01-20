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
 * 利用者テーブルを追加する.
 */
final class CreateUserTable extends Migration
{
    private $user = 'user';
    private $userAttr = 'user_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->user, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('利用者ID');
            $table->references('organization', '事業者');
            $table->references('bank_account', '銀行口座');
            $table->createdAt();
        });
        Schema::create($this->userAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('利用者属性ID');
            $table->references($this->user, '利用者');
            $table->structuredName();
            $table->sex();
            $table->birthday()->nullable();
            $table->addr();
            $table->location();
            $table->tel();
            $table->fax();
            $table->string('mbs_customer_code', 20)->charset('binary')->comment('MBS顧客番号');
            $table->attr($this->user);
        });
        Schema::createAttrIntermediate($this->user, '利用者');
        Schema::createAttrTriggers($this->user);
        Schema::createKeywordIndexTable(
            $this->user,
            '利用者',
            ['family_name', 'given_name', 'phonetic_family_name', 'phonetic_given_name']
        );
        Schema::createIntermediate($this->userAttr, 'office', '利用者属性', '事業所');
        Schema::createIntermediate($this->userAttr, 'staff', '利用者属性', 'スタッフ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIntermediate($this->userAttr, 'staff');
        Schema::dropIntermediate($this->userAttr, 'office');
        Schema::dropKeywordIndexTable($this->user);
        Schema::dropAttrTriggers($this->user);
        Schema::dropAttrIntermediate($this->user);
        Schema::dropIfExists($this->userAttr);
        Schema::dropIfExists($this->user);
    }
}
