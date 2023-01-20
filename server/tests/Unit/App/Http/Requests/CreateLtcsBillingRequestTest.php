<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateLtcsBillingRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LtcsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateLtcsBillingRequest} のテスト.
 */
final class CreateLtcsBillingRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use LtcsProvisionReportFinderMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private CreateLtcsBillingRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new CreateLtcsBillingRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq()
            );

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]->id))
                ->byDefault();
            $self->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::fromArray($self->examples->ltcsProvisionReports), Pagination::create()))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should(
            'return array',
            function (): void {
                $input = $this->defaultInput();
                // リクエスト内容を反映させるために initialize() を利用する
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input)
                );
                $this->assertEquals(
                    $this->expectedPayload($input),
                    $this->request->payload()
                );
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when officeId is empty' => [
                ['officeId' => ['入力してください。']],
                ['officeId' => ''],
                ['officeId' => $this->examples->offices[1]->id],
            ],
            'when transactedIn is empty' => [
                ['transactedIn' => ['入力してください。']],
                ['transactedIn' => ''],
                ['transactedIn' => '2020-01'],
            ],
            'when transactedIn is invalid' => [
                ['transactedIn' => ['正しい日付を入力してください。']],
                ['transactedIn' => 'invalid'],
                ['transactedIn' => '2020-01'],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($invalid + $input);
                $this->assertTrue($validator->fails());
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($valid + $input);
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
                }
            },
            compact('examples')
        );
        $this->should('fails when provision report is empty', function () {
            $this->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::empty(), Pagination::create()));
            $input = $this->defaultInput();
            $validator = $this->request->createValidatorInstance($input);
            $this->assertTrue($validator->fails());
            $this->assertEquals(['officeId' => ['対象となる予実が存在しません。']], $validator->errors()->toArray());
        });
        $this->should('fails when provision report entry is only ownExpense', function () {
            $this->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($this->examples->ltcsProvisionReports[8]), Pagination::create()));
            $input = $this->defaultInput();
            $validator = $this->request->createValidatorInstance($input);
            $this->assertTrue($validator->fails());
            $this->assertEquals(['officeId' => ['対象となる予実が存在しません。']], $validator->errors()->toArray());
        });
    }

    /**
     * 入力値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2020-01',
        ];
    }

    private function expectedPayload(array $input): array
    {
        [$year, $month] = explode('-', $input['transactedIn']);
        $transactedIn = Carbon::create($year, $month);
        return [
            'officeId' => $input['officeId'],
            'transactedIn' => $transactedIn,
            'fixedAt' => CarbonRange::create([
                'start' => $transactedIn->subMonth()->day(11),
                'end' => $transactedIn->day(10)->endOfDay(),
            ]),
        ];
    }
}
