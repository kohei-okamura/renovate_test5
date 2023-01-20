<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\Config\Config;
use Domain\Context\Context;
use Domain\File\FileStorage;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\Task;
use Domain\Spreadsheet\Coordinate;
use Infrastructure\File\TemporaryFilesImpl;
use Lib\Exceptions\RuntimeException;
use Lib\RandomString;
use Mockery;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use SplFileInfo;
use Tests\Unit\App\Http\Concretes\TestingContext;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\FindOfficeUseCaseMixin;
use Tests\Unit\Mixins\FindShiftUseCaseMixin;
use Tests\Unit\Mixins\FindStaffUseCaseMixin;
use Tests\Unit\Mixins\FindUserUseCaseMixin;
use Tests\Unit\Mixins\TemporaryFilesMixin;
use Tests\Unit\Test;
use UseCase\Office\FindOfficeUseCase;
use UseCase\Shift\FindShiftUseCase;
use UseCase\Shift\GenerateShiftTemplateInteractor;
use UseCase\Staff\FindStaffUseCase;
use UseCase\User\FindUserUseCase;

/**
 * {@link \UseCase\Shift\GenerateShiftTemplateInteractor} Test.
 *
 * 実際に生成された Xlsxファイル の中身を検証
 */
class GenerateShiftTemplateInteractorSpreadsheetTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use FindOfficeUseCaseMixin;
    use FindShiftUseCaseMixin;
    use FindStaffUseCaseMixin;
    use FindUserUseCaseMixin;
    use TemporaryFilesMixin;
    use UnitSupport;

    private const SHIFT_START_ROW = 9;
    private const SCHEDULE_START_ROW = 2;

    private const SHIFT_ROWS = 30;
    private const SCHEDULE_ROWS = 30;

    private const SHEET_INDEX_SHIFT = 0;
    private const SHEET_INDEX_SCHEDULE = 1;
    private const SHEET_INDEX_OFFICE = 2;
    private const SHEET_INDEX_USER = 3;
    private const SHEET_INDEX_STAFF = 4;
    private const SHEET_INDEX_TASK = 5;

    private GenerateShiftTemplateInteractor $interactor;
    private CarbonRange $range;
    private Pagination $pagination;
    private Seq $findShifts;
    private string $path;
    private array $parameters;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (GenerateShiftTemplateInteractorSpreadsheetTest $self): void {
            $self->pagination = Pagination::create();

            $self->context = Mockery::mock(TestingContext::class)->makePartial();
            // テストに共通なデータ（Xlsxファイル）を生成する
            $self->config = Mockery::mock(Config::class);
            $self->config
                ->expects('get')
                ->with('zinger.path.resources.spreadsheets')
                ->andReturn(base_path('resources/spreadsheets'));
            $self->fileStorage = Mockery::mock(FileStorage::class);
            $self->fileStorage
                ->allows('store')
                ->andReturn(Option::some('/path/to/file'))
                ->byDefault();
            $self->findOfficeUseCase = Mockery::mock(FindOfficeUseCase::class);
            $self->findOfficeUseCase
                ->expects('handle')
                ->with($self->context, [Permission::createShifts()], [], ['all' => true])
                ->andReturn(FinderResult::from($self->examples->offices, $self->pagination));
            $self->findUserUseCase = Mockery::mock(FindUserUseCase::class);
            $self->findUserUseCase
                ->expects('handle')
                ->with(
                    $self->context,
                    Permission::createShifts(),
                    ['officeId' => $self->examples->offices[0]->id],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($self->examples->users, $self->pagination));
            $self->findStaffUseCase = Mockery::mock(FindStaffUseCase::class);
            $self->findStaffUseCase
                ->expects('handle')
                ->with(
                    $self->context,
                    Permission::createShifts(),
                    ['officeId' => $self->examples->offices[0]->id],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($self->examples->staffs, $self->pagination));
            $self->findShiftUseCase = Mockery::mock(FindShiftUseCase::class);
            $self->findShiftUseCase
                ->expects('handle')
                ->andReturnUsing(function (
                    Context $context,
                    Permission $permission,
                    array $filterParams,
                    array $paginationParams
                ) use ($self): FinderResult {
                    return FinderResult::from($self->findShifts, Pagination::create());
                });
            $dir = sys_get_temp_dir();
            $prefix = 'zinger-';
            $suffix = '.xlsx';
            $self->path = RandomString::seq(16)
                ->map(fn (string $name): string => $dir . '/' . $prefix . $name . $suffix)
                ->find(fn (string $path): bool => !file_exists($path))
                ->getOrElse(function (): void {
                    throw new RuntimeException('Failed to create temporary file');
                });
            touch($self->path);
            chmod($self->path, 0600);
            $self->temporaryFiles = Mockery::mock(TemporaryFilesImpl::class);
            $self->temporaryFiles
                ->allows('create')
                ->andReturn(new SplFileInfo($self->path))
                ->byDefault();

            $self->findShifts = Seq::fromArray($self->examples->shifts)
                ->filter(fn (Shift $x): bool => $x->schedule->date >= $self->parameters['scheduleDateAfter'])
                ->filter(fn (Shift $x): bool => $x->schedule->date <= $self->parameters['scheduleDateBefore']);
            $self->parameters = [
                'officeId' => $self->examples->offices[0]->id,
                'scheduleDateAfter' => Carbon::parse('2040-01-01'),
                'scheduleDateBefore' => Carbon::parse('2040-05-01'),
            ];
            $self->range = CarbonRange::create([
                'start' => Carbon::parse('2041-01-01'),
                'end' => Carbon::parse('2041-05-01'),
            ]);

            $self->interactor = app(GenerateShiftTemplateInteractor::class);
            $self->assertSame(
                '/path/to/file',
                $self->interactor->handle($self->context, $self->range, true, $self->parameters)
            );
        });
        static::beforeEachSpec(function (GenerateShiftTemplateInteractorSpreadsheetTest $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('set an office name to the cell in the shift sheet', function () {
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);
            $targetCell = 'B2';
            $officeName = $worksheet->getCell($targetCell)->getValue();
            $this->assertSame($this->examples->offices[0]->name, $officeName);
        });
        $this->should('set an officeId to the cell in the shift sheet', function () {
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);
            $targetCell = 'B3';
            $officeId = $worksheet->getCell($targetCell)->getCalculatedValue();
            $this->assertSame($this->examples->offices[0]->id, $officeId);
        });
        $this->should('set formulas to the cells in the shift sheet', function () {
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);
            $targetCells = Coordinate::range(
                Coordinate::cell('I', self::SHIFT_START_ROW),
                Coordinate::cell('DR', self::SHIFT_START_ROW + self::SHIFT_ROWS - 1)
            );
            $actualFormulas = $worksheet->rangeToArray($targetCells);
            foreach ($actualFormulas as $row => $actualFormula) {
                $this->assertNotNull($actualFormula[0], 'cell ' . Coordinate::cell('I', self::SHIFT_START_ROW + $row));
                $this->assertNotNull($actualFormula[3], 'cell ' . Coordinate::cell('L', self::SHIFT_START_ROW + $row));
                $this->assertNotNull($actualFormula[4], 'cell ' . Coordinate::cell('M', self::SHIFT_START_ROW + $row));
                $this->assertNotNull($actualFormula[5], 'cell ' . Coordinate::cell('N', self::SHIFT_START_ROW + $row));
                $this->assertNotNull($actualFormula[6], 'cell ' . Coordinate::cell('O', self::SHIFT_START_ROW + $row));
                $this->assertNotNull($actualFormula[10], 'cell ' . Coordinate::cell('S', self::SHIFT_START_ROW + $row));
                $this->assertNotNull($actualFormula[11], 'cell ' . Coordinate::cell('T', self::SHIFT_START_ROW + $row));
                $this->assertNotNull($actualFormula[12], 'cell ' . Coordinate::cell('U', self::SHIFT_START_ROW + $row));
                $this->assertNotNull($actualFormula[14], 'cell ' . Coordinate::cell('W', self::SHIFT_START_ROW + $row));
                for ($index = 34, $col = 'AQ'; $index <= 113; $index++, $col++) {
                    $this->assertNotNull(
                        $actualFormula[$index],
                        'cell ' . Coordinate::cell($col, self::SHIFT_START_ROW + $row)
                    );
                }
            }
        });
        $this->should('set past shifts to the cells in the shift sheet', function () {
            $exampleShifts = $this->findShifts->toArray();
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);
            $targetCells = Coordinate::range(
                Coordinate::cell('A', self::SHIFT_START_ROW),
                Coordinate::cell('AO', self::SHIFT_START_ROW + count($exampleShifts) - 1)
            );
            $actualShifts = $worksheet->rangeToArray($targetCells);
            foreach ($actualShifts as $index => $actualShift) {
                foreach ($this->examples->users as $user) {
                    if ($user->id === $exampleShifts[$index]->userId) {
                        $expectedUserName = "{$user->name->displayName}({$user->id})";
                        $this->assertSame($expectedUserName, $actualShift[0]);
                    }
                }

                foreach ($this->examples->staffs as $staff) {
                    if ($exampleShifts[$index]->assignees[0]->staffId === $staff->id) {
                        $expectedAssigneeName1 = "{$staff->name->displayName}({$staff->employeeNumber})";
                        $this->assertSame($expectedAssigneeName1, $actualShift[1]);
                    }
                }

                $expectedIsTraining1 = $exampleShifts[$index]->assignees[0]->isTraining;
                $this->assertSame($expectedIsTraining1 ? '＊' : null, $actualShift[2]);

                if (array_key_exists(1, $exampleShifts[$index]->assignees)) {
                    foreach ($this->examples->staffs as $staff) {
                        if ($exampleShifts[$index]->assignees[1]->staffId === $staff->id) {
                            $expectedAssigneeName2 = "{$staff->name->displayName}({$staff->employeeNumber})";
                            $this->assertSame($expectedAssigneeName2, $actualShift[3]);
                        }
                    }

                    $expectedIsTraining2 = $exampleShifts[$index]->assignees[1]->isTraining;
                    $this->assertSame($expectedIsTraining2 ? '＊' : null, $actualShift[4]);
                }

                $expectedTaskName = Task::resolve($exampleShifts[$index]->task);
                $this->assertSame($expectedTaskName, $actualShift[5]);

                $expectedServiceCode = $exampleShifts[$index]->serviceCode;
                $this->assertModelStrictEquals($expectedServiceCode, ServiceCode::fromString((string)$actualShift[6]));

                $expectedScheduleDate = $this->range->start->addDays(
                    $this->parameters['scheduleDateAfter']->diffInDays(
                        $exampleShifts[$index]->schedule->date
                    )
                )
                    ->format('n月j日');
                $this->assertSame($expectedScheduleDate, $actualShift[7]);

                $expectedScheduleStart = $exampleShifts[$index]->schedule->start->format('G:i');
                $this->assertSame($expectedScheduleStart, $actualShift[9]);

                $expectedScheduleEnd = $exampleShifts[$index]->schedule->end->format('G:i');
                $this->assertSame($expectedScheduleEnd, $actualShift[10]);

                foreach ($exampleShifts[$index]->options as $option) {
                    if ($option->equals(ServiceOption::notificationEnabled())) {
                        $this->assertSame('＊', $actualShift[23]);
                    } elseif ($option->equals(ServiceOption::oneOff())) {
                        $this->assertSame('＊', $actualShift[24]);
                    } elseif ($option->equals(ServiceOption::firstTime())) {
                        $this->assertSame('＊', $actualShift[25]);
                    } elseif ($option->equals(ServiceOption::emergency())) {
                        $this->assertSame('＊', $actualShift[26]);
                    } elseif ($option->equals(ServiceOption::sucking())) {
                        $this->assertSame('＊', $actualShift[27]);
                    } elseif ($option->equals(ServiceOption::welfareSpecialistCooperation())) {
                        $this->assertSame('＊', $actualShift[28]);
                    } elseif ($option->equals(ServiceOption::plannedByNovice())) {
                        $this->assertSame('＊', $actualShift[29]);
                    } elseif ($option->equals(ServiceOption::providedByBeginner())) {
                        $this->assertSame('＊', $actualShift[30]);
                    } elseif ($option->equals(ServiceOption::providedByCareWorkerForPwsd())) {
                        $this->assertSame('＊', $actualShift[31]);
                    } elseif ($option->equals(ServiceOption::over20())) {
                        $this->assertSame('＊', $actualShift[32]);
                    } elseif ($option->equals(ServiceOption::over50())) {
                        $this->assertSame('＊', $actualShift[33]);
                    } elseif ($option->equals(ServiceOption::behavioralDisorderSupportCooperation())) {
                        $this->assertSame('＊', $actualShift[34]);
                    } elseif ($option->equals(ServiceOption::hospitalized())) {
                        $this->assertSame('＊', $actualShift[35]);
                    } elseif ($option->equals(ServiceOption::longHospitalized())) {
                        $this->assertSame('＊', $actualShift[36]);
                    } elseif ($option->equals(ServiceOption::coaching())) {
                        $this->assertSame('＊', $actualShift[37]);
                    } elseif ($option->equals(ServiceOption::vitalFunctionsImprovement1())) {
                        $this->assertSame('＊', $actualShift[38]);
                    } elseif ($option->equals(ServiceOption::vitalFunctionsImprovement2())) {
                        $this->assertSame('＊', $actualShift[39]);
                    }
                }

                foreach ($this->examples->staffs as $staff) {
                    if ($exampleShifts[$index]->assignerId === $staff->id) {
                        $expectedAssignerName = "{$staff->name->displayName}({$staff->employeeNumber})";
                        $this->assertSame($expectedAssignerName, $actualShift[40]);
                    }
                }
            }
        });
        $this->should('set office names and ids to the cells in the office sheet', function () {
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_OFFICE);
            $targetCells = Coordinate::range('A1', Coordinate::cell('B', count($this->examples->offices)));
            $actualOffices = $worksheet->rangeToArray($targetCells);
            foreach ($this->examples->offices as $index => $expectedOffice) {
                $actualOffice = $actualOffices[$index];
                $this->assertSame($expectedOffice->name, $actualOffice[0]);
                $this->assertSame($expectedOffice->id, $actualOffice[1]);
            }
        });
        $this->should('set user names and ids to the cells in the user sheet', function () {
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_USER);
            $targetCells = Coordinate::range('A1', Coordinate::cell('D', count($this->examples->users)));
            $actualUsers = $worksheet->rangeToArray($targetCells);
            foreach ($this->examples->users as $index => $expectedUser) {
                $actualUser = $actualUsers[$index];
                $this->assertSame("{$expectedUser->name->displayName}({$expectedUser->id})", $actualUser[0]);
                $this->assertSame($expectedUser->name->familyName, $actualUser[1]);
                $this->assertSame($expectedUser->name->givenName, $actualUser[2]);
                $this->assertSame($expectedUser->id, $actualUser[3]);
            }
        });
        $this->should('set staff names and ids to the cells in the staff sheet', function () {
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_STAFF);
            $targetCells = Coordinate::range('A1', Coordinate::cell('D', count($this->examples->staffs)));
            $actualStaffs = $worksheet->rangeToArray($targetCells);
            foreach ($this->examples->staffs as $index => $expectedStaff) {
                $actualStaff = $actualStaffs[$index];
                $this->assertSame(
                    "{$expectedStaff->name->displayName}({$expectedStaff->employeeNumber})",
                    $actualStaff[0]
                );
                $this->assertSame($expectedStaff->name->familyName, $actualStaff[1]);
                $this->assertSame($expectedStaff->name->givenName, $actualStaff[2]);
                $this->assertSame($expectedStaff->id, $actualStaff[3]);
            }
        });
        $this->should('set task names, labels and service codes to the cells in the task sheet', function () {
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_TASK);
            $targetCells = Coordinate::range('A1', Coordinate::cell('C', count(Task::all())));
            $actualTasks = $worksheet->rangeToArray($targetCells);
            foreach (Task::all() as $index => $expectedTask) {
                $actualTask = $actualTasks[$index];
                $this->assertSame(Task::resolve($expectedTask), $actualTask[0]);
                $this->assertSame($expectedTask->key(), $actualTask[1]);
                $this->assertSame($expectedTask->value(), $actualTask[2]);
            }
        });
        $this->should('set terms equal to range', function (): void {
            $spreadsheet = (new XlsxReader())->load($this->path);
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);

            $this->assertSame(
                $this->range->start->toDateString(),
                $worksheet->getCell('B4')->getCalculatedValue(),
            );
            $this->assertSame(
                $this->range->end->toDateString(),
                $worksheet->getCell('B5')->getCalculatedValue(),
            );
        });
    }
}
