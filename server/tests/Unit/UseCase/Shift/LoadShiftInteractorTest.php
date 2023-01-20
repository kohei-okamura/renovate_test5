<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Common\ServiceSegment;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\Task;
use Lib\Exceptions\LogicException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\ImportShiftAsyncValidatorMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Shift\LoadShiftInteractor;

/**
 * LoadShiftInteractor のテスト.
 */
class LoadShiftInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use ImportShiftAsyncValidatorMixin;
    use LookupStaffUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private const SHEET_INDEX_SHIFT = 0;
    private const VALID_SHIFTS_FILE = 'Shift/valid-shifts.xlsx';

    private LoadShiftInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LoadShiftInteractorTest $self): void {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->importShiftAsyncValidator
                ->allows('validate')
                ->andReturnNull()
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with($self->context, Permission::createShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->with($self->context, Permission::createShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $self->interactor = app(LoadShiftInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use IdentifyContractUseCase', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createShifts(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createShifts(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    ServiceSegment::longTermCare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createShifts(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));
            $spreadsheet = (new XlsxReader())->load(codecept_data_dir(self::VALID_SHIFTS_FILE));
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);

            $this->interactor->handle($this->context, $worksheet)->toArray();
        });
        $this->should(
            'throw LogicException when IdentifyContractUseCase return none',
            function () {
                $this->identifyContractUseCase
                    ->expects('handle')
                    ->andReturn(Option::none());
                $spreadsheet = (new XlsxReader())->load(codecept_data_dir(self::VALID_SHIFTS_FILE));
                $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);

                $this->assertThrows(LogicException::class, function () use ($worksheet): void {
                    $this->interactor->handle($this->context, $worksheet)->toArray();
                });
            }
        );
        $this->should(
            'return array of Shift when pass validation',
            function () {
                $spreadsheet = (new XlsxReader())->load(codecept_data_dir(self::VALID_SHIFTS_FILE));
                $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);

                $expected = $this->expectedShifts()->toArray();
                $actual = $this->interactor->handle($this->context, $worksheet)->toArray();
                $this->assertEach(function ($expectedValue, $actualValue): void {
                    $this->assertModelStrictEquals($expectedValue, $actualValue);
                }, $expected, $actual);
            }
        );
        $this->should('use validate on ImportShiftAsyncValidator', function (): void {
            $this->importShiftAsyncValidator
                ->expects('validate')
                ->andReturnNull()
                ->byDefault();
            $spreadsheet = (new XlsxReader())->load(codecept_data_dir(self::VALID_SHIFTS_FILE));
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);

            $this->interactor->handle($this->context, $worksheet);
        });
    }

    /**
     * Excel から取得できるはずの勤務シフトデータ.
     *
     * @return \ScalikePHP\Seq
     */
    private function expectedShifts(): Seq
    {
        return Seq::fromArray([
            Shift::create([
                'contractId' => 1,
                'officeId' => 1,
                'userId' => 1,
                'assignerId' => 11,
                'task' => Task::dwsVisitingCareForPwsd(),
                'serviceCode' => null,
                'headcount' => 2,
                'assignees' => [
                    Assignee::create([
                        'staffId' => 11,
                        'isUndecided' => false,
                        'isTraining' => false,
                    ]),
                    Assignee::create([
                        'staffId' => 12,
                        'isUndecided' => false,
                        'isTraining' => true,
                    ]),
                ],
                'schedule' => Schedule::create([
                    'date' => Carbon::parse('2041-04-01'),
                    'start' => Carbon::parse('2041-04-01T10:00:00+0900'),
                    'end' => Carbon::parse('2041-04-01T11:00:00+0900'),
                ]),
                'durations' => [
                    Duration::create([
                        'activity' => Activity::dwsVisitingCareForPwsd(),
                        'duration' => 50,
                    ]),
                    Duration::create([
                        'activity' => Activity::resting(),
                        'duration' => 10,
                    ]),
                ],
                'options' => [
                    ServiceOption::behavioralDisorderSupportCooperation(),
                    ServiceOption::hospitalized(),
                    ServiceOption::longHospitalized(),
                    ServiceOption::coaching(),
                ],
                'note' => '特になし',
                'isConfirmed' => false,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
            Shift::create([
                'contractId' => 1,
                'officeId' => 1,
                'userId' => 1,
                'assignerId' => 11,
                'task' => Task::ltcsPhysicalCare(),
                'serviceCode' => ServiceCode::fromString('111111'),
                'headcount' => 1,
                'assignees' => [
                    Assignee::create([
                        'staffId' => 14,
                        'isUndecided' => false,
                        'isTraining' => false,
                    ]),
                ],
                'schedule' => Schedule::create([
                    'date' => Carbon::parse('2041-04-10'),
                    'start' => Carbon::parse('2041-04-10T11:00:00+0900'),
                    'end' => Carbon::parse('2041-04-10T12:00:00+0900'),
                ]),
                'durations' => [
                    Duration::create([
                        'activity' => Activity::ltcsPhysicalCare(),
                        'duration' => 60,
                    ]),
                ],
                'options' => [
                    ServiceOption::vitalFunctionsImprovement1(),
                    ServiceOption::vitalFunctionsImprovement2(),
                ],
                'note' => '',
                'isConfirmed' => false,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
            Shift::create([
                'contractId' => 1,
                'officeId' => 1,
                'userId' => 1,
                'assignerId' => 13,
                'task' => Task::dwsAccompanyWithPhysicalCare(),
                'serviceCode' => null,
                'headcount' => 1,
                'assignees' => [
                    Assignee::create([
                        'staffId' => 12,
                        'isUndecided' => false,
                        'isTraining' => false,
                    ]),
                ],
                'schedule' => Schedule::create([
                    'date' => Carbon::parse('2041-04-11'),
                    'start' => Carbon::parse('2041-04-11T12:00:00+0900'),
                    'end' => Carbon::parse('2041-04-11T13:00:00+0900'),
                ]),
                'durations' => [
                    Duration::create([
                        'activity' => Activity::dwsAccompanyWithPhysicalCare(),
                        'duration' => 60,
                    ]),
                ],
                'options' => [
                    ServiceOption::notificationEnabled(),
                    ServiceOption::oneOff(),
                    ServiceOption::firstTime(),
                    ServiceOption::emergency(),
                    ServiceOption::sucking(),
                    ServiceOption::welfareSpecialistCooperation(),
                    ServiceOption::plannedByNovice(),
                    ServiceOption::providedByBeginner(),
                    ServiceOption::providedByCareWorkerForPwsd(),
                    ServiceOption::over20(),
                    ServiceOption::over50(),
                ],
                'note' => '',
                'isConfirmed' => false,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
            Shift::create([
                'contractId' => null,
                'officeId' => 1,
                'userId' => null,
                'assignerId' => 11,
                'task' => Task::meeting(),
                'serviceCode' => null,
                'headcount' => 1,
                'assignees' => [
                    Assignee::create([
                        'staffId' => 11,
                        'isUndecided' => false,
                        'isTraining' => false,
                    ]),
                ],
                'schedule' => Schedule::create([
                    'date' => Carbon::parse('2041-04-30'),
                    'start' => Carbon::parse('2041-04-30T14:00:00+0900'),
                    'end' => Carbon::parse('2041-04-30T15:00:00+0900'),
                ]),
                'durations' => [
                    Duration::create([
                        'activity' => Activity::meeting(),
                        'duration' => 60,
                    ]),
                ],
                'options' => [],
                'note' => '',
                'isConfirmed' => false,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
        ]);
    }
}
