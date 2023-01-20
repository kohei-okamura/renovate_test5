<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Support\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * 事業所：介護保険サービス：介護予防支援テーブルを追加する.
 */
final class CreateOfficeLtcsPreventionServiceTable extends Migration
{
    private const OFFICE_ATTR = 'office_attr';
    private const OFFICE_LTCS_PREVENTION_SERVICE = 'office_ltcs_prevention_service';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(self::OFFICE_LTCS_PREVENTION_SERVICE, function (Blueprint $table): void {
            $table->id()->comment('事業所：介護保険サービス：介護予防支援ID');
            $table->references(self::OFFICE_ATTR, '事業所属性')->onDelete('cascade');
            $table->code(10)->comment('事業所番号');
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
        Schema::dropIfExists(self::OFFICE_LTCS_PREVENTION_SERVICE);
    }
}
