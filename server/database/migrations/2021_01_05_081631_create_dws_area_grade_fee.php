<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDwsAreaGradeFee extends Migration
{
    private const DWS_AREA_GRADE = 'dws_area_grade';
    private const LTCS_AREA_GRADE = 'ltcs_area_grade';
    private string $dwsAreaGradeFee = 'dws_area_grade_fee';
    private string $ltcsAreaGradeFee = 'ltcs_area_grade_fee';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        Schema::create($this->dwsAreaGradeFee, function (Blueprint $table) {
            $table->id()->comment('地域区分単価ID');
            $table->references(self::DWS_AREA_GRADE, '地域区分');
            $table->date('effectivated_on')->comment('適用日');
            $table->integer('fee')->comment('単価');
        });
        Schema::create($this->ltcsAreaGradeFee, function (Blueprint $table) {
            $table->id()->comment('地域区分単価ID');
            $table->references(self::LTCS_AREA_GRADE, '地域区分');
            $table->date('effectivated_on')->comment('適用日');
            $table->integer('fee')->comment('単価');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->dwsAreaGradeFee);
        Schema::dropIfExists($this->ltcsAreaGradeFee);
    }
}
