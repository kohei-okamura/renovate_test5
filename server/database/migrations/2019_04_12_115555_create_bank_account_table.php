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
 * 銀行口座マスタテーブルを追加する.
 */
final class CreateBankAccountTable extends Migration
{
    private $bankAccountType = 'bank_account_type';
    private $bankAccount = 'bank_account';
    private $bankAccountAttr = 'bank_account_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->bankAccountType, '銀行口座種別', $this->bankAccountType());
        Schema::create($this->bankAccount, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('銀行口座ID');
            $table->createdAt();
        });
        Schema::create($this->bankAccountAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('銀行口座属性ID');
            $table->references($this->bankAccount, '銀行口座');
            $table->string('bank_name', 100)->comment('銀行名');
            $table->string('bank_code', 4)->charset('binary')->comment('銀行コード');
            $table->string('bank_branch_name', 100)->comment('銀行支店名');
            $table->string('bank_branch_code', 3)->charset('binary')->comment('銀行支店コード');
            $table->catalogued($this->bankAccountType, '銀行口座種別');
            $table->string('bank_account_number', 10)->charset('binary')->comment('銀行口座番号');
            $table->string('bank_account_holder', 100)->comment('銀行口座名義');
            $table->integer('version')->comment('バージョン');
            $table->updatedAt();
            // KEYS
            $table->unique(['bank_account_id', 'version'], 'bank_account_attr_version_unique');
        });
        Schema::createAttrIntermediate($this->bankAccount, '銀行口座');
        Schema::createAttrTriggers($this->bankAccount);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropAttrTriggers($this->bankAccount);
        Schema::dropAttrIntermediate($this->bankAccount);
        Schema::dropIfExists($this->bankAccountAttr);
        Schema::dropIfExists($this->bankAccount);
        Schema::dropIfExists($this->bankAccountType);
    }

    /**
     * 銀行口座種別の定義一覧.
     *
     * @return array
     */
    protected function bankAccountType(): array
    {
        return [
            [0, '不明'],
            [1, '普通預金'],
            [2, '当座預金'],
            [3, '定期預金'],
        ];
    }
}
