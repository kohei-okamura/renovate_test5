<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 事業所マスタテーブルを追加する.
 */
final class CreateOfficeTable extends Migration
{
    private string $office = 'office';
    private string $officeAttr = 'office_attr';
    private string $officeDisabilityWelfareService = 'office_disability_welfare_service';
    private string $officeLongTermCareService = 'office_long_term_care_service';
    private string $officeComprehensiveService = 'office_comprehensive_service';
    private string $officeStatus = 'office_status';
    private string $serviceSegment = 'service_segment';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->office, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所ID');
            $table->references('organization', '事業者');
            $table->createdAt();
        });
        Schema::createCatalogue($this->officeStatus, '状態', $this->officeStatus());
        Schema::create($this->officeAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所属性ID');
            $table->references('office', '事業所');
            $table->unsignedBigInteger('office_group_id')->nullable()->comment('事業所グループ');
            $table->catalogued('business', '事業内容');
            $table->string('name', 200)->comment('事業所名');
            $table->string('abbr', 200)->comment('略称');
            $table->string('phonetic_name', 200)->comment('フリガナ');
            $table->catalogued('purpose', '事業者区分');
            $table->addr();
            $table->location();
            $table->tel();
            $table->fax();
            $table->email();
            $table->catalogued($this->officeStatus, '状態', 'status');
            $table->attr($this->office);
            // CONSTRAINTS
            $table->foreign('office_group_id')->references('id')->on('office_group');
        });
        Schema::createAttrIntermediate($this->office, '事業所');
        Schema::createCatalogueIntermediate($this->officeAttr, $this->serviceSegment, '事業所属性', 'サービス領域');
        Schema::create($this->officeDisabilityWelfareService, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所障害福祉サービス情報ID');
            $table->references($this->officeAttr, '事業所属性')->onDelete('cascade');
            $table->unsignedBigInteger('dws_area_grade_id')->nullable()->comment('障害地域区分ID');
            $table->code(20)->comment('事業所番号');
            $table->date('opened_on')->nullable()->comment('開設日');
            $table->date('designation_expired_on')->nullable()->comment('指定更新期日');
            // CONSTRAINTS
            $table->foreign('dws_area_grade_id')->references('id')->on('dws_area_grade')->onDelete('cascade');
        });
        Schema::create($this->officeLongTermCareService, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所介護保険サービス情報ID');
            $table->references($this->officeAttr, '事業所属性')->onDelete('cascade');
            $table->unsignedBigInteger('ltcs_area_grade_id')->nullable()->comment('介保地域区分ID');
            $table->code(20)->comment('事業所番号');
            $table->date('opened_on')->nullable()->comment('開設日');
            $table->date('designation_expired_on')->nullable()->comment('指定更新期日');
            // CONSTRAINTS
            $table->foreign('ltcs_area_grade_id')->references('id')->on('ltcs_area_grade')->onDelete('cascade');
        });
        Schema::create($this->officeComprehensiveService, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所総合事業サービス情報ID');
            $table->references($this->officeAttr, '事業所属性')->onDelete('cascade');
            $table->code(20)->comment('事業所番号');
            $table->date('opened_on')->nullable()->comment('開設日');
            $table->date('designation_expired_on')->nullable()->comment('指定更新期日');
        });
        Schema::createAttrTriggers($this->office);
        Schema::createKeywordIndexTable(
            $this->office,
            '事業所',
            ['name', 'abbr', 'phonetic_name']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropAttrTriggers($this->office);
        Schema::dropIfExists($this->officeComprehensiveService);
        Schema::dropIfExists($this->officeLongTermCareService);
        Schema::dropIfExists($this->officeDisabilityWelfareService);
        Schema::dropCatalogueIntermediate($this->officeAttr, $this->serviceSegment);
        Schema::dropAttrIntermediate($this->office);
        Schema::dropKeywordIndexTable($this->office);
        Schema::dropIfExists($this->officeAttr);
        Schema::dropIfExists($this->officeStatus);
        Schema::dropIfExists($this->office);
    }

    /**
     * 事業所：状態の定義一覧.
     *
     * @return array
     */
    private function officeStatus(): array
    {
        return [
            [1, 'InPreparation'],
            [2, 'InOperation'],
            [8, 'Suspended'],
            [9, 'Closed'],
        ];
    }
}
