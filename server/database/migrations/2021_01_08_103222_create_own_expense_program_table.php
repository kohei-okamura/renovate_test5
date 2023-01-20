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
 * 自費サービス情報テーブルを追加する.
 */
class CreateOwnExpenseProgramTable extends Migration
{
    private string $organization = 'organization';
    private string $office = 'office';
    private string $ownExpenseProgram = 'own_expense_program';
    private string $ownExpenseProgramAttr = 'own_expense_program_attr';
    private string $feeTaxType = 'fee_tax_type';
    private string $feeTaxCategory = 'fee_tax_category';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::createCatalogue($this->feeTaxType, '課税区分', $this->feeTaxTypes());
        Schema::createCatalogue($this->feeTaxCategory, '税率区分', $this->feeTaxCategories());
        Schema::create($this->ownExpenseProgram, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('自費サービス情報ID');
            $table->references($this->organization, '事業者');
            $table->unsignedBigInteger("{$this->office}_id")->nullable()->comment('事業所ID');
            $table->createdAt();
            // CONSTRAINTS
            $table->foreign("{$this->office}_id")->references('id')->on($this->office)->onDelete('cascade');
        });
        Schema::create($this->ownExpenseProgramAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('自費サービス情報属性ID');
            $table->references($this->ownExpenseProgram, '自費サービス情報');
            $table->string('name', 200)->comment('名称');
            $table->integer('duration_minutes')->comment('提供時間数');
            $table->integer('fee_tax_excluded')->comment('税抜');
            $table->integer('fee_tax_included')->comment('税込');
            $table->catalogued('fee_tax_type', '課税区分');
            $table->catalogued('fee_tax_category', '税率区分');
            $table->text('note')->comment('備考');
            $table->attr($this->ownExpenseProgram);
        });
        Schema::createAttrIntermediate($this->ownExpenseProgram, '自費サービス情報');
        Schema::createAttrTriggers($this->ownExpenseProgram);
        Schema::createKeywordIndexTable(
            $this->ownExpenseProgram,
            '自費サービス情報',
            ['name']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropKeywordIndexTable($this->ownExpenseProgram);
        Schema::dropAttrTriggers($this->ownExpenseProgram);
        Schema::dropAttrIntermediate($this->ownExpenseProgram);
        Schema::dropKeywordIndexTable($this->ownExpenseProgram);
        Schema::dropIfExists($this->ownExpenseProgramAttr);
        Schema::dropIfExists($this->ownExpenseProgram);
        Schema::dropIfExists($this->feeTaxType);
        Schema::dropIfExists($this->feeTaxCategory);
    }

    /**
     * 課税区分の定義一覧.
     *
     * @return array
     */
    private function feeTaxTypes(): array
    {
        return [
            [1, '税抜'],
            [2, '税込'],
            [3, '非課税'],
        ];
    }

    /**
     * 税率区分の定義一覧.
     *
     * @return array
     */
    private function feeTaxCategories(): array
    {
        return [
            [0, '該当なし'],
            [1, '消費税'],
            [2, '消費税（軽減税率）'],
        ];
    }
}
