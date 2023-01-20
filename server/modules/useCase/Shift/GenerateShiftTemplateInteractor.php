<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Config\Config;
use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\File\FileStorage;
use Domain\File\TemporaryFiles;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Domain\Spreadsheet\Coordinate;
use Illuminate\Support\Arr;
use InvalidArgumentException as PhpInvalidArgumentException;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use Lib\Exceptions\TemporaryFileAccessException;
use Lib\Math;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use ScalikePHP\Seq;
use UseCase\Office\FindOfficeUseCase;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\Staff\FindStaffUseCase;
use UseCase\User\FindUserUseCase;

/**
 * 勤務シフト一括登録雛形ファイル生成ユースケース実装.
 */
final class GenerateShiftTemplateInteractor implements GenerateShiftTemplateUseCase
{
    private const SHIFT_START_ROW = 9;
    private const SCHEDULE_START_ROW = 2;

    private const SHIFT_ROWS = 30;
    private const SCHEDULE_ROWS = 30;

    private const COLUMNS = 135;

    private const TIMETABLE_START_COLUMN = 62;

    private const SHEET_INDEX_SHIFT = 0;
    private const SHEET_INDEX_SCHEDULE = 1;
    private const SHEET_INDEX_OFFICE = 2;
    private const SHEET_INDEX_USER = 3;
    private const SHEET_INDEX_STAFF = 4;
    private const SHEET_INDEX_TASK = 5;

    private const TEMPLATE_FILENAME = 'shifts.xlsx';

    private const TEMPORARY_FILE_PREFIX = 'zinger-';

    private Config $config;
    private FindOfficeUseCase $findOffice;
    private FindShiftUseCase $findShift;
    private FindUserUseCase $findUser;
    private FindStaffUseCase $findStaff;
    private LookupOfficeUseCase $lookupOffice;
    private TemporaryFiles $temporaryFiles;
    private FileStorage $storage;

    /**
     * {@link \UseCase\Shift\GenerateShiftTemplateInteractor} constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \UseCase\Office\FindOfficeUseCase $findOffice
     * @param \UseCase\Shift\FindShiftUseCase $findShift
     * @param \UseCase\User\FindUserUseCase $findUser
     * @param \UseCase\Staff\FindStaffUseCase $findStaff
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOffice
     * @param \Domain\File\TemporaryFiles $temporaryFiles
     * @param \Domain\File\FileStorage $storage
     */
    public function __construct(
        Config $config,
        FindOfficeUseCase $findOffice,
        FindShiftUseCase $findShift,
        FindUserUseCase $findUser,
        FindStaffUseCase $findStaff,
        LookupOfficeUseCase $lookupOffice,
        TemporaryFiles $temporaryFiles,
        FileStorage $storage
    ) {
        $this->config = $config;
        $this->findOffice = $findOffice;
        $this->findShift = $findShift;
        $this->findUser = $findUser;
        $this->findStaff = $findStaff;
        $this->lookupOffice = $lookupOffice;
        $this->temporaryFiles = $temporaryFiles;
        $this->storage = $storage;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, CarbonRange $range, bool $isCopy, array $filterParams): string
    {
        $officeId = Arr::get($filterParams, 'officeId');
        if ($officeId === null) {
            throw new InvalidArgumentException('officeId is empty');
        }
        /** @var \Domain\Common\Carbon $sourceStart */
        $sourceStart = Arr::get($filterParams, 'scheduleDateAfter');
        $offices = $this->findOffice->handle($context, [Permission::createShifts()], [], ['all' => true])->list;
        $staffs = $this->findStaff
            ->handle(
                $context,
                Permission::createShifts(),
                ['officeId' => $officeId],
                ['all' => true]
            )
            ->list;
        $users = $this->findUser
            ->handle(
                $context,
                Permission::createShifts(),
                ['officeId' => $officeId],
                ['all' => true]
            )
            ->list;
        $shifts = $isCopy
            ? $this->findShift
                ->handle(
                    $context,
                    Permission::createShifts(),
                    $filterParams,
                    ['all' => true]
                )
                ->list
            : Seq::emptySeq();

        try {
            $spreadsheet = $this->loadTemplate();
            $spreadsheet->getDefaultStyle()->getFont()->setName('メイリオ');
            $this->setupOfficeWorksheet($spreadsheet, $offices);
            $this->setupUserWorksheet($spreadsheet, $users);
            $this->setupStaffWorksheet($spreadsheet, $staffs);
            $this->setupTaskWorksheet($spreadsheet);
            /** @var \Domain\Office\Office $office */
            $office = $offices->find(fn (Office $x): bool => $x->id === $officeId)
                ->getOrElse(function () use ($officeId): void {
                    throw new NotFoundException("Office [{$officeId}] not found");
                });
            $this->setupShiftWorksheet($spreadsheet, $office, $users, $staffs, $shifts, $range, $sourceStart);
            $this->setupScheduleWorksheet($spreadsheet);

            $path = $this->save($spreadsheet);
            return $this->storage->store('exported', FileInputStream::from(basename($path), $path))
                ->getOrElse(function (): void {
                    throw new TemporaryFileAccessException('Failed to upload shift template');
                });
        } catch (PhpSpreadsheetException $exception) {
            throw new RuntimeException("Failed to generate shift template: {$exception->getMessage()}", 0, $exception);
        }
    }

