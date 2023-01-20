<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Spreadsheet;

use Domain\Spreadsheet\ShiftWorksheet;
use Domain\Spreadsheet\ShiftWorksheetRow;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use ScalikePHP\Seq;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * ShiftWorksheet のテスト.
 */
class ShiftWorksheetTest extends Test
{
    use UnitSupport;

    private const SHEET_INDEX_SHIFT = 0;
    private const VALID_SHIFTS_FILE = 'Shift/valid-shifts.xlsx';

    private ShiftWorksheet $shiftWorksheet;
    private Worksheet $worksheet;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ShiftWorksheetTest $self): void {
            $self->worksheet = (new XlsxReader())->load(codecept_data_dir(self::VALID_SHIFTS_FILE))
                ->getSheet(self::SHEET_INDEX_SHIFT);
            $self->shiftWorksheet = new ShiftWorksheet($self->worksheet);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_row(): void
    {
        $this->should('return ShiftWorksheetRow corresponding to the given index line', function () {
            $this->assertRowStrictEquals(
                new ShiftWorksheetRow(new Row($this->worksheet, 10)),
                $this->shiftWorksheet->row(10)
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_rows(): void
    {
        $this->should('return generator of ShiftWorksheetRow corresponding to lines between startRow and endRow', function () {
            $expected = Seq::fromArray([
                new ShiftWorksheetRow(new Row($this->worksheet, 9)),
                new ShiftWorksheetRow(new Row($this->worksheet, 10)),
                new ShiftWorksheetRow(new Row($this->worksheet, 11)),
            ])->toGenerator();
            $actual = $this->shiftWorksheet->rows(9, 11);
            $expectedArray = Seq::fromArray($expected)->toArray();
            $actualArray = Seq::fromArray($actual)->toArray();

            $this->assertEquals($expected, $actual);
            $this->assertRowStrictEquals($expectedArray[0], $actualArray[0]);
            $this->assertRowStrictEquals($expectedArray[1], $actualArray[1]);
            $this->assertRowStrictEquals($expectedArray[2], $actualArray[2]);
        });
    }

    /**
     * @param ShiftWorksheetRow $expected
     * @param ShiftWorksheetRow $actual
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function assertRowStrictEquals(ShiftWorksheetRow $expected, ShiftWorksheetRow $actual): void
    {
        self::assertEquals($expected, $actual);
        self::assertSame($expected->isTraining1(), $actual->isTraining1());
        self::assertSame($expected->isTraining1()->getValue(), $actual->isTraining1()->getValue());
        self::assertSame($expected->isTraining2(), $actual->isTraining2());
        self::assertSame($expected->isTraining2()->getValue(), $actual->isTraining2()->getValue());
        self::assertSame($expected->serviceCode(), $actual->serviceCode());
        self::assertSame($expected->serviceCode()->getValue(), $actual->serviceCode()->getValue());
        self::assertSame($expected->date(), $actual->date());
        self::assertSame($expected->date()->getValue(), $actual->date()->getValue());
        self::assertSame($expected->notificationEnabled(), $actual->notificationEnabled());
        self::assertSame($expected->notificationEnabled()->getValue(), $actual->notificationEnabled()->getValue());
        self::assertSame($expected->oneOff(), $actual->oneOff());
        self::assertSame($expected->oneOff()->getValue(), $actual->oneOff()->getValue());
        self::assertSame($expected->firstTime(), $actual->firstTime());
        self::assertSame($expected->firstTime()->getValue(), $actual->firstTime()->getValue());
        self::assertSame($expected->emergency(), $actual->emergency());
        self::assertSame($expected->emergency()->getValue(), $actual->emergency()->getValue());
        self::assertSame($expected->sucking(), $actual->sucking());
        self::assertSame($expected->sucking()->getValue(), $actual->sucking()->getValue());
        self::assertSame($expected->welfareSpecialistCooperation(), $actual->welfareSpecialistCooperation());
        self::assertSame($expected->welfareSpecialistCooperation()->getValue(), $actual->welfareSpecialistCooperation()->getValue());
        self::assertSame($expected->plannedByNovice(), $actual->plannedByNovice());
        self::assertSame($expected->plannedByNovice()->getValue(), $actual->plannedByNovice()->getValue());
        self::assertSame($expected->providedByBeginner(), $actual->providedByBeginner());
        self::assertSame($expected->providedByBeginner()->getValue(), $actual->providedByBeginner()->getValue());
        self::assertSame($expected->providedByCareWorkerForPwsd(), $actual->providedByCareWorkerForPwsd());
        self::assertSame($expected->providedByCareWorkerForPwsd()->getValue(), $actual->providedByCareWorkerForPwsd()->getValue());
        self::assertSame($expected->over20(), $actual->over20());
        self::assertSame($expected->over20()->getValue(), $actual->over20()->getValue());
        self::assertSame($expected->over50(), $actual->over50());
        self::assertSame($expected->over50()->getValue(), $actual->over50()->getValue());
        self::assertSame($expected->behavioralDisorderSupportCooperation(), $actual->behavioralDisorderSupportCooperation());
        self::assertSame($expected->behavioralDisorderSupportCooperation()->getValue(), $actual->behavioralDisorderSupportCooperation()->getValue());
        self::assertSame($expected->hospitalized(), $actual->hospitalized());
        self::assertSame($expected->hospitalized()->getValue(), $actual->hospitalized()->getValue());
        self::assertSame($expected->longHospitalized(), $actual->longHospitalized());
        self::assertSame($expected->longHospitalized()->getValue(), $actual->longHospitalized()->getValue());
        self::assertSame($expected->coaching(), $actual->coaching());
        self::assertSame($expected->coaching()->getValue(), $actual->coaching()->getValue());
        self::assertSame($expected->vitalFunctionsImprovement1(), $actual->vitalFunctionsImprovement1());
        self::assertSame($expected->vitalFunctionsImprovement1()->getValue(), $actual->vitalFunctionsImprovement1()->getValue());
        self::assertSame($expected->vitalFunctionsImprovement2(), $actual->vitalFunctionsImprovement2());
        self::assertSame($expected->vitalFunctionsImprovement2()->getValue(), $actual->vitalFunctionsImprovement2()->getValue());
        self::assertSame($expected->note(), $actual->note());
        self::assertSame($expected->note()->getValue(), $actual->note()->getValue());
        self::assertSame($expected->userId(), $actual->userId());
        self::assertSame($expected->userId()->getValue(), $actual->userId()->getValue());
        self::assertSame($expected->assigneeId1(), $actual->assigneeId1());
        self::assertSame($expected->assigneeId1()->getValue(), $actual->assigneeId1()->getValue());
        self::assertSame($expected->assigneeId2(), $actual->assigneeId2());
        self::assertSame($expected->assigneeId2()->getValue(), $actual->assigneeId2()->getValue());
        self::assertSame($expected->assignerId(), $actual->assignerId());
        self::assertSame($expected->assignerId()->getValue(), $actual->assignerId()->getValue());
        self::assertSame($expected->task(), $actual->task());
        self::assertSame($expected->task()->getValue(), $actual->task()->getValue());
        self::assertSame($expected->startMinute(), $actual->startMinute());
        self::assertSame($expected->startMinute()->getValue(), $actual->startMinute()->getValue());
        self::assertSame($expected->endMinute(), $actual->endMinute());
        self::assertSame($expected->endMinute()->getValue(), $actual->endMinute()->getValue());
        self::assertSame($expected->totalDuration(), $actual->totalDuration());
        self::assertSame($expected->totalDuration()->getValue(), $actual->totalDuration()->getValue());
        self::assertSame($expected->dwsHome(), $actual->dwsHome());
        self::assertSame($expected->dwsHome()->getValue(), $actual->dwsHome()->getValue());
        self::assertSame($expected->visitingCare(), $actual->visitingCare());
        self::assertSame($expected->visitingCare()->getValue(), $actual->visitingCare()->getValue());
        self::assertSame($expected->outingSupport(), $actual->outingSupport());
        self::assertSame($expected->outingSupport()->getValue(), $actual->outingSupport()->getValue());
        self::assertSame($expected->physicalCare(), $actual->physicalCare());
        self::assertSame($expected->physicalCare()->getValue(), $actual->physicalCare()->getValue());
        self::assertSame($expected->housework(), $actual->housework());
        self::assertSame($expected->housework()->getValue(), $actual->housework()->getValue());
        self::assertSame($expected->comprehensive(), $actual->comprehensive());
        self::assertSame($expected->comprehensive()->getValue(), $actual->comprehensive()->getValue());
        self::assertSame($expected->commAccompany(), $actual->commAccompany());
        self::assertSame($expected->commAccompany()->getValue(), $actual->commAccompany()->getValue());
        self::assertSame($expected->ownExpense(), $actual->ownExpense());
        self::assertSame($expected->ownExpense()->getValue(), $actual->ownExpense()->getValue());
        self::assertSame($expected->other(), $actual->other());
        self::assertSame($expected->other()->getValue(), $actual->other()->getValue());
        self::assertSame($expected->resting(), $actual->resting());
        self::assertSame($expected->resting()->getValue(), $actual->resting()->getValue());
    }
}
