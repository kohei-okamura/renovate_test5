<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations;

use App\Validations\ImportShiftAsyncValidatorImpl;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Shift\Task;
use Illuminate\Support\Arr;
use Lib\Exceptions\ValidationException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\ImportShiftAsyncValidatorImpl} のテスト.
 */
final class ImportShiftAsyncValidatorImplTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupUserUseCaseMixin;
    use UnitSupport;

    private ImportShiftAsyncValidatorImpl $validator;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ImportShiftAsyncValidatorImplTest $self): void {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->with(\Mockery::any(), \Mockery::any(), \Mockery::any(), 2, \Mockery::any(), \Mockery::any())
                ->andReturn(Option::none())
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with($self->context, [Permission::createShifts()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
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

            $self->validator = app(ImportShiftAsyncValidatorImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validate(): void
    {
        $this->should('not throw ValidationException when the data passes the validation rules', function (): void {
            $this->validator->validate($this->context, $this->defaultInput());
        });

        $examples = [
            'when officeId is empty' => [
                ['「事業所名」は入力してください。'],
                ['officeId' => ''],
                ['officeId' => 1],
            ],
            'when an unknown officeId given' => [
                ['「事業所名」は正しい値を入力してください。'],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => 1],
            ],
            'when isTraining1 is not `＊`' => [
                ['「研修: 1」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.isTraining1' => '#'],
                ['shifts.0.isTraining1' => '＊'],
            ],
            'when isTraining2 is not `＊`' => [
                ['「研修: 2」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.isTraining2' => '#'],
                ['shifts.0.isTraining2' => '＊'],
            ],
            'when serviceCode is not string' => [
                ['「サービスコード」は文字列で入力してください。（行番号9）'],
                ['shifts.0.serviceCode' => 123456],
                ['shifts.0.serviceCode' => '123456'],
            ],
            'when serviceCode is longer than 6' => [
                ['「サービスコード」は6文字以内で入力してください。（行番号9）'],
                ['shifts.0.serviceCode' => '1234567'],
                ['shifts.0.serviceCode' => '123456'],
            ],
            'when serviceCode is not the specified format' => [
                ['「サービスコード」は書式が正しくありません。（行番号9）'],
                ['shifts.0.serviceCode' => 'foobar'],
                ['shifts.0.serviceCode' => '123456'],
            ],
            'when date is empty' => [
                ['「日付」は入力してください。（行番号9）'],
                ['shifts.0.date' => ''],
                ['shifts.0.date' => 51136],
            ],
            'when date is not an excel timestamp' => [
                ['「日付」は正しい日付を入力してください。（行番号9）'],
                ['shifts.0.date' => Carbon::yesterday()->format('Y/m/d')],
                ['shifts.0.date' => 51136],
            ],
            'when date is not an excel timestamp before today' => [
                ['「日付」は今日以降の日付を入力してください。（行番号9）'],
                ['shifts.0.date' => 1136],
                ['shifts.0.date' => 51136],
            ],
            'when notificationEnabled is not `＊`' => [
                ['「通知」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.notificationEnabled' => '#'],
                ['shifts.0.notificationEnabled' => '＊'],
            ],
            'when oneOff is not `＊`' => [
                ['「単発」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.oneOff' => '#'],
                ['shifts.0.oneOff' => '＊'],
            ],
            'when firstTime is not `＊`' => [
                ['「初回」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.firstTime' => '#'],
                ['shifts.0.firstTime' => '＊'],
            ],
            'when emergency is not `＊`' => [
                ['「緊急時対応」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.emergency' => '#'],
                ['shifts.0.emergency' => '＊'],
            ],
            'when sucking is not `＊`' => [
                ['「喀痰吸引」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.sucking' => '#'],
                ['shifts.0.sucking' => '＊'],
            ],
            'when welfareSpecialistCooperation is not `＊`' => [
                ['「福祉専門職員等連携」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.welfareSpecialistCooperation' => '#'],
                ['shifts.0.welfareSpecialistCooperation' => '＊'],
            ],
            'when plannedByNovice is not `＊`' => [
                ['「初計」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.plannedByNovice' => '#'],
                ['shifts.0.plannedByNovice' => '＊'],
            ],
            'when providedByBeginner is not `＊`' => [
                ['「基礎研修課程修了者等」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.providedByBeginner' => '#'],
                ['shifts.0.providedByBeginner' => '＊'],
            ],
            'when providedByCareWorkerForPwsd is not `＊`' => [
                ['「重研」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.providedByCareWorkerForPwsd' => '#'],
                ['shifts.0.providedByCareWorkerForPwsd' => '＊'],
            ],
            'when over20 is not `＊`' => [
                ['「同一建物減算」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.over20' => '#'],
                ['shifts.0.over20' => '＊'],
            ],
            'when over50 is not `＊`' => [
                ['「同一建物減算（大規模）」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.over50' => '#'],
                ['shifts.0.over50' => '＊'],
            ],
            'when behavioralDisorderSupportCooperation is not `＊`' => [
                ['「行動障害支援連携」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.behavioralDisorderSupportCooperation' => '#'],
                ['shifts.0.behavioralDisorderSupportCooperation' => '＊'],
            ],
            'when hospitalized is not `＊`' => [
                ['「入院」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.hospitalized' => '#'],
                ['shifts.0.hospitalized' => '＊'],
            ],
            'when longHospitalized is not `＊`' => [
                ['「入院（長期）」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.longHospitalized' => '#'],
                ['shifts.0.longHospitalized' => '＊'],
            ],
            'when coaching is not `＊`' => [
                ['「熟練同行」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.coaching' => '#'],
                ['shifts.0.coaching' => '＊'],
            ],
            'when vitalFunctionsImprovement1 is not `＊`' => [
                ['「生活機能向上連携Ⅰ」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.vitalFunctionsImprovement1' => '#'],
                ['shifts.0.vitalFunctionsImprovement1' => '＊'],
            ],
            'when vitalFunctionsImprovement2 is not `＊`' => [
                ['「生活機能向上連携Ⅱ」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.vitalFunctionsImprovement2' => '#'],
                ['shifts.0.vitalFunctionsImprovement2' => '＊'],
            ],
            'when note is not string' => [
                ['「備考」は文字列で入力してください。（行番号9）'],
                ['shifts.0.note' => 1234567890],
                ['shifts.0.note' => 'ここに備考を書く'],
            ],
            'when userId does not integer' => [
                ['「利用者」は整数で入力してください。（行番号9）'],
                ['shifts.0.userId' => 'error'],
                ['shifts.0.userId' => 1],
            ],
            'when an unknown userId given' => [
                ['「利用者」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.userId' => self::NOT_EXISTING_ID],
                ['shifts.0.userId' => 1],
            ],
            'when userId does not belong to given officeId' => [
                ['「利用者」は事業所に所属している利用者を指定してください。（行番号9）'],
                ['shifts.0.userId' => 2],
                ['shifts.0.userId' => 1],
            ],
            'when assigneeId1 is empty' => [
                ['「担当スタッフ: 1」は入力してください。（行番号9）'],
                ['shifts.0.assigneeId1' => null],
                ['shifts.0.assigneeId1' => 1],
            ],
            'when assigneeId1 is not integer' => [
                ['「担当スタッフ: 1」は整数で入力してください。（行番号9）'],
                ['shifts.0.assigneeId1' => 'error'],
                ['shifts.0.assigneeId1' => 1],
            ],
            'when an unknown assigneeId1 given' => [
                ['「担当スタッフ: 1」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.assigneeId1' => self::NOT_EXISTING_ID],
                ['shifts.0.assigneeId1' => 1],
            ],
            'when assigneeId2 is not integer' => [
                ['「担当スタッフ: 2」は整数で入力してください。（行番号9）'],
                ['shifts.0.assigneeId2' => 'error'],
                ['shifts.0.assigneeId2' => 2],
            ],
            'when the given assigneeId2 is the same as assigneeId1' => [
                ['「担当スタッフ: 2」は「担当スタッフ: 1」と違うものを入力してください。（行番号9）'],
                ['shifts.0.assigneeId1' => 1, 'shifts.0.assigneeId2' => 1],
                ['shifts.0.assigneeId1' => 1, 'shifts.0.assigneeId2' => 2],
            ],
            'when an unknown assigneeId2 given' => [
                ['「担当スタッフ: 2」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.assigneeId2' => self::NOT_EXISTING_ID],
                ['shifts.0.assigneeId2' => 2],
            ],
            'when assignerId is empty' => [
                ['「管理スタッフ」は入力してください。（行番号9）'],
                ['shifts.0.assignerId' => null],
                ['shifts.0.assignerId' => 1],
            ],
            'when assignerId is not integer' => [
                ['「管理スタッフ」は整数で入力してください。（行番号9）'],
                ['shifts.0.assignerId' => 'error'],
                ['shifts.0.assignerId' => 1],
            ],
            'when an unknown assignerId given' => [
                ['「管理スタッフ」は正しい値を入力してください。（行番号9）'],
                ['shifts.0.assignerId' => self::NOT_EXISTING_ID],
                ['shifts.0.assignerId' => 1],
            ],
            'when task is empty' => [
                ['「予定区分」は入力してください。（行番号9）'],
                ['shifts.0.task' => null],
                ['shifts.0.task' => Task::dwsVisitingCareForPwsd()->value()],
            ],
            'when task is not task' => [
                ['「予定区分」は勤務区分を選択してください。（行番号9）'],
                ['shifts.0.task' => 'error'],
                ['shifts.0.task' => Task::dwsVisitingCareForPwsd()->value()],
            ],
            'when startMinute is empty' => [
                ['「開始」は入力してください。（行番号9）'],
                ['shifts.0.startMinute' => ''],
                ['shifts.0.startMinute' => 200.0],
            ],
            'when startMinute is not integer' => [
                ['「開始」は整数で入力してください。（行番号9）'],
                ['shifts.0.startMinute' => 'error'],
                ['shifts.0.startMinute' => 200.0],
            ],
            'when endMinute is empty' => [
                ['「終了」は入力してください。（行番号9）'],
                ['shifts.0.endMinute' => ''],
                ['shifts.0.endMinute' => 200.0],
            ],
            'when endMinute is not integer' => [
                ['「終了」は整数で入力してください。（行番号9）'],
                ['shifts.0.endMinute' => 'error'],
                ['shifts.0.endMinute' => 200.0],
            ],
            'when totalDuration is empty' => [
                ['「合計」は入力してください。（行番号9）'],
                ['shifts.0.totalDuration' => ''],
                ['shifts.0.totalDuration' => 360.0],
            ],
            'when totalDuration is not integer' => [
                ['「合計」は整数で入力してください。（行番号9）'],
                ['shifts.0.totalDuration' => 'error'],
                ['shifts.0.totalDuration' => 360.0],
            ],
            'when totalDuration is different from the sum' => [
                ['「合計」は確認用項目と一致していません。（行番号9）'],
                ['shifts.0.totalDuration' => 300.0],
                ['shifts.0.totalDuration' => 360.0],
            ],
            'when dwsHome is empty' => [
                ['「居宅」は入力してください。（行番号9）'],
                ['shifts.0.dwsHome' => ''],
                ['shifts.0.dwsHome' => 330],
            ],
            'when dwsHome is not integer' => [
                ['「居宅」は整数で入力してください。（行番号9）'],
                ['shifts.0.dwsHome' => 'error'],
                ['shifts.0.dwsHome' => 330],
            ],
            'when visitingCare is empty' => [
                ['「重訪」は入力してください。（行番号9）'],
                ['shifts.0.visitingCare' => ''],
                ['shifts.0.visitingCare' => 0],
            ],
            'when visitingCare is not integer' => [
                ['「重訪」は整数で入力してください。（行番号9）'],
                ['shifts.0.visitingCare' => 'error'],
                ['shifts.0.visitingCare' => 0],
            ],
            'when outingSupport is empty' => [
                ['「移動加算」は入力してください。（行番号9）'],
                ['shifts.0.outingSupport' => ''],
                ['shifts.0.outingSupport' => 330.0],
            ],
            'when outingSupport is not integer' => [
                ['「移動加算」は整数で入力してください。（行番号9）'],
                ['shifts.0.outingSupport' => 'error'],
                ['shifts.0.outingSupport' => 330.0],
            ],
            'when physicalCare is empty' => [
                ['「介保身体」は入力してください。（行番号9）'],
                ['shifts.0.physicalCare' => ''],
                ['shifts.0.physicalCare' => 0.0],
            ],
            'when physicalCare is not integer' => [
                ['「介保身体」は整数で入力してください。（行番号9）'],
                ['shifts.0.physicalCare' => 'error'],
                ['shifts.0.physicalCare' => 0.0],
            ],
            'when housework is empty' => [
                ['「介保生活」は入力してください。（行番号9）'],
                ['shifts.0.housework' => ''],
                ['shifts.0.housework' => 0.0],
            ],
            'when housework is not integer' => [
                ['「介保生活」は整数で入力してください。（行番号9）'],
                ['shifts.0.housework' => 'error'],
                ['shifts.0.housework' => 0.0],
            ],
            'when comprehensive is empty' => [
                ['「総合事業」は入力してください。（行番号9）'],
                ['shifts.0.comprehensive' => ''],
                ['shifts.0.comprehensive' => 0],
            ],
            'when comprehensive is not integer' => [
                ['「総合事業」は整数で入力してください。（行番号9）'],
                ['shifts.0.comprehensive' => 'error'],
                ['shifts.0.comprehensive' => 0],
            ],
            'when commAccompany is empty' => [
                ['「移動支援」は入力してください。（行番号9）'],
                ['shifts.0.commAccompany' => ''],
                ['shifts.0.commAccompany' => 0],
            ],
            'when commAccompany is not integer' => [
                ['「移動支援」は整数で入力してください。（行番号9）'],
                ['shifts.0.commAccompany' => 'error'],
                ['shifts.0.commAccompany' => 0],
            ],
            'when ownExpense is empty' => [
                ['「自費」は入力してください。（行番号9）'],
                ['shifts.0.ownExpense' => ''],
                ['shifts.0.ownExpense' => 0],
            ],
            'when ownExpense is not integer' => [
                ['「自費」は整数で入力してください。（行番号9）'],
                ['shifts.0.ownExpense' => 'error'],
                ['shifts.0.ownExpense' => 0],
            ],
            'when other is empty' => [
                ['「その他」は入力してください。（行番号9）'],
                ['shifts.0.other' => ''],
                ['shifts.0.other' => 0.0],
            ],
            'when other is not integer' => [
                ['「その他」は整数で入力してください。（行番号9）'],
                ['shifts.0.other' => 'error'],
                ['shifts.0.other' => 0.0],
            ],
            'when resting is empty' => [
                ['「休憩」は入力してください。（行番号9）'],
                ['shifts.0.resting' => ''],
                ['shifts.0.resting' => 30.0],
            ],
            'when resting is not integer' => [
                ['「休憩」は整数で入力してください。（行番号9）'],
                ['shifts.0.resting' => 'error'],
                ['shifts.0.resting' => 30.0],
            ],
        ];
        $this->should(
            'throw ValidationException when the data does not pass the validation rules',
            function ($expected, $invalid, $valid): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                try {
                    $this->validator->validate($this->context, $input);
                    $this->assertTrue(false, 'unreachable when throw ValidationException');
                } catch (ValidationException $e) {
                    $this->assertSame($expected, Seq::fromArray($e->getErrors())->toArray());
                }
                if ($valid !== null) {
                    $input = $this->defaultInput();
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $this->validator->validate($this->context, $input);
                }
            },
            compact('examples')
        );
    }

    /**
     * @return array
     */
    private function defaultInput(): array
    {
        $dwsHome = 330;
        $visitingCare = 0;
        $physicalCare = 0.0;
        $housework = 0.0;
        $comprehensive = 0;
        $commAccompany = 0;
        $ownExpense = 0;
        $other = 0;
        $resting = 30.0;
        return [
            'officeId' => 1,
            'shifts' => [
                [
                    'isTraining1' => null,
                    'isTraining2' => null,
                    'serviceCode' => '123456',
                    'date' => 51136,
                    'notificationEnabled' => null,
                    'oneOff' => null,
                    'firstTime' => null,
                    'emergency' => null,
                    'sucking' => null,
                    'welfareSpecialistCooperation' => null,
                    'plannedByNovice' => null,
                    'providedByBeginner' => null,
                    'providedByCareWorkerForPwsd' => null,
                    'over20' => null,
                    'over50' => null,
                    'behavioralDisorderSupportCooperation' => null,
                    'hospitalized' => null,
                    'longHospitalized' => null,
                    'coaching' => null,
                    'vitalFunctionsImprovement1' => null,
                    'vitalFunctionsImprovement2' => null,
                    'note' => null,
                    'userId' => 1,
                    'assigneeId1' => 1,
                    'assigneeId2' => 2,
                    'assignerId' => 3,
                    'task' => Task::dwsVisitingCareForPwsd()->value(),
                    'startMinute' => 300.0,
                    'endMinute' => 660.0,
                    'totalDuration' => 360.0,
                    'dwsHome' => $dwsHome,
                    'visitingCare' => $visitingCare,
                    'outingSupport' => 0.0,
                    'physicalCare' => $physicalCare,
                    'housework' => $housework,
                    'comprehensive' => $comprehensive,
                    'commAccompany' => $commAccompany,
                    'ownExpense' => $ownExpense,
                    'other' => $other,
                    'resting' => $resting,
                    'totalDuration_confirmation' => $dwsHome + $visitingCare + $physicalCare + $housework
                        + $comprehensive + $commAccompany + $ownExpense + $other + $resting,
                ],
            ],
        ];
    }
}
