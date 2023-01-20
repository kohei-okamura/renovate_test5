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
 * スタッフマスタテーブルを追加する.
 */
final class CreateStaffTable extends Migration
{
    private string $bankAccount = 'bank_account';
    private string $certification = 'certification';
    private string $office = 'office';
    private string $officeGroup = 'office_group';
    private string $organization = 'organization';
    private string $role = 'role';
    private string $staff = 'staff';
    private string $staffAttr = 'staff_attr';
    private string $staffStatus = 'staff_status';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->staff, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('スタッフID');
            $table->references($this->organization, '事業者');
            $table->references($this->bankAccount, '銀行口座');
            $table->createdAt();
        });
        Schema::createCatalogue($this->staffStatus, '状態', $this->staffStatus());
        Schema::create($this->staffAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('スタッフ属性ID');
            $table->references($this->staff, 'スタッフ');
            $table->string('employee_number', 20)->charset('binary')->comment('社員番号');
            $table->structuredName();
            $table->sex();
            $table->birthday();
            $table->addr();
            $table->location();
            $table->tel();
            $table->fax();
            $table->email();
            $table->password();
            $table->boolean('is_verified')->comment('メールアドレス検証済みフラグ');
            $table->catalogued($this->staffStatus, '状態', 'status');
            $table->attr($this->staff);
            // KEYS
            $table->index(['staff_id', 'is_enabled', 'is_verified', 'email'], 'staff_attr_email_index');
            $table->index(['staff_id', 'is_enabled', 'family_name', 'given_name'], 'staff_name_index');
            $table->index(
                ['staff_id', 'is_enabled', 'phonetic_family_name', 'phonetic_given_name'],
                'staff_phonetic_name_index'
            );
        });
        Schema::createAttrIntermediate($this->staff, 'スタッフ');
        Schema::createAttrTriggers($this->staff);
        Schema::createKeywordIndexTable(
            $this->staff,
            'スタッフ',
            ['family_name', 'given_name', 'phonetic_family_name', 'phonetic_given_name']
        );
        Schema::createIntermediate($this->staffAttr, $this->bankAccount, 'スタッフ属性', '銀行口座');
        Schema::createCatalogueIntermediate($this->staffAttr, $this->certification, 'スタッフ属性', '資格');
        Schema::createIntermediate($this->staffAttr, $this->office, 'スタッフ属性', '事業所');
        Schema::createIntermediate($this->staffAttr, $this->officeGroup, 'スタッフ属性', '事業所グループ');
        Schema::createIntermediate($this->staffAttr, $this->role, 'スタッフ属性', '権限');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIntermediate($this->staffAttr, $this->role);
        Schema::dropIntermediate($this->staffAttr, $this->officeGroup);
        Schema::dropIntermediate($this->staffAttr, $this->office);
        Schema::dropCatalogueIntermediate($this->staffAttr, $this->certification);
        Schema::dropIntermediate($this->staffAttr, $this->bankAccount);
        Schema::dropKeywordIndexTable($this->staff);
        Schema::dropAttrTriggers($this->staff);
        Schema::dropAttrIntermediate($this->staff);
        Schema::dropIfExists($this->staffAttr);
        Schema::dropIfExists($this->staffStatus);
        Schema::dropIfExists($this->staff);
    }

    /**
     * スタッフ：状態の定義一覧.
     *
     * @return array
     */
    private function staffStatus(): array
    {
        return [
            [1, 'Provisional'],
            [2, 'Active'],
            [9, 'Retired'],
        ];
    }
}