    /**
     * テンプレートファイルを読み込む.
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private function loadTemplate(): Spreadsheet
    {
        $path = $this->config->get('zinger.path.resources.spreadsheets') . '/' . self::TEMPLATE_FILENAME;
        try {
            return (new XlsxReader())->load($path);
        } catch (PhpInvalidArgumentException $exception) { // NOTE: https://github.com/PHPOffice/PhpSpreadsheet/blob/1.14.1/samples/Reader/16_Handling_loader_exceptions_using_TryCatch.php
            throw new RuntimeException("Failed to load template: {$path}", 0, $exception);
        }
    }

    /**
     * ファイルを一時ファイルとして保存する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @return string
     */
    private function save(Spreadsheet $spreadsheet): string
    {
        $path = $this->temporaryFiles->create(self::TEMPORARY_FILE_PREFIX, '.xlsx')->getPathname();
        try {
            $writer = new XlsxWriter($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->setOffice2003Compatibility(false);
            $writer->save($path);
            return $path;
        } catch (PhpSpreadsheetException $exception) {
            throw new RuntimeException("Failed to save the spreadsheet: {$exception->getMessage()}", 0, $exception);
        }
    }

    /**
     * ドロップダウン型入力規則を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param string $coordinate
     * @param string $formula
     * @return void
     */
    private function setupDropDownValidation(Worksheet $worksheet, string $coordinate, string $formula): void
    {
        $validation = (new DataValidation())
            ->setAllowBlank(true)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setFormula1($formula)
            ->setShowDropDown(true)
            ->setShowErrorMessage(true)
            ->setShowInputMessage(false)
            ->setType(DataValidation::TYPE_LIST);
        $worksheet->setDataValidation($coordinate, $validation);
    }

    /**
     * シート「週間シフト」を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param \Domain\Office\Office $office
     * @param \ScalikePHP\Seq $users
     * @param \ScalikePHP\Seq $staffs
     * @param \ScalikePHP\Seq $shifts
     * @param \Domain\Common\CarbonRange $range
     * @param null|\Domain\Common\Carbon $sourceStart
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return void
     */
    private function setupShiftWorksheet(
        Spreadsheet $spreadsheet,
        Office $office,
        Seq $users,
        Seq $staffs,
        Seq $shifts,
        CarbonRange $range,
        ?Carbon $sourceStart
    ): void {
        $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);
        $this->setupShiftWorksheetOfficeInfo($worksheet, $office);
        $this->setupShiftWorksheetRange($worksheet, $range);
        $this->setupShiftWorksheetConditionalStyles($worksheet, self::SHIFT_ROWS);
        $this->setupShiftWorksheetDataValidation($worksheet, self::SHIFT_ROWS);
        $this->setupShiftWorksheetFormulas($worksheet, self::SHIFT_ROWS);
        $this->setupShiftWorksheetStyles($worksheet, self::SHIFT_ROWS);
        if ($shifts->nonEmpty()) {
            // 元となる過去の勤務シフトがない（コピーしないなど）場合は、過去の勤務シフトコピー処理は呼ばない
            $this->setupShiftWorksheetSourceBasedShift($worksheet, $users, $staffs, $shifts, $range, $sourceStart);
        }
    }

