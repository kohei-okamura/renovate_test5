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
 * 事業所グループマスタテーブルを追加する.
 */
final class CreateOfficeGroupTable extends Migration
{
    private $officeGroup = 'office_group';
    private $officeGroupIndex = 'office_group_index';
    private $buildOfficeGroupIndex = 'build_office_group_index';
    private $afterInsertOfficeGroup = 'after_insert_office_group';
    private $afterUpdateOfficeGroup = 'after_update_office_group';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->down();
        Schema::create($this->officeGroup, function (Blueprint $table): void {
            // COLUMNS
            $table->id()->comment('事業所グループID');
            $table->references('organization', '事業者');
            $table->bigInteger('parent_office_group_id')->unsigned()->nullable()->comment('上位事業所グループID');
            $table->string('name', 100)->comment('事業所グループ名');
            $table->sortOrder();
            $table->createdAt();
            $table->updatedAt();
            // KEYS
            $table->index(['organization_id', 'parent_office_group_id']);
            // CONSTRAINTS
            $table->foreign('parent_office_group_id')->references('id')->on($this->officeGroup);
        });
        Schema::create($this->officeGroupIndex, function (Blueprint $table): void {
            // COLUMNS
            $table->bigInteger('office_group_id')->unsigned()->comment('事業所グループID');
            $table->bigInteger('parent_office_group_id')->unsigned()->comment('上位事業所グループID');
            $table->integer('distance')->comment('距離');
            // KEYS
            $table->primary(['office_group_id', 'parent_office_group_id'], 'office_group_index_primary');
            // CONSTRAINTS
            $table->foreign('office_group_id')->references('id')->on($this->officeGroup)->onDelete('cascade');
            $table->foreign('parent_office_group_id')->references('id')->on($this->officeGroup)->onDelete('cascade');
        });
        Schema::createProcedure(
            $this->buildOfficeGroupIndex,
            'IN id BIGINT UNSIGNED',
            <<<__EOS__
            DECLARE pid BIGINT UNSIGNED;
            DECLARE distance BIGINT UNSIGNED;
            SET pid = id;
            SET distance = 0;
            DELETE FROM {$this->officeGroupIndex} WHERE {$this->officeGroupIndex}.office_group_id = id;
            WHILE pid IS NOT NULL DO
                INSERT INTO {$this->officeGroupIndex} VALUES (id, pid, distance);
                SELECT parent_office_group_id INTO pid FROM {$this->officeGroup} WHERE {$this->officeGroup}.id = pid;
                SET distance = distance + 1;
            END WHILE;
            __EOS__
        );
        Schema::createTrigger(
            $this->afterInsertOfficeGroup,
            'INSERT',
            $this->officeGroup,
            ["CALL {$this->buildOfficeGroupIndex}(NEW.id)"]
        );
        Schema::createTrigger(
            $this->afterUpdateOfficeGroup,
            'UPDATE',
            $this->officeGroup,
            ["CALL {$this->buildOfficeGroupIndex}(NEW.id)"]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropTrigger($this->afterUpdateOfficeGroup);
        Schema::dropTrigger($this->afterInsertOfficeGroup);
        Schema::dropProcedure($this->buildOfficeGroupIndex);
        Schema::dropIfExists($this->officeGroupIndex);
        Schema::dropIfExists($this->officeGroup);
    }
}
