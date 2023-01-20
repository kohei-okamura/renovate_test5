<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingStatement;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\FinderResult;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry;
use Domain\User\User;
use Domain\User\UserLtcsSubsidy;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\SetupException;
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
use Tests\Unit\Mixins\IdentifyLtcsAreaGradeFeeUseCaseMixin;
use Tests\Unit\Mixins\IdentifyLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\IdentifyUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\ResolveLtcsNameFromServiceCodesUseCaseMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildLtcsBillingStatementInteractor;
use UseCase\Billing\BuildLtcsBillingStatementUseCase;
use UseCase\Billing\CreateLtcsBillingStatementInteractor;
use UseCase\Billing\CreateLtcsBillingStatementListInteractor;
use UseCase\Billing\CreateLtcsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingStatementListInteractor} のテスト.
 */
final class CreateLtcsBillingStatementListInteractorTest extends Test
{
    use BuildLtcsProvisionReportSheetAppendixUseCaseMixin;
    use CarbonMixin;
    use ContractFinderMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use IdentifyLtcsAreaGradeFeeUseCaseMixin;
    use IdentifyLtcsInsCardUseCaseMixin;
    use IdentifyUserLtcsSubsidyUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LtcsBillingStatementRepositoryMixin;
    use LtcsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use ResolveLtcsNameFromServiceCodesUseCaseMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Carbon $carbon;
    private Seq $reports;
    private Map $serviceCodeMap;
    private LtcsProvisionReportSheetAppendix $appendix;

    private CreateLtcsBillingStatementListInteractor $interactor;

    /**
     * 初期化処理.
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->carbon = Carbon::create(2020, 12, 8);
            $self->setupTestData();
            $self->reports = $self->users->map(fn (User $x) => $self->examples->ltcsProvisionReports[11]->copy([
                'userId' => $x->id,
            ]));
            $self->serviceCodeMap = Map::from([
                '116275' => '処遇改善加算Ⅰ',
                '112097' => '身体3・Ⅰ',
                '112121' => '身3生2・Ⅰ',
                '118014' => '生活3・夜・Ⅰ',
            ]);
            $user = $self->users->head();
            $insCard = $self->insCards->find(fn (LtcsInsCard $x): bool => $x->userId === $user->id)->get();
            $self->appendix = new LtcsProvisionReportSheetAppendix(
                providedIn: $self->bundle->providedIn,
                insNumber: $insCard->insNumber,
                userName: $user->name->displayName,
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
                maxBenefit: $insCard->ltcsLevel->maxBenefit(),
                insuranceClaimAmount: 88101,
                subsidyClaimAmount: 0,
                copayAmount: 9790,
                unitCost: Decimal::fromInt(11_4000),
            );

            // 今回のテストでは以下のユースケースに処理を切り出すという対応をしたためテストでは以前のものと変更がないことが確認したい。
            // Mockを使うとMockの戻り値によってしまうため定義の手間がかかる & 以前のものと変わっていてもわからないという問題がある。
            // そのため今回のテストでは共通化して切り出したユースケースは実際の実装を使うようにする。
            $dependencies = [
                BuildLtcsBillingStatementUseCase::class => BuildLtcsBillingStatementInteractor::class,
                CreateLtcsBillingStatementUseCase::class => CreateLtcsBillingStatementInteractor::class,
            ];
            foreach ($dependencies as $abstract => $concrete) {
                app()->bind($abstract, $concrete);
            }
        });
        self::beforeEachSpec(function (self $self): void {
            $self->contractFinder
                ->allows('find')
                ->andReturnUsing(function (array $filterParams) use ($self): FinderResult {
                    $xs = $self->contracts->filter(fn (Contract $x): bool => $x->userId === $filterParams['userId']);
                    return FinderResult::from([...$xs], Pagination::create([]));
                })
                ->byDefault();

            $self->identifyLtcsAreaGradeFeeUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->fee))
                ->byDefault();

            $self->identifyLtcsInsCardUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, User $user, Carbon $targetDate) use ($self): Option {
                    return $self->insCards->find(fn (LtcsInsCard $x): bool => $x->userId === $user->id);
                })
                ->byDefault();

            $self->identifyUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, User $user, Carbon $targetDate) use ($self): Seq {
                    return $self->subsidies
                        ->filter(fn (UserLtcsSubsidy $x): bool => $x->userId === $user->id)
                        ->map(fn (UserLtcsSubsidy $x): Option => Option::some($x))
                        ->append([Option::none(), Option::none(), Option::none()])
                        ->take(3)
                        ->computed();
                })
                ->byDefault();

            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, Permission $permission, int ...$ids) use ($self): Seq {
                    return $self->users->filter(fn (User $x): bool => in_array($x->id, $ids, true));
                })
                ->byDefault();

            $self->ltcsBillingStatementRepository
                ->allows('store')
                ->andReturnUsing(fn (LtcsBillingStatement $x): LtcsBillingStatement => $x->copy([
                    'id' => $x->user->userId,
                ]))
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

            $self->interactor = app(CreateLtcsBillingStatementListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('identify LtcsAreaGradeFee using arguments', function (): void {
            $this->identifyLtcsAreaGradeFeeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->office->ltcsCareManagementService->ltcsAreaGradeId,
                    $this->bundle->providedIn
                )
                ->andReturn(Option::some($this->fee));

            $this->interactor->handle($this->context, $this->office, $this->bundle, $this->reports);
        });
        $this->should('throw SetupException when failed to identify the LtcsAreaGradeFee', function (): void {
            $this->identifyLtcsAreaGradeFeeUseCase->expects('handle')->andReturn(Option::none());
            $this->assertThrows(SetupException::class, function (): void {
                $this->interactor->handle($this->context, $this->office, $this->bundle, $this->reports);
            });
        });
        $this->should('lookup User for each userId in the bundle', function (): void {
            $users = $this->users->toMap('id');
            foreach ($users->keys() as $userId) {
                $this->lookupUserUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::createBillings(), $userId)
                    ->andReturn($users->get($userId)->toSeq());
            }

            $this->interactor->handle($this->context, $this->office, $this->bundle, $this->reports);

            $this->assertNotEmpty($users);
        });
        $this->should('throw NotFoundException when failed to lookup the user', function (): void {
            $this->lookupUserUseCase->expects('handle')->andReturn(Seq::empty());
            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->office, $this->bundle, $this->reports);
            });
        });
        // CreateLtcsBillingStatementUseCase の実装を利用しているため、リポジトリの store の回数で呼び出し回数のテストをしている.
        $this->should(
            'call store on LtcsBillingRepository number of times users included in details',
            function (): void {
                $this->ltcsBillingStatementRepository
                    ->expects('store')
                    ->andReturnUsing(fn (LtcsBillingStatement $x): LtcsBillingStatement => $x->copy([
                        'id' => $x->user->userId,
                    ]))
                    ->times(
                        Seq::from(...$this->bundle->details)
                            ->groupBy('userId')
                            ->values()
                            ->count()
                    );

                $this->interactor->handle($this->context, $this->office, $this->bundle, $this->reports);
            }
        );
        $this->should('return Seq of LtcsBillingStatement', function (): void {
            $actual = $this->interactor->handle($this->context, $this->office, $this->bundle, $this->reports);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof LtcsBillingStatement);
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