    /**
     * シート「週間シフト」の事業所情報を設定する.
     *
     * @param Worksheet $worksheet
     * @param \Domain\Office\Office $office
     * @return void
     */
    private function setupShiftWorksheetOfficeInfo(Worksheet $worksheet, Office $office): void
    {
        $worksheet->setCellValue(Coordinate::cell('B', 2), $office->name);
        $worksheet->setCellValue(
            Coordinate::cell(
                'B',
                3
            ),
            '=IF(ISBLANK(B2), "-", VLOOKUP(B2, 事業所!A:B, 2, FALSE))'
        );

        $destination = Coordinate::range(
            Coordinate::cell('B', 2),
            Coordinate::cell('B', 2)
        );
        $this->setupDropDownValidation($worksheet, $destination, '事業所!A:A');
    }

    /**
     * シート「週間シフト」の期間を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param CarbonRange $range
     * @return void
     */
    private function setupShiftWorksheetRange(Worksheet $worksheet, CarbonRange $range): void
    {
        $worksheet->setCellValue(Coordinate::cell('B', 4), $range->start->toDateString());
        $worksheet->setCellValue(Coordinate::cell('B', 5), $range->end->toDateString());
    }

    /**
     * シート「週間シフト」の条件付き書式を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param int $rows
     * @return void
     */
    private function setupShiftWorksheetConditionalStyles(Worksheet $worksheet, int $rows): void
    {
        $endRow = self::SHIFT_START_ROW + $rows - 1;
        $styles = [
            'A9' => Coordinate::range('A9', Coordinate::cell('A', $endRow)),
            'C9' => Coordinate::range('C9', Coordinate::cell('C', $endRow)),
            'E9' => Coordinate::range('E9', Coordinate::cell('E', $endRow)),
            'G9' => Coordinate::range('G9', Coordinate::cell('G', $endRow)),
            'G9:AP9' => Coordinate::range('G9', Coordinate::cell('AP', $endRow)),
            'N9:O9' => Coordinate::range('N9', Coordinate::cell('O', $endRow)),
            'P9' => Coordinate::range('P9', Coordinate::cell('P', $endRow)),
            'Q9' => Coordinate::range('Q9', Coordinate::cell('Q', $endRow)),
            'R9' => Coordinate::range('R9', Coordinate::cell('R', $endRow)),
            'S9' => Coordinate::range('S9', Coordinate::cell('S', $endRow)),
            'T9' => Coordinate::range('T9', Coordinate::cell('T', $endRow)),
            'U9' => Coordinate::range('U9', Coordinate::cell('U', $endRow)),
            'W9' => Coordinate::range('W9', Coordinate::cell('W', $endRow)),
            'Z9:AA9' => Coordinate::range('Z9', Coordinate::cell('AA', $endRow)),
            'AB9' => Coordinate::range('AB9', Coordinate::cell('AB', $endRow)),
            'AC9:AF9' => Coordinate::range('AC9', Coordinate::cell('AF', $endRow)),
            'AG9:AH9' => Coordinate::range('AG9', Coordinate::cell('AH', $endRow)),
            'AI9:AL9' => Coordinate::range('AI9', Coordinate::cell('AL', $endRow)),
            'AM9:AN9' => Coordinate::range('AM9', Coordinate::cell('AN', $endRow)),
        ];
        foreach ($styles as $source => $destination) {
            $worksheet->setConditionalStyles($destination, $worksheet->getConditionalStyles($source));
            $worksheet->removeConditionalStyles($source);
        }
    }

