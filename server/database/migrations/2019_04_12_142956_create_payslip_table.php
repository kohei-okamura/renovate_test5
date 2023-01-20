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
 * 給与明細テーブルを追加する.
 */
final class CreatePayslipTable extends Migration
{
    private $payslip = 'payslip';
    private $payslipItem = 'payslip_item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->payslip, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('給与明細ID');
            $table->references('organization', '事業者');
            $table->references('office', '事業所');
            $table->references('staff', 'スタッフ');
            $table->date('pay_on')->comment('支給年月日');
            $table->string('title', 100)->comment('タイトル');
            $table->string('employee_number', 20)->charset('binary')->comment('社員番号');
            $table->string('employee_family_name', 100)->comment('社員姓');
            $table->string('employee_given_name', 100)->comment('社員名');
            $table->integer('total_taxable_payment')->comment('課税支給合計');
            $table->integer('total_nontaxable_payment')->comment('非課税支給合計');
            $table->integer('total_payment')->comment('支給合計');
            $table->integer('total_deduction')->comment('控除合計');
            $table->integer('net_payment')->comment('差引支給額');
            $table->integer('transfer_amount')->comment('振込額');
            $table->enum('income_tax_category', ['甲', '乙', '丙'])->comment('税額表');
            $table->createdAt();
            $table->updatedAt();
        });
        Schema::create($this->payslipItem, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('給与明細項目ID');
            $table->references($this->payslip, '給与明細');
            $table->enum('item_type', ['payment', 'deduction', 'attendance', 'other'])->comment('項目種別');
            $table->string('title', 20)->comment('タイトル');
            $table->string('content', 20)->comment('内容（金額・数量）');
            $table->enum('content_type', ['text', 'int', 'decimal'])->comment('型');
            $table->boolean('is_hidden')->comment('非表示フラグ');
            $table->sortOrder();
            // KEYS
            $table->unique(['payslip_id', 'item_type', 'sort_order'], 'payslip_payment_sort_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->payslipItem);
        Schema::dropIfExists($this->payslip);
    }
}
