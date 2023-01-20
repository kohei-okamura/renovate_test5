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
 * 事業所の各サービスのマスタテーブルを追加する.
 */
final class CreateOfficeServicesTable extends Migration
{
    private const OFFICE_ATTR = 'office_attr';
    private const OFFICE_DWS_GENERIC_SERVICE = 'office_dws_generic_service';
    private const OFFICE_DWS_COMM_ACCOMPANY_SERVICE = 'office_dws_comm_accompany_service';
    private const OFFICE_LTCS_CARE_MANAGEMENT_SERVICE = 'office_ltcs_care_management_service';
    private const OFFICE_LTCS_HOME_VISIT_LONG_TERM_CARE_SERVICE = 'office_ltcs_home_visit_long_term_care_service';
    private const OFFICE_LTCS_COMP_HOME_VISITING_SERVICE = 'office_ltcs_comp_home_visiting_service';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create(self::OFFICE_DWS_GENERIC_SERVICE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所：障害福祉サービスID');
            $table->references(self::OFFICE_ATTR, '事業所属性')->onDelete('cascade');
            $table->unsignedBigInteger('dws_area_grade_id')->nullable()->comment('障害地域区分ID');
            $table->code(20)->comment('事業所番号');
            $table->date('opened_on')->nullable()->comment('開設日');
            $table->date('designation_expired_on')->nullable()->comment('指定更新期日');
            // CONSTRAINTS
            $table->foreign('dws_area_grade_id')->references('id')->on('dws_area_grade')->onDelete('cascade');
        });
        Schema::create(self::OFFICE_DWS_COMM_ACCOMPANY_SERVICE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所：障害福祉サービス（地域生活支援事業・移動支援）ID');
            $table->references(self::OFFICE_ATTR, '事業所属性')->onDelete('cascade');
            $table->code(20)->comment('事業所番号');
            $table->date('opened_on')->nullable()->comment('開設日');
            $table->date('designation_expired_on')->nullable()->comment('指定更新期日');
            // CONSTRAINTS
        });
        Schema::create(self::OFFICE_LTCS_CARE_MANAGEMENT_SERVICE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所：介護保険サービス：居宅介護支援ID');
            $table->references(self::OFFICE_ATTR, '事業所属性')->onDelete('cascade');
            $table->unsignedBigInteger('ltcs_area_grade_id')->nullable()->comment('介保地域区分ID');
            $table->code(20)->comment('事業所番号');
            $table->date('opened_on')->nullable()->comment('開設日');
            $table->date('designation_expired_on')->nullable()->comment('指定更新期日');
            // CONSTRAINTS
            $table->foreign('ltcs_area_grade_id', 'office_ltcs_care_management_area_grade_foreign')->references('id')->on('ltcs_area_grade')->onDelete('cascade');
        });
        Schema::create(self::OFFICE_LTCS_HOME_VISIT_LONG_TERM_CARE_SERVICE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所：介護保険サービス：訪問介護ID');
            $table->references(self::OFFICE_ATTR, '事業所属性')->onDelete('cascade');
            $table->unsignedBigInteger('ltcs_area_grade_id')->nullable()->comment('介保地域区分ID');
            $table->code(20)->comment('事業所番号');
            $table->date('opened_on')->nullable()->comment('開設日');
            $table->date('designation_expired_on')->nullable()->comment('指定更新期日');
            // CONSTRAINTS
            $table->foreign('ltcs_area_grade_id', 'office_ltcs_home_visit_long_term_care_area_grade_foreign')->references('id')->on('ltcs_area_grade')->onDelete('cascade');
        });
        Schema::create(self::OFFICE_LTCS_COMP_HOME_VISITING_SERVICE, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所：介護保険サービス：訪問型サービス（総合事業）ID');
            $table->references(self::OFFICE_ATTR, '事業所属性')->onDelete('cascade');
            $table->code(20)->comment('事業所番号');
            $table->date('opened_on')->nullable()->comment('開設日');
            $table->date('designation_expired_on')->nullable()->comment('指定更新期日');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(self::OFFICE_DWS_GENERIC_SERVICE);
        Schema::dropIfExists(self::OFFICE_DWS_COMM_ACCOMPANY_SERVICE);
        Schema::dropIfExists(self::OFFICE_LTCS_CARE_MANAGEMENT_SERVICE);
        Schema::dropIfExists(self::OFFICE_LTCS_HOME_VISIT_LONG_TERM_CARE_SERVICE);
        Schema::dropIfExists(self::OFFICE_LTCS_COMP_HOME_VISITING_SERVICE);
    }
}
