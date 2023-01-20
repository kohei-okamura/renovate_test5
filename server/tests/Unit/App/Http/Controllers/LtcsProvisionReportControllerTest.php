<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsProvisionReportController;
use App\Http\Requests\CreateLtcsProvisionReportSheetRequest;
use App\Http\Requests\Delegates\LtcsProvisionReportFormDelegateImpl;
use App\Http\Requests\DeleteLtcsProvisionReportRequest;
use App\Http\Requests\FindLtcsProvisionReportRequest;
use App\Http\Requests\GetLtcsProvisionReportScoreSummaryRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsProvisionReportRequest;
use App\Http\Requests\UpdateLtcsProvisionReportStatusRequest;
use App\Jobs\CreateLtcsProvisionReportSheetJob;
use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\TimeRange;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Job\Job;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Permission\Permission;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\DeleteLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\GetIndexLtcsProvisionReportDigestUseCaseMixin;
use Tests\Unit\Mixins\GetLtcsProvisionReportScoreSummaryUseCaseMixin;
use Tests\Unit\Mixins\GetLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\IdentifyLtcsHomeVisitLongTermCareDictionaryUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateLtcsProvisionReportStatusUseCaseMixin;
use Tests\Unit\Mixins\UpdateLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\LtcsProvisionReportController} のテスト.
 */
class LtcsProvisionReportControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use DeleteLtcsProvisionReportUseCaseMixin;
    use ExamplesConsumer;
    use GetLtcsProvisionReportUseCaseMixin;
    use GetIndexLtcsProvisionReportDigestUseCaseMixin;
    use GetLtcsProvisionReportScoreSummaryUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use IdentifyLtcsHomeVisitLongTermCareDictionaryUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use UpdateLtcsProvisionReportUseCaseMixin;
    use UpdateLtcsProvisionReportStatusUseCaseMixin;
    use JobsDispatcherMixin;

    private const FILTER_PARAMS = [
        'officeId' => 1,
        'providedIn' => '2020-10',
    ];
    private const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];
    private LtcsProvisionReport $ltcsProvisionReport;
    private LtcsProvisionReportController $controller;
    private FinderResult $finderResult;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProvisionReportControllerTest $self): void {
            $self->finderResult = FinderResult::from([], Pagination::create());
            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];

            $self->deleteLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[0]))
                ->byDefault();
            $self->updateLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn($self->examples->ltcsProvisionReports[0])
                ->byDefault();
            $self->updateLtcsProvisionReportStatusUseCase
                ->allows('handle')
                ->andReturn($self->examples->ltcsProvisionReports[0])
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->getIndexLtcsProvisionReportDigestUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();
            $self->getLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->ltcsProvisionReport))
                ->byDefault();
            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();
            $self->identifyLtcsHomeVisitLongTermCareDictionary
                ->allows('handle')
                ->andReturn(Option::some($self->examples->ltcsHomeVisitLongTermCareDictionaries[0]))
                ->byDefault();
            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]), Pagination::create([])))
                ->byDefault();
            // TODO とりあえず適当。ちゃんと実装する時に修正する
            $self->getLtcsProvisionReportScoreSummaryUseCase
                ->allows('handle')
                ->andReturn([
                    'plan' => ['managedScore' => 20000, 'unmanagedScore' => 20000],
                    'result' => ['managedScore' => 20000, 'unmanagedScore' => 20000],
                ])
                ->byDefault();

            $self->controller = app(LtcsProvisionReportController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_delete(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-provision-reports/{officeId}/{userId}/{providedIn}',
            'DELETE',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                // ルートパラメーター
                'officeId' => $this->ltcsProvisionReport->officeId,
                'userId' => $this->ltcsProvisionReport->userId,
                'providedIn' => $this->ltcsProvisionReport->providedIn,
            ])
        ));
        app()->bind(DeleteLtcsProvisionReportRequest::class, function () {
            $request = Mockery::mock(DeleteLtcsProvisionReportRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call(
                    [$this->controller, 'delete'],
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'providedIn' => $this->ltcsProvisionReport->providedIn,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call(
                    [$this->controller, 'delete'],
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getContent()
            );
        });
        $this->should('delete LtcsProvisionReport using use case', function (): void {
            $this->deleteLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    equalTo($this->ltcsProvisionReport->providedIn),
                )
                ->andReturnNull();
            app()->call(
                [$this->controller, 'delete'],
                [
                    'officeId' => $this->ltcsProvisionReport->officeId,
                    'userId' => $this->ltcsProvisionReport->userId,
                    'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-provision-reports/{officeId}/{userId}/{providedIn}',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'get'],
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON of ltcsProvisionReport', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'officeId' => $this->ltcsProvisionReport->officeId,
                    'userId' => $this->ltcsProvisionReport->userId,
                    'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ]
            );
            $ltcsProvisionReport = $this->ltcsProvisionReport;

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('ltcsProvisionReport')), $response->getContent());
        });
        $this->should('get ltcsProvisionReport using use case', function (): void {
            $this->getLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    equalTo($this->ltcsProvisionReport->providedIn),
                )
                ->andReturn(Option::from($this->ltcsProvisionReport));

            app()->call(
                [$this->controller, 'get'],
                [
                    'officeId' => $this->ltcsProvisionReport->officeId,
                    'userId' => $this->ltcsProvisionReport->userId,
                    'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ]
            );
        });
        $this->should('return a 204 response when the id not exists in db', function (): void {
            $this->getLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'officeId' => $this->ltcsProvisionReport->officeId,
                    'userId' => $this->ltcsProvisionReport->userId,
                    'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ]
            );
            $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-provision-reports',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindLtcsProvisionReportRequest::class, function () {
            $request = Mockery::mock(FindLtcsProvisionReportRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn(self::FILTER_PARAMS)->byDefault();
            $request->allows('paginationParams')->andReturn(self::PAGINATION_PARAMS)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'getIndex'])->getStatusCode()
            );
        });
        $this->should('return a JSON of FinderResult', function (): void {
            $this->assertSame(
                $this->finderResult->toJson(),
                app()->call([$this->controller, 'getIndex'])->getContent()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-provision-reports/{officeId}/{userId}/{providedIn}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateLtcsProvisionReportRequest::class, function () {
            $delegate = new LtcsProvisionReportFormDelegateImpl();
            $request = Mockery::mock(UpdateLtcsProvisionReportRequest::class)->makePartial();
            $request->allows('getDelegate')->andReturn($delegate)->byDefault();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $ltcsProvisionReport = $this->ltcsProvisionReport;
            $this->assertSame(
                Json::encode(compact('ltcsProvisionReport')),
                app()->call(
                    [$this->controller, 'update'],
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getContent()
            );
        });
        $this->should('update LtcsProvisionReport using use case', function (): void {
            $this->updateLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    $this->payload(),
                )
                ->andReturn($this->ltcsProvisionReport);
            app()->call(
                [$this->controller, 'update'],
                [
                    'officeId' => $this->ltcsProvisionReport->officeId,
                    'userId' => $this->ltcsProvisionReport->userId,
                    'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_status(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-provision-reports/{officeId}/{userId}/{providedIn}/status',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'officeId' => $this->examples->offices[0]->id,
                'userId' => $this->examples->users[0]->id,
                'status' => LtcsProvisionReportStatus::fixed()->value(),
            ])
        ));
        app()->bind(UpdateLtcsProvisionReportStatusRequest::class, function () {
            $request = Mockery::mock(UpdateLtcsProvisionReportStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'status'],
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'providedIn' => $this->ltcsProvisionReport->providedIn,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $ltcsProvisionReport = $this->ltcsProvisionReport;
            $this->assertSame(
                Json::encode(compact('ltcsProvisionReport')),
                app()->call(
                    [$this->controller, 'status'],
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getContent()
            );
        });
        $this->should('update status of LtcsProvisionReport using use case', function (): void {
            $this->updateLtcsProvisionReportStatusUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    ['status' => LtcsProvisionReportStatus::fixed()]
                )
                ->andReturn($this->ltcsProvisionReport);
            app()->call(
                [$this->controller, 'status'],
                [
                    'officeId' => $this->ltcsProvisionReport->officeId,
                    'userId' => $this->ltcsProvisionReport->userId,
                    'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get_score_summary(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-provision-report-score-summary',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(GetLtcsProvisionReportScoreSummaryRequest::class, function () {
            $delegate = new LtcsProvisionReportFormDelegateImpl();
            $request = Mockery::mock(GetLtcsProvisionReportScoreSummaryRequest::class)->makePartial();
            $request->allows('getDelegate')->andReturn($delegate)->byDefault();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'getScoreSummary'])->getStatusCode()
            );
        });
        $this->should('return a JSON of score summary', function (): void {
            $this->assertSame(
                Json::encode([
                    'plan' => ['managedScore' => 20000, 'unmanagedScore' => 20000],
                    'result' => ['managedScore' => 20000, 'unmanagedScore' => 20000],
                ]),
                app()->call([$this->controller, 'getScoreSummary'])->getContent()
            );
        });
        $this->should('get score summary using use case', function (): void {
            $plan = new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: $this->ltcsProvisionReport->plan->maxBenefitExcessScore,
                maxBenefitQuotaExcessScore: $this->ltcsProvisionReport->plan->maxBenefitQuotaExcessScore,
            );
            $result = new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: $this->ltcsProvisionReport->result->maxBenefitExcessScore,
                maxBenefitQuotaExcessScore: $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
            );
            $this->getLtcsProvisionReportScoreSummaryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    equalTo(Carbon::parse($this->ltcsProvisionReport->providedIn->format('Y-m'))),
                    Mockery::capture($actualEntries),
                    HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
                    LtcsTreatmentImprovementAddition::none(),
                    LtcsSpecifiedTreatmentImprovementAddition::none(),
                    LtcsBaseIncreaseSupportAddition::none(),
                    LtcsOfficeLocationAddition::none(),
                    Mockery::capture($actualPlan),
                    Mockery::capture($actualResult),
                )
                ->andReturn([
                    'plan' => ['managedScore' => 20000, 'unmanagedScore' => 20000],
                    'result' => ['managedScore' => 20000, 'unmanagedScore' => 20000],
                ]);
            $expectedEntries = $this->entries();

            app()->call([$this->controller, 'getScoreSummary']);

            $this->assertEach(
                function (LtcsProvisionReportEntry $expected, LtcsProvisionReportEntry $actual): void {
                    $this->assertModelStrictEquals($expected, $actual);
                },
                $expectedEntries->toArray(),
                $actualEntries->toArray()
            );
            $this->assertModelStrictEquals($plan, $actualPlan);
            $this->assertModelStrictEquals($result, $actualResult);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_createSheet(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-provision-report-sheets',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'officeId' => $this->input()['officeId'],
                'userId' => $this->input()['userId'],
                'providedIn' => $this->input()['providedIn'],
                'issuedOn' => '2021-11-10T00:00:00Z',
                'needsMaskingInsNumber' => true,
                'needsMaskingInsName' => true,
            ]),
        ));
        app()->bind(CreateLtcsProvisionReportSheetRequest::class, function () {
            $request = Mockery::mock(CreateLtcsProvisionReportSheetRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'createSheet'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createSheet'])->getContent()
            );
        });
        $this->should('create ltcs-provision-report-sheet using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateLtcsProvisionReportSheetJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'createSheet']);
        });
    }

    /**
     * payload が返す配列を生成.
     *
     * @return array
     */
    private function payload(): array
    {
        $input = $this->input();
        return [
            'entries' => $this->entries($input['entries'])->toArray(),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from($input['specifiedOfficeAddition']),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::from($input['treatmentImprovementAddition']),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::from($input['specifiedTreatmentImprovementAddition']),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::from($input['baseIncreaseSupportAddition']),
            'locationAddition' => LtcsOfficeLocationAddition::from($input['locationAddition']),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: $input['plan']['maxBenefitExcessScore'],
                maxBenefitQuotaExcessScore: $input['plan']['maxBenefitQuotaExcessScore'],
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: $input['result']['maxBenefitExcessScore'],
                maxBenefitQuotaExcessScore: $input['result']['maxBenefitQuotaExcessScore'],
            ),
        ];
    }

    /**
     * payload が返す配列を生成.
     *
     * @param ?array $entries
     * @return \Domain\ProvisionReport\LtcsProvisionReportEntry[]|\ScalikePHP\Seq
     */
    private function entries(?array $entries = null): Seq
    {
        $values = $entries ?? $this->input()['entries'];
        return Seq::fromArray($values)->map(
            fn (array $entry): LtcsProvisionReportEntry => LtcsProvisionReportEntry::create([
                'ownExpenseProgramId' => $entry['ownExpenseProgramId'],
                'slot' => TimeRange::create([
                    'start' => $entry['slot']['start'],
                    'end' => $entry['slot']['end'],
                ]),
                'timeframe' => Timeframe::from($entry['timeframe']),
                'category' => LtcsProjectServiceCategory::from($entry['category']),
                'amounts' => Seq::fromArray($entry['amounts'])
                    ->map(fn (array $amount): LtcsProjectAmount => LtcsProjectAmount::create([
                        'category' => LtcsProjectAmountCategory::from($amount['category']),
                        'amount' => $amount['amount'],
                    ]))
                    ->toArray(),
                'headcount' => $entry['headcount'],
                'serviceCode' => ServiceCode::fromString($entry['serviceCode']),
                'options' => Seq::fromArray($entry['options'])
                    ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                    ->toArray(),
                'note' => $entry['note'],
                'plans' => Seq::fromArray($entry['plans'])
                    ->map(fn (string $plan): Carbon => Carbon::parse($plan))
                    ->toArray(),
                'results' => Seq::fromArray($entry['results'])
                    ->map(fn (string $result): Carbon => Carbon::parse($result))
                    ->toArray(),
            ])
        );
    }

    /**
     * 更新用input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'entries' => Seq::fromArray($this->ltcsProvisionReport->entries)
                ->map(fn (LtcsProvisionReportEntry $entry): array => [
                    'ownExpenseProgramId' => $entry->ownExpenseProgramId,
                    'slot' => [
                        'start' => $entry->slot->start,
                        'end' => $entry->slot->end,
                    ],
                    'timeframe' => $entry->timeframe->value(),
                    'category' => $entry->category->value(),
                    'amounts' => Seq::fromArray($entry->amounts)
                        ->map(fn (LtcsProjectAmount $amount): array => [
                            'category' => $amount->category->value(),
                            'amount' => $amount->amount,
                        ])
                        ->toArray(),
                    'headcount' => $entry->headcount,
                    'serviceCode' => $entry->serviceCode->toString(),
                    'options' => Seq::fromArray($entry->options)
                        ->map(fn (ServiceOption $option): int => $option->value())
                        ->toArray(),
                    'note' => $entry->note,
                    'plans' => Seq::fromArray($entry->plans)
                        ->map(fn (Carbon $plan): string => $plan->toDateString())
                        ->toArray(),
                    'results' => Seq::fromArray($entry->results)
                        ->map(fn (Carbon $result): string => $result->toDateString())
                        ->toArray(),
                ])
                ->toArray(),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none()->value(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none()->value(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none()->value(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none()->value(),
            'locationAddition' => LtcsOfficeLocationAddition::none()->value(),
            'plan' => [
                'maxBenefitExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitExcessScore,
                'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitQuotaExcessScore,
            ],
            'result' => [
                'maxBenefitExcessScore' => $this->ltcsProvisionReport->result->maxBenefitExcessScore,
                'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
            ],
            // ルートパラメーター
            'officeId' => $this->ltcsProvisionReport->officeId,
            'userId' => $this->ltcsProvisionReport->userId,
            'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
        ];
    }
}
