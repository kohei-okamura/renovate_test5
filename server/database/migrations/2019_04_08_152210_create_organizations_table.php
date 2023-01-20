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
 * 事業者マスタテーブルを追加する.
 */
final class CreateOrganizationsTable extends Migration
{
    private $organization = 'organization';
    private $organizationAttr = 'organization_attr';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->organization, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業者ID');
            $table->code(100)->unique()->comment('事業者コード');
            $table->createdAt();
        });
        Schema::create($this->organizationAttr, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業者属性ID');
            $table->references($this->organization, '事業所');
            $table->string('name', 100)->comment('事業者名');
            $table->addr();
            $table->tel();
            $table->fax();
            $table->attr($this->organization);
        });
        Schema::createAttrIntermediate($this->organization, '事業所');
        Schema::createAttrTriggers($this->organization);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropAttrTriggers($this->organization);
        Schema::dropAttrIntermediate($this->organization);
        Schema::dropIfExists($this->organizationAttr);
        Schema::dropIfExists($this->organization);
    }
}
