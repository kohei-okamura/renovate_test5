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
 * 事業所属性と事業所：指定区分の中間テーブルを生成する.
 */
final class CreateOfficeAttrToQualificationTable extends Migration
{
    private const OFFICE_ATTR = 'office_attr';
    private const OFFICE_QUALIFICATION = 'office_qualification';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create(self::OFFICE_ATTR . '_' . self::OFFICE_QUALIFICATION, function (Blueprint $table): void {
            $table->references(self::OFFICE_ATTR, '事業所属性')->onDelete('cascade');
            $table->string('qualification', 100)->charset('binary')->comment('事業所：指定区分ID');
            $table->foreign('qualification')->references('id')->on(self::OFFICE_QUALIFICATION);
            $table->primary(
                [self::OFFICE_ATTR . '_id', 'qualification'],
                self::OFFICE_ATTR . '_qualification_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropCatalogueIntermediate(self::OFFICE_ATTR, self::OFFICE_QUALIFICATION);
    }
}