    /**
     * シート「週間シフト」の書式を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param int $rows
     * @return void
     */
    private function setupShiftWorksheetDataValidation(Worksheet $worksheet, int $rows): void
    {
        $startRow = self::SHIFT_START_ROW;
        $endRow = self::SHIFT_START_ROW + $rows - 1;

        $dropdownValidations = [
            'A' => '利用者一覧!A:A',
            'B' => 'スタッフ一覧!A:A',
            'C' => '"＊"',
            'D' => 'スタッフ一覧!A:A',
            'E' => '"＊"',
            'F' => '勤務区分!A:A',
            'X' => '"＊"',
            'Y' => '"＊"',
            'Z' => '"＊"',
            'AA' => '"＊"',
            'AB' => '"＊"',
            'AC' => '"＊"',
            'AD' => '"＊"',
            'AE' => '"＊"',
            'AF' => '"＊"',
            'AG' => '"＊"',
            'AH' => '"＊"',
            'AI' => '"＊"',
            'AJ' => '"＊"',
            'AK' => '"＊"',
            'AL' => '"＊"',
            'AM' => '"＊"',
            'AN' => '"＊"',
            'AO' => 'スタッフ一覧!A:A',
        ];
        foreach ($dropdownValidations as $column => $formula) {
            $destination = Coordinate::range(
                Coordinate::cell($column, $startRow),
                Coordinate::cell($column, $endRow)
            );
            $this->setupDropDownValidation($worksheet, $destination, $formula);
        }

        $copyValidations = ['G', 'H', 'J', 'K', 'P', 'Q', 'R', 'V'];
        foreach ($copyValidations as $column) {
            $start = Coordinate::cell($column, $startRow);
            $end = Coordinate::cell($start, $endRow);
            $validation = $worksheet->getDataValidation($start);
            $worksheet->setDataValidation($start, null);
            $worksheet->setDataValidation(Coordinate::range($start, $end), $validation);
        }
    }

    /**
     * シート「週間シフト」の各セルに値を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param int $rows
     * @return void
     */
    private function setupShiftWorksheetFormulas(Worksheet $worksheet, int $rows): void
    {
        $values = [
            'I' => '=IF(ISBLANK(H??), "", H??)',
            'L' => '=IF(J?? > K??, "●", "")',
            'M' => '=IF(AX?? > 0, FLOOR(AX?? / 60, 1) * 100 + MOD(AX??, 60), "")',
            'N' => '=IF(AY?? > 0, FLOOR(AY?? / 60, 1) * 100 + MOD(AY??, 60), "")',
            'O' => '=IF(AZ?? > 0, FLOOR(AZ?? / 60, 1) * 100 + MOD(AZ??, 60), "")',
            'S' => '=IF(BD?? > 0, FLOOR(BD?? / 60, 1) * 100 + MOD(BD??, 60), "")',
            'T' => '=IF(BE?? > 0, FLOOR(BE?? / 60, 1) * 100 + MOD(BE??, 60), "")',
            'U' => '=IF(BF?? > 0, FLOOR(BF?? / 60, 1) * 100 + MOD(BF??, 60), "")',
            'W' => '=IF(AND(ISBLANK(A??), ISBLANK(B??)), "", IF(AU?? < 801100, FLOOR((SUM(AY??:AZ??) + SUM(BB??:BH??)) / 60, 1) * 100 + MOD(SUM(AY??:AZ??) + SUM(BB??:BH??), 60), M??))',
            'AQ' => '=IF(ISBLANK(A??), "-", VLOOKUP(A??, 利用者一覧!A:D, 4, FALSE))',
            'AR' => '=IF(ISBLANK(B??), "-", VLOOKUP(B??, スタッフ一覧!A:D, 4, FALSE))',
            'AS' => '=IF(ISBLANK(D??), "-", VLOOKUP(D??, スタッフ一覧!A:D, 4, FALSE))',
            'AT' => '=IF(ISBLANK(AO??), "-", VLOOKUP(AO??, スタッフ一覧!A:D, 4, FALSE))',
            'AU' => '=IF(ISBLANK(F??), "-", VLOOKUP(F??, 勤務区分!A:C, 3, FALSE))',
            'AV' => '=FLOOR(J?? / 100, 1) * 60 + MOD(J??, 100)',
            'AW' => '=FLOOR(K?? / 100, 1) * 60 + MOD(K??, 100)',
            'AX' => '=IF(AV?? <= AW??, AW?? - AV??, 1440 - AV?? + AW??)',
            'AY' => '=IF(AND(AU?? >= 101100, AU?? <= 101199), BI??, 0)',
            'AZ' => '=IF(AND(AU?? >= 101200, AU?? <= 101299), BI??, 0)',
            'BA' => '=FLOOR(P?? / 100, 1) * 60 + MOD(P??, 100)',
            'BB' => '=FLOOR(Q?? / 100, 1) * 60 + MOD(Q??, 100)',
            'BC' => '=FLOOR(R?? / 100, 1) * 60 + MOD(R??, 100)',
            'BD' => '=IF(AND(AU?? >= 211100, AU?? < 211199), BI??, 0)',
            'BE' => '=IF(AND(AU?? >= 111100, AU?? < 111199), BI??, 0)',
            'BF' => '=IF(AND(AU?? >= 701100, AU?? < 701199), BI??, 0)',
            'BG' => '=IF(AU?? >= 800000, BI??, 0)',
            'BH' => '=FLOOR(V?? / 100, 1) * 60 + MOD(V??, 100)',
            'BI' => '=AX?? - BH??',
            'ED' => '=IF(AND(ISBLANK(A??), ISBLANK(B??)), "", CONCATENATE(AR??, ":", YEAR(H??), ":", MONTH(H??), ":", DAY(H??), ":", J??))',
            'EE' => '=IF(AND(ISBLANK(A??), ISBLANK(B??)), "", IF(AU?? < 900000, CONCATENATE(A??, "（", F??, "）"), F??))',
        ];
        $range = range(self::SHIFT_START_ROW, self::SHIFT_START_ROW + $rows - 1);
        foreach ($values as $column => $value) {
            foreach ($range as $row) {
                $worksheet->setCellValue(Coordinate::cell($column, $row), str_replace('??', (string)$row, $value));
            }
        }
        foreach (range(0, 71) as $offset) {
            $h = Math::floor($offset / 2) * 100 + ($offset % 2 * 30);
            $value = $offset < 48
                ? str_replace(
                    '##',
                    (string)$h,
                    '=IF(OR(ISBLANK(J??), ISBLANK(K??), AND(J?? < K??, OR(J?? > ##, K?? <= ##)), AND(J?? > K??, J?? > ##)), "", 1)'
                )
                : str_replace('##', (string)($h - 2400), '=IF(OR(ISBLANK(J??), ISBLANK(K??), J?? < K??, K?? <= ##), "", 1)');
            $column = $offset + self::TIMETABLE_START_COLUMN;
            foreach ($range as $row) {
                $worksheet->setCellValue(Coordinate::cell($column, $row), str_replace('??', (string)$row, $value));
            }
        }
    }

