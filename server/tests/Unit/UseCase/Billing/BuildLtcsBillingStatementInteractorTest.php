<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetail as ServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition as Disposition;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Contract\ContractStatus;
use Domain\FinderResult;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Office\Office;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry;
use Domain\ServiceCode\ServiceCode;
use Domain\User\User;
use Domain\User\UserLtcsSubsidy;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsProvisionReportSheetAppendixUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\ContractFinderMixin;
use Tests\Unit\Mixins\IdentifyLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\IdentifyUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\ResolveLtcsNameFromServiceCodesUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildLtcsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\BuildLtcsBillingStatementInteractor} のテスト.
 */
final class BuildLtcsBillingStatementInteractorTest extends Test
{
    use BuildLtcsProvisionReportSheetAppendixUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ContractFinderMixin;
    use ExamplesConsumer;
    use IdentifyLtcsInsCardUseCaseMixin;
    use IdentifyUserLtcsSubsidyUseCaseMixin;
    use LtcsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use ResolveLtcsNameFromServiceCodesUseCaseMixin;
    use UnitSupport;

    private Carbon $carbon;

    private BuildLtcsBillingStatementInteractor $interactor;
    private LtcsInsCard $insCard;
    private User $user;
    private Seq $details;
    private LtcsProvisionReport $report;
    private Seq $reports;
    private Decimal $unitCost;
    private Map $serviceCodeMap;
    private LtcsProvisionReportSheetAppendix $appendix;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->carbon = Carbon::create(2020, 12, 8);
            $self->setupTestData();
            $self->user = $self->users->head();
            $self->insCard = $self->insCards->find(fn (LtcsInsCard $x): bool => $x->userId === $self->user->id)->get();
            $self->details = Seq::fromArray($self->bundle->details);
            $self->report = $self->examples->ltcsProvisionReports[11];
            $self->reports = Seq::from($self->report);
            $self->unitCost = Decimal::fromInt(11_2000);
            $self->serviceCodeMap = Map::from([
                '116275' => '処遇改善加算Ⅰ',
                '112097' => '身体3・Ⅰ',
                '112121' => '身3生2・Ⅰ',
                '118014' => '生活3・夜・Ⅰ',
            ]);
            $self->appendix = new LtcsProvisionReportSheetAppendix(
                providedIn: $self->bundle->providedIn,
                insNumber: $self->insCard->insNumber,
                userName: $self->user->name->displayName,
                unmanagedEntries: Seq::from(
                    new LtcsProvisionReportSheetAppendixEntry(
                        officeName: '土屋訪問介護事業所 ',
                        officeCode: '1234567890',
                        serviceName: '処遇改善加算Ⅰ',
                        serviceCode: '116275',
                        unitScore: 1035,
                        count: 1,
                        wholeScore: 1035,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 90,
                    ),
                ),
                managedEntries: Seq::from(
                    new LtcsProvisionReportSheetAppendixEntry(
                        officeName: '土屋訪問介護事業所 ',
                        officeCode: '1234567890',
                        serviceName: '身体3・Ⅰ',
                        serviceCode: '112097',
                        unitScore: 695,
                        count: 4,
                        wholeScore: 2780,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 90,
                    ),
                    new LtcsProvisionReportSheetAppendixEntry(
                        officeName: '土屋訪問介護事業所 ',
                        officeCode: '1234567890',
                        serviceName: '身3生2・Ⅰ',
                        serviceCode: '112121',
                        unitScore: 856,
                        count: 4,
                        wholeScore: 3424,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 90,
                    ),
                    new LtcsProvisionReportSheetAppendixEntry(
                        officeName: '土屋訪問介護事業所 ',
                        officeCode: '1234567890',
                        serviceName: '生活3・夜・Ⅰ',
                        serviceCode: '118014',
                        unitScore: 337,
                        count: 4,
                        wholeScore: 1348,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 90,
                    ),
                ),
                maxBenefit: $self->insCard->ltcsLevel->maxBenefit(),
                insuranceClaimAmount: 88101,
                subsidyClaimAmount: 0,
                copayAmount: 9790,
                unitCost: Decimal::fromInt(11_4000),
            );
        });
        self::beforeEachSpec(function (self $self): void {
            $self->contractFinder
                ->allows('find')
                ->andReturnUsing(function (array $filterParams) use ($self): FinderResult {
                    $xs = $self->contracts->filter(fn (Contract $x): bool => $x->userId === $filterParams['userId']);
                    return FinderResult::from([...$xs], Pagination::create([]));
                })
                ->byDefault();

            $self->identifyLtcsInsCardUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, User $user) use ($self): Option {
                    return $self->insCards->find(fn (LtcsInsCard $x): bool => $x->userId === $user->id);
                })
                ->byDefault();

            $self->identifyUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, User $user) use ($self): Seq {
                    return $self->subsidies
                        ->filter(fn (UserLtcsSubsidy $x): bool => $x->userId === $user->id)
                        ->map(fn (UserLtcsSubsidy $x): Option => Option::some($x))
                        ->append([Option::none(), Option::none(), Option::none()])
                        ->take(3)
                        ->computed();
                })
                ->byDefault();

            $self->buildLtcsProvisionReportSheetAppendixUseCase
                ->allows('handle')
                ->andReturn($self->appendix)
                ->byDefault();

            $self->resolveLtcsNameFromServiceCodesUseCase
                ->allows('handle')
                ->andReturn($self->serviceCodeMap)
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturnUsing(
                    fn (int ...$ids): Seq => $self->offices->filter(
                        fn (Office $x): bool => in_array($x->id, $ids, true)
                    )
                )
                ->byDefault();

            $self->interactor = app(BuildLtcsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('identify Contract for each user', function (): void {
            $contracts = $this->contracts->toMap('userId');
            $filterParams = [
                'officeId' => $this->office->id,
                'userId' => $this->user->id,
                'serviceSegment' => ServiceSegment::longTermCare(),
                'status' => [ContractStatus::formal(), ContractStatus::terminated()],
                'contractedOnBefore' => $this->bundle->providedIn->endOfMonth(),
                'terminatedOnAfter' => $this->bundle->providedIn->startOfMonth(),
            ];
            $paginationParams = [
                'itemsPerPage' => 1,
                'sortBy' => 'id',
                'desc' => true,
            ];
            $this->contractFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($contracts->get($this->user->id), Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );
        });
        $this->should('identify LtcsInsCard for each user', function (): void {
            $targetDate = [];
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->user, Mockery::on(function (Carbon $x) use (&$targetDate): bool {
                    $targetDate[] = $x;
                    return true;
                }))
                ->andReturn(Option::from($this->insCard))
                ->twice();

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );

            $this->assertEquals($this->bundle->providedIn->startOfMonth(), $targetDate[0]);
            $this->assertEquals($this->bundle->providedIn->endOfMonth(), $targetDate[1]);
        });
        $this->should('throw NotFoundException when failed to identify the LtcsInsCard', function (): void {
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->user,
                    Mockery::on(fn (Carbon $x): bool => $x->eq($this->bundle->providedIn->endOfMonth()))
                )
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->bundle,
                    $this->user,
                    $this->office,
                    $this->details,
                    $this->unitCost,
                    $this->reports,
                );
            });
        });
        $this->should('lookup care plan author office for each user', function (): void {
            $this->officeRepository
                ->expects('lookup')
                ->with(
                    $this->insCards
                        ->find(fn (LtcsInsCard $x): bool => $x->userId === $this->user->id)
                        ->get()
                        ->carePlanAuthorOfficeId
                )
                ->andReturn(Seq::from($this->office));

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );
        });
        $this->should('throw NotFoundException when failed to lookup the office', function (): void {
            $this->officeRepository->expects('lookup')->andReturn(Seq::empty());
            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->bundle,
                    $this->user,
                    $this->office,
                    $this->details,
                    $this->unitCost,
                    $this->reports,
                );
            });
        });
        $this->should(
            'throw LogicException when the LtcsInsCard has no carePlanAuthorOfficeId even if carePlanAuthorType is careManagerOffice',
            function (): void {
                $insCard = $this->insCards->head()->copy([
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice(),
                    'carePlanAuthorOfficeId' => null,
                ]);
                $this->identifyLtcsInsCardUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->user,
                        Mockery::on(fn (Carbon $x): bool => $x->eq($this->bundle->providedIn->endOfMonth()))
                    )
                    ->andReturn(Option::some($insCard));

                $this->assertThrows(LogicException::class, function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->bundle,
                        $this->user,
                        $this->office,
                        $this->details,
                        $this->unitCost,
                        $this->reports,
                    );
                });
            }
        );
        $this->should('identify UserLtcsSubsidy(s) for each user', function (): void {
            $subsidies = $this->subsidies->toMap('userId');
            $this->identifyUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, $this->user, $this->bundle->providedIn)
                ->andReturn(
                    $subsidies->get($this->user->id)
                        ->toSeq()
                        ->map(fn (UserLtcsSubsidy $x): Option => Option::some($x))
                        ->append([Option::none(), Option::none(), Option::none()])
                        ->take(3)
                        ->computed()
                );

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );
        });
        $this->should('build appendix', function (): void {
            $this->buildLtcsProvisionReportSheetAppendixUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->report,
                    Mockery::on(fn (Option $x): bool => $x->isDefined() && $x->get() === $this->insCard),
                    $this->insCard,
                    $this->office,
                    $this->user,
                    $this->details,
                    $this->serviceCodeMap
                )
                ->andReturn($this->appendix);

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );
        });
        $this->should('return LtcsBillingStatement', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->specify('サービスコード名称を取得する', function (): void {
            // 引数で受け取ったサービス詳細に含まれるサービスコードの配列
            $serviceCodes = $this->details
                ->filter(fn (ServiceDetail $x): bool => $x->disposition === Disposition::result())
                ->map(fn (LtcsBillingServiceDetail $x): ServiceCode => $x->serviceCode)
                ->distinctBy(fn (ServiceCode $x): string => $x->toString())
                ->computed();
            $this->resolveLtcsNameFromServiceCodesUseCase
                ->expects('handle')
                ->with($this->context, Mockery::capture($actual), equalTo($this->bundle->providedIn))
                ->andReturn($this->serviceCodeMap);

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );

            $this->assertArrayStrictEquals($serviceCodes->toArray(), $actual->toArray());
        });
    }
}
