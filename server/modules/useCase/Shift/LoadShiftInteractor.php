<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Domain\Shift\ShiftUtils;
use Domain\Shift\Task;
use Domain\Spreadsheet\ShiftWorksheet;
use Domain\Spreadsheet\ShiftWorksheetRow;
use Domain\Validator\ImportShiftAsyncValidator;
use Lib\Arrays;
use Lib\Exceptions\LogicException;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Contract\IdentifyContractUseCase;

/**
 * 勤務シフト読み込み実装.
 */
class LoadShiftInteractor implements LoadShiftUseCase
{
    private IdentifyContractUseCase $identifyContractUseCase;
    private ImportShiftAsyncValidator $validator;

    /**
     * Constructor.
     *
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \Domain\Validator\ImportShiftAsyncValidator $validator
     */
    public function __construct(IdentifyContractUseCase $identifyContractUseCase, ImportShiftAsyncValidator $validator)
    {
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->validator = $validator;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Worksheet $worksheet): Seq
    {
        $shiftsData = $this->loadWorksheetData($worksheet);
        $this->validator->validate($context, $shiftsData);
        return Seq::fromArray($shiftsData['shifts'])->map(
            function ($shiftData) use ($context, $shiftsData): Shift {
                $officeId = $shiftsData['officeId'];
                $contractId = $this->identifyContractId($context, $officeId, $shiftData)->orNull();
                return ShiftUtils::fromAssoc(compact('officeId', 'contractId') + $shiftData);
            }
        );
    }

    /**
     * ワークシートからデータを読み込む.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return array
     */
    private function loadWorksheetData(Worksheet $worksheet): array
    {
        $shiftSheet = new ShiftWorksheet($worksheet);
        $officeId = $worksheet->getCell(ShiftWorksheet::OFFICE_ID_CELL)->getCalculatedValue();
        $shifts = Arrays::generate(function () use ($worksheet, $shiftSheet) {
            foreach ($shiftSheet->rows(ShiftWorksheet::SHIFT_START_ROW, $worksheet->getHighestDataRow()) as $row) {
                if ($row->assigneeId1()->getCalculatedValue() === '-') {
                    break;
                }
                $shift = $this->getShiftAt($row);
                $totalDuration = $shift['dwsHome'] + $shift['visitingCare']
                    + $shift['physicalCare'] + $shift['housework']
                    + $shift['comprehensive'] + $shift['commAccompany']
                    + $shift['ownExpense'] + $shift['other']
                    + $shift['resting'];
                yield ['totalDuration_confirmation' => $totalDuration] + $shift;
            }
        });
        return [
            'officeId' => $officeId,
            'shifts' => $shifts,
        ];
    }

    /**
     * 勤務シフトの配列から契約IDを特定する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param array $shiftData
     * @return int[]|\ScalikePHP\Option
     */
    private function identifyContractId(Context $context, int $officeId, array $shiftData): Option
    {
        return Task::from($shiftData['task'])
            ->toServiceSegment()
            ->map(function (ServiceSegment $serviceSegment) use ($context, $officeId, $shiftData) {
                return $this->identifyContractUseCase
                    ->handle(
                        $context,
                        Permission::createShifts(),
                        $officeId,
                        $shiftData['userId'],
                        $serviceSegment,
                        Carbon::now()
                    )
                    ->map(fn (Contract $x): int => $x->id)
                    ->getOrElse(function () use ($officeId, $shiftData): void {
                        throw new LogicException(
                            "The user({$shiftData['userId']}) doesn't have a contract with the office({$officeId})"
                        );
                    });
            });
    }

    /**
     * 行から勤務シフトを取得する.
     *
     * @param \Domain\Spreadsheet\ShiftWorksheetRow $row
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return array
     */
    private function getShiftAt(ShiftWorksheetRow $row): array
    {
        return [
            'isTraining1' => $row->isTraining1()->getCalculatedValue(),
            'isTraining2' => $row->isTraining2()->getCalculatedValue(),
            'serviceCode' => $row->serviceCode()->getFormattedValue() ?: null,
            'date' => $row->date()->getValue(),
            'notificationEnabled' => $row->notificationEnabled()->getCalculatedValue(),
            'oneOff' => $row->oneOff()->getCalculatedValue(),
            'firstTime' => $row->firstTime()->getCalculatedValue(),
            'emergency' => $row->emergency()->getCalculatedValue(),
            'sucking' => $row->sucking()->getCalculatedValue(),
            'welfareSpecialistCooperation' => $row->welfareSpecialistCooperation()->getCalculatedValue(),
            'plannedByNovice' => $row->plannedByNovice()->getCalculatedValue(),
            'providedByBeginner' => $row->providedByBeginner()->getCalculatedValue(),
            'providedByCareWorkerForPwsd' => $row->providedByCareWorkerForPwsd()->getCalculatedValue(),
            'over20' => $row->over20()->getCalculatedValue(),
            'over50' => $row->over50()->getCalculatedValue(),
            'behavioralDisorderSupportCooperation' => $row->behavioralDisorderSupportCooperation()->getCalculatedValue(),
            'hospitalized' => $row->hospitalized()->getCalculatedValue(),
            'longHospitalized' => $row->longHospitalized()->getCalculatedValue(),
            'coaching' => $row->coaching()->getCalculatedValue(),
            'vitalFunctionsImprovement1' => $row->vitalFunctionsImprovement1()->getCalculatedValue(),
            'vitalFunctionsImprovement2' => $row->vitalFunctionsImprovement2()->getCalculatedValue(),
            'note' => $row->note()->getCalculatedValue() ?? '',
            'userId' => $row->userId()->getOldCalculatedValue() === '-'
                ? null
                : (int)$row->userId()->getOldCalculatedValue(),
            'assigneeId1' => (int)$row->assigneeId1()->getOldCalculatedValue(),
            'assigneeId2' => $row->assigneeId2()->getOldCalculatedValue() === '-'
                ? null
                : (int)$row->assigneeId2()->getOldCalculatedValue(),
            'assignerId' => $row->assignerId()->getOldCalculatedValue() === '-'
                ? null
                : (int)$row->assignerId()->getOldCalculatedValue(),
            'task' => $row->task()->getCalculatedValue(),
            'startMinute' => $row->startMinute()->getCalculatedValue(),
            'endMinute' => $row->endMinute()->getCalculatedValue(),
            'totalDuration' => $row->totalDuration()->getCalculatedValue(),
            'dwsHome' => $row->dwsHome()->getCalculatedValue(),
            'visitingCare' => $row->visitingCare()->getCalculatedValue(),
            'outingSupport' => $row->outingSupport()->getCalculatedValue(),
            'physicalCare' => $row->physicalCare()->getCalculatedValue(),
            'housework' => $row->housework()->getCalculatedValue(),
            'comprehensive' => $row->comprehensive()->getCalculatedValue(),
            'commAccompany' => $row->commAccompany()->getCalculatedValue(),
            'ownExpense' => $row->ownExpense()->getCalculatedValue(),
            'other' => $row->other()->getCalculatedValue(),
            'resting' => $row->resting()->getCalculatedValue(),
        ];
    }
}
