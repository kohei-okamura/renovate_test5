<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindAttendanceRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\Task;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * FindAttendanceRequest のテスト.
 */
class FindAttendanceRequestTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [
        'sortBy' => 'date',
        'all' => true,
        'desc' => true,
        'itemsPerPage' => 10,
        'page' => 2,
    ];

    private FindAttendanceRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindAttendanceRequestTest $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::listAttendances(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());

            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::listAttendances(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::listAttendances()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();

            $self->request = (new FindAttendanceRequest())->replace($self->input());
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_filterParams(): void
    {
        $this->should('return an array of specified filter params', function (): void {
            $this->assertEquals($this->filterParams(), $this->request->filterParams());
        });
        $this->should('set carbon params to null when specify empty', function (): void {
            $request = (new FindAttendanceRequest())->replace(['start' => '', 'end' => '']);
            $filterParams = $request->filterParams();
            $this->assertSame(['start' => null, 'end' => null], $filterParams);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_paginationParams(): void
    {
        $this->should('return an array of pagination params', function (): void {
            $this->assertSame(self::PAGINATION_PARAMS, $this->request->paginationParams());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->input());
            $this->assertTrue($validator->passes());
        });

        $examples = [
            'when userId is not exist' => [
                ['userId' => ['正しい値を入力してください。']],
                ['userId' => self::NOT_EXISTING_ID],
                ['userId' => $this->examples->users[0]->id],
            ],
            'when assigneeId is empty' => [
                ['assigneeId' => ['正しい値を入力してください。']],
                ['assigneeId' => self::NOT_EXISTING_ID],
                ['assigneeId' => $this->examples->staffs[0]->id],
            ],
            'when assignerId is empty' => [
                ['assignerId' => ['正しい値を入力してください。']],
                ['assignerId' => self::NOT_EXISTING_ID],
                ['assignerId' => $this->examples->staffs[0]->id],
            ],
            'when officeId is not exist' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->offices[0]->id],
            ],
            'when an invalid task is given' => [
                ['task' => ['勤務区分を選択してください。']],
                ['task' => self::INVALID_ENUM_VALUE],
                ['task' => Task::commAccompany()->value()],
            ],
            'when isConfirmed is not boolean_ext' => [
                ['isConfirmed' => ['trueかfalseにしてください。']],
                ['isConfirmed' => 'string'],
                ['isConfirmed' => 'true'],
            ],
            'when start is not date' => [
                ['start' => ['正しい日付を入力してください。']],
                ['start' => 'date'],
                ['start' => '2020-01-01'],
            ],
            'when end is not date' => [
                ['end' => ['正しい日付を入力してください。']],
                ['end' => 'date'],
                ['end' => '2020-01-01'],
            ],
            'when end is before start' => [
                ['end' => ['勤務日（開始）以降の日付を入力してください。']],
                ['start' => '2020-01-02', 'end' => '2020-01-01'],
                ['start' => '2020-01-01', 'end' => '2020-01-01'],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->input());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->input());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力値.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            ...self::PAGINATION_PARAMS,
            ...$this->filterParams(),
            'task' => (string)Task::dwsPhysicalCare()->value(),
            'start' => '2020-01-01',
            'end' => '2020-01-01',
        ];
    }

    /**
     * filterParams() が返す期待値.
     *
     * @return array
     */
    private function filterParams(): array
    {
        return [
            'userId' => 2,
            'assigneeId' => 3,
            'assignerId' => 4,
            'officeId' => 5,
            'task' => Task::dwsPhysicalCare(),
            'isConfirmed' => false,
            'start' => Carbon::parse('2020-01-01'),
            'end' => Carbon::parse('2020-01-01'),
        ];
    }
}