    /**
     * シート「週間シフト」の書式を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param int $rows
     */
    private function setupShiftWorksheetStyles(Worksheet $worksheet, int $rows): void
    {
        foreach (Coordinate::columns()->take(self::COLUMNS) as $column) {
            $source = Coordinate::cell($column, self::SHIFT_START_ROW);
            $destination = Coordinate::range(
                Coordinate::cell($column, self::SHIFT_START_ROW + 1),
                Coordinate::cell($column, self::SHIFT_START_ROW + $rows - 1)
            );
            $worksheet->duplicateStyle($worksheet->getStyle($source), $destination);
        }
    }

    /**
     * シート「週間シフト」に過去の勤務シフトを元にしたShiftを設定する.
     *
     * @param Worksheet $worksheet
     * @param \Domain\User\User[]|\ScalikePHP\Seq $users
     * @param \Domain\Staff\Staff[]|\ScalikePHP\Seq $staffs
     * @param \Domain\Shift\Shift[]|\ScalikePHP\Seq $shifts
     * @param \Domain\Common\CarbonRange $range
     * @param \Domain\Common\Carbon $sourceStart
     */
    private function setupShiftWorksheetSourceBasedShift(
        Worksheet $worksheet,
        Seq $users,
        Seq $staffs,
        Seq $shifts,
        CarbonRange $range,
        Carbon $sourceStart
    ): void {
        foreach ($shifts as $index => $shift) {
            foreach ($users as $user) {
                if ($user->id === $shift->userId) {
                    $worksheet->setCellValue(
                        Coordinate::cell('A', $index + 9),
                        "{$user->name->displayName}({$user->id})"
                    );
                }
            }
            foreach ($staffs as $staff) {
                if ($staff->id === $shift->assignees[0]->staffId) {
                    $worksheet->setCellValue(
                        Coordinate::cell('B', $index + 9),
                        "{$staff->name->displayName}({$staff->employeeNumber})"
                    );
                }
            }
            $worksheet->setCellValue(Coordinate::cell('C', $index + 9), $shift->assignees[0]->isTraining ? '＊' : '');
            if ($shift->headcount === 2) {
                foreach ($staffs as $staff) {
                    if ($staff->id === $shift->assignees[1]->staffId) {
                        $worksheet->setCellValue(
                            Coordinate::cell('D', $index + 9),
                            "{$staff->name->displayName}({$staff->employeeNumber})"
                        );
                    }
                }
                $worksheet->setCellValue(
                    Coordinate::cell('E', $index + 9),
                    $shift->assignees[1]->isTraining ? '＊' : ''
                );
            }
            $worksheet->setCellValue(Coordinate::cell('F', $index + 9), Task::resolve($shift->task));
            $worksheet->setCellValue(Coordinate::cell('G', $index + 9), $shift->serviceCode->toString());
            $diffDays = $sourceStart->diffInDays($shift->schedule->date);
            $shiftDate = $range->start->addDays($diffDays);
            if ($range->end < $shiftDate) {
                throw new InvalidArgumentException('rangeとsourceの日数が合っていない');
            }
            $worksheet->setCellValue(
                Coordinate::cell('H', $index + self::SHIFT_START_ROW),
                Date::PHPToExcel($shiftDate)
            );
            $worksheet->getStyle(Coordinate::cell('H', $index + self::SHIFT_START_ROW))
                ->getNumberFormat()
                ->setFormatCode('m月d日;@');
            $worksheet->setCellValue(Coordinate::cell('J', $index + 9), (int)$shift->schedule->start->format('Gi'));
            $worksheet->setCellValue(Coordinate::cell('K', $index + 9), (int)$shift->schedule->end->format('Gi'));
            foreach ($shift->options as $option) {
                if ($option->equals(ServiceOption::notificationEnabled())) {
                    $worksheet->setCellValue(Coordinate::cell('X', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::oneOff())) {
                    $worksheet->setCellValue(Coordinate::cell('Y', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::firstTime())) {
                    $worksheet->setCellValue(Coordinate::cell('Z', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::emergency())) {
                    $worksheet->setCellValue(Coordinate::cell('AA', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::sucking())) {
                    $worksheet->setCellValue(Coordinate::cell('AB', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::welfareSpecialistCooperation())) {
                    $worksheet->setCellValue(Coordinate::cell('AC', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::plannedByNovice())) {
                    $worksheet->setCellValue(Coordinate::cell('AD', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::providedByBeginner())) {
                    $worksheet->setCellValue(Coordinate::cell('AE', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::providedByCareWorkerForPwsd())) {
                    $worksheet->setCellValue(Coordinate::cell('AF', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::over20())) {
                    $worksheet->setCellValue(Coordinate::cell('AG', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::over50())) {
                    $worksheet->setCellValue(Coordinate::cell('AH', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::behavioralDisorderSupportCooperation())) {
                    $worksheet->setCellValue(Coordinate::cell('AI', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::hospitalized())) {
                    $worksheet->setCellValue(Coordinate::cell('AJ', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::longHospitalized())) {
                    $worksheet->setCellValue(Coordinate::cell('AK', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::coaching())) {
                    $worksheet->setCellValue(Coordinate::cell('AL', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::vitalFunctionsImprovement1())) {
                    $worksheet->setCellValue(Coordinate::cell('AM', $index + 9), '＊');
                } elseif ($option->equals(ServiceOption::vitalFunctionsImprovement2())) {
                    $worksheet->setCellValue(Coordinate::cell('AN', $index + 9), '＊');
                }
            }
            foreach ($staffs as $staff) {
                if ($staff->id === $shift->assignerId) {
                    $worksheet->setCellValue(
                        Coordinate::cell('AO', $index + 9),
                        "{$staff->name->displayName}({$staff->employeeNumber})"
                    );
                }
            }
        }
    }

    /**
     * シート「スケジュール」を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return void
     */
    private function setupScheduleWorksheet(Spreadsheet $spreadsheet): void
    {
        $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SCHEDULE);
        $this->setupScheduleWorksheetConditionalStyles($worksheet, self::SCHEDULE_ROWS);
        $this->setupScheduleWorksheetDataValidation($worksheet, self::SCHEDULE_ROWS);
        $this->setupScheduleWorksheetFormulas($worksheet, self::SCHEDULE_ROWS);
        $this->setupScheduleWorksheetStyles($worksheet, self::SCHEDULE_ROWS);
    }

    /**
     * シート「スケジュール」の条件付き書式を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param int $rows
     * @return void
     */
    private function setupScheduleWorksheetConditionalStyles(Worksheet $worksheet, int $rows): void
    {
        $endRow = self::SCHEDULE_START_ROW + $rows - 1;
        $styles = [
            'D2:AY2' => Coordinate::range('D2', Coordinate::cell('AY', $endRow)),
        ];
        foreach ($styles as $source => $destination) {
            $worksheet->setConditionalStyles($destination, $worksheet->getConditionalStyles($source));
            $worksheet->removeConditionalStyles($source);
        }
    }

    /**
     * シート「スケジュール」の書式を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param int $rows
     * @return void
     */
    private function setupScheduleWorksheetDataValidation(Worksheet $worksheet, int $rows): void
    {
        $startRow = self::SCHEDULE_START_ROW;
        $endRow = self::SCHEDULE_START_ROW + $rows - 1;
        $column = 'A';
        $formula = 'スタッフ一覧!A:A';

        $destination = Coordinate::range(
            Coordinate::cell($column, $startRow),
            Coordinate::cell($column, $endRow)
        );
        $this->setupDropDownValidation($worksheet, $destination, $formula);
    }

    /**
     * シート「スケジュール」の各セルに値を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param int $rows
     * @return void
     */
    private function setupScheduleWorksheetFormulas(Worksheet $worksheet, int $rows): void
    {
        $values = [
            'C' => '=IF(B??, B??, "")',
            'D' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!AV:AV, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CR:CR, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'E' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!AW:AW, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CS:CS, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'F' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!AX:AX, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CT:CT, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'G' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!AY:AY, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CU:CU, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'H' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!AZ:AZ, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CV:CV, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'I' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BA:BA, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CW:CW, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'J' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BB:BB, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CX:CX, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'K' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BC:BC, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CY:CY, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'L' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BD:BD, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!CZ:CZ, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'M' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BE:BE, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DA:DA, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'N' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BF:BF, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DB:DB, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'O' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BG:BG, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DC:DC, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'P' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BH:BH, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DD:DD, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'Q' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BI:BI, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DE:DE, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'R' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BJ:BJ, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DF:DF, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'S' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BK:BK, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DG:DG, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'T' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BL:BL, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DH:DH, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'U' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BM:BM, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DI:DI, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'V' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BN:BN, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DJ:DJ, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'W' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BO:BO, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DK:DK, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'X' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BP:BP, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DL:DL, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'Y' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BQ:BQ, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DM:DM, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'Z' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BR:BR, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DN:DN, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AA' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BS:BS, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DO:DO, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AB' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BT:BT, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DP:DP, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AC' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BU:BU, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DQ:DQ, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AD' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BV:BV, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DR:DR, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AE' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BW:BW, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DS:DS, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AF' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BX:BX, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DT:DT, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AG' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BY:BY, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DU:DU, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AH' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!BZ:BZ, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DV:DV, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AI' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CA:CA, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DW:DW, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AJ' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CB:CB, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DX:DX, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AK' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CC:CC, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DY:DY, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AL' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CD:CD, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!DZ:DZ, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AM' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CE:CE, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EA:EA, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AN' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CF:CF, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EB:EB, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AO' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CG:CG, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EC:EC, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AP' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CH:CH, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!ED:ED, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AQ' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CI:CI, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EE:EE, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AR' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CJ:CJ, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EF:EF, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AS' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CK:CK, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EG:EG, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AT' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CL:CL, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EH:EH, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AU' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CM:CM, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EI:EI, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AV' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CN:CN, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EJ:EJ, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AW' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CO:CO, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EK:EK, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AX' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CP:CP, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EL:EL, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AY' => '=IF(COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B??, 週間シフト!CQ:CQ, 1) + COUNTIFS(週間シフト!$B:$B, $A??, 週間シフト!$H:$H, $B?? - 1, 週間シフト!EM:EM, 1) > 0, IFERROR(VLOOKUP(CONCATENATE($AZ??, ":", FLOOR((COLUMN() - 4) / 2, 1) * 100 + MOD(COLUMN(), 2) * 30), 週間シフト!$DP:$DQ, 2, FALSE), 0), "")',
            'AZ' => '=CONCATENATE(VLOOKUP(A??, スタッフ一覧!A:D, 4, FALSE), ":",  YEAR(B??), ":",  MONTH(B??), ":",  DAY(B??))',
        ];
        $range = range(self::SCHEDULE_START_ROW, self::SCHEDULE_START_ROW + $rows - 1);
        foreach ($values as $column => $value) {
            foreach ($range as $row) {
                $worksheet->setCellValue(Coordinate::cell($column, $row), str_replace('??', (string)$row, $value));
            }
        }
    }

    /**
     * シート「スケジュール」の書式を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param int $rows
     * @return void
     */
    private function setupScheduleWorksheetStyles(Worksheet $worksheet, int $rows): void
    {
        foreach (Coordinate::columns()->take(self::COLUMNS) as $column) {
            $source = Coordinate::cell($column, self::SCHEDULE_START_ROW);
            $destination = Coordinate::range(
                Coordinate::cell($column, self::SCHEDULE_START_ROW + 1),
                Coordinate::cell($column, self::SCHEDULE_START_ROW + $rows - 1)
            );
            $worksheet->duplicateStyle($worksheet->getStyle($source), $destination);
        }
    }

    /**
     * シート「事業所」を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param \Domain\Office\Office[]|\ScalikePHP\Seq $offices
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return void
     */
    private function setupOfficeWorksheet(Spreadsheet $spreadsheet, Seq $offices): void
    {
        $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_OFFICE);
        foreach ($offices as $index => $office) {
            $worksheet->setCellValue(Coordinate::cell(1, $index + 1), $office->name);
            $worksheet->setCellValue(Coordinate::cell(2, $index + 1), $office->id);
        }
    }

    /**
     * シート「利用者一覧」を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param \Domain\User\User[]|\ScalikePHP\Seq $users
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return void
     */
    private function setupUserWorksheet(Spreadsheet $spreadsheet, Seq $users): void
    {
        $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_USER);
        foreach ($users as $index => $user) {
            $worksheet->setCellValue(Coordinate::cell(1, $index + 1), "{$user->name->displayName}({$user->id})");
            $worksheet->setCellValue(Coordinate::cell(2, $index + 1), $user->name->familyName);
            $worksheet->setCellValue(Coordinate::cell(3, $index + 1), $user->name->givenName);
            $worksheet->setCellValue(Coordinate::cell(4, $index + 1), $user->id);
        }
    }

    /**
     * シート「スタッフ一覧」を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param \Domain\Staff\Staff[]|\ScalikePHP\Seq $staffs
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return void
     */
    private function setupStaffWorksheet(Spreadsheet $spreadsheet, Seq $staffs): void
    {
        $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_STAFF);
        foreach ($staffs as $index => $staff) {
            $worksheet->setCellValue(
                Coordinate::cell(1, $index + 1),
                "{$staff->name->displayName}({$staff->employeeNumber})"
            );
            $worksheet->setCellValue(Coordinate::cell(2, $index + 1), $staff->name->familyName);
            $worksheet->setCellValue(Coordinate::cell(3, $index + 1), $staff->name->givenName);
            $worksheet->setCellValue(Coordinate::cell(4, $index + 1), $staff->id);
        }
    }

    /**
     * シート「勤務区分」を設定する.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return void
     */
    private function setupTaskWorksheet(Spreadsheet $spreadsheet): void
    {
        $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_TASK);
        foreach (Task::all() as $index => $task) {
            $worksheet->setCellValue(Coordinate::cell(1, $index + 1), Task::resolve($task));
            $worksheet->setCellValue(Coordinate::cell(2, $index + 1), $task->key());
            $worksheet->setCellValue(Coordinate::cell(3, $index + 1), $task->value());
        }
    }
}
