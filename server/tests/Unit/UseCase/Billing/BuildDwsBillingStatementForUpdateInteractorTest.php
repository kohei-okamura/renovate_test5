<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsBillingStatement;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationFinderMixin;
use Tests\Unit\Mixins\DwsBillingRepositoryMixin;
use Tests\Unit\Mixins\IdentifyHomeHelpServiceCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\IdentifyVisitingCareForPwsdCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\UserRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingStatementForUpdateInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementForUpdateInteractor} のテスト.
 */
final class BuildDwsBillingStatementForUpdateInteractorTest extends Test
{
    use BuildDwsBillingStatementUseCaseMixin;
    use ContextMixin;
    use DwsBillingBundleRepositoryMixin;
    use DwsBillingCopayCoordinationFinderMixin;
    use DwsBillingRepositoryMixin;
    use ExamplesConsumer;
    use IdentifyHomeHelpServiceCalcSpecUseCaseMixin;
    use IdentifyVisitingCareForPwsdCalcSpecUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use UnitSupport;
    use UserRepositoryMixin;

    private DwsBillingStatement $billingStatement;
    private BuildDwsBillingStatementForUpdateInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billingStatement = $self->examples->dwsBillingStatements[0];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBillingRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->dwsBillingBundleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[0]))
                ->byDefault();
            $self->dwsBillingCopayCoordinationFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->dwsBillingCopayCoordinations[0]), Pagination::create()))
                ->byDefault();
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->identifyHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->homeHelpServiceCalcSpecs[0]))
                ->byDefault();
            $self->identifyVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->visitingCareForPwsdCalcSpecs[0]))
                ->byDefault();
            $self->userRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->buildDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->billingStatement)
                ->byDefault();

            $self->interactor = app(BuildDwsBillingStatementForUpdateInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Statement', function (): void {
            $this->assertModelStrictEquals(
                $this->billingStatement,
                $this->interactor->handle($this->context, $this->examples->dwsBillingStatements[2])
            );
        });

        $this->should('serviceDetail is only one user', function (): void {
            $this->dwsBillingBundleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->examples->dwsBillingBundles[6]))
                ->byDefault();
            $this->buildDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0],
                    $this->examples->dwsBillingBundles[6],
                    $this->examples->homeHelpServiceCalcSpecs[0],
                    $this->examples->visitingCareForPwsdCalcSpecs[0],
                    $this->examples->users[0],
                    Mockery::capture($actual),
                    Mockery::capture($actualCopayCoordination),
                    Mockery::capture($actualStatement),
                )
                ->andReturn($this->billingStatement);

            $this->interactor->handle($this->context, $this->examples->dwsBillingStatements[2]);
            $this->assertForAll($actual, fn (DwsBillingServiceDetail $x): bool => $x->userId === $this->examples->users[0]->id);
            $this->assertModelStrictEquals($actualCopayCoordination->get(), $this->examples->dwsBillingCopayCoordinations[0]);
            $this->assertModelStrictEquals($actualStatement->get(), $this->examples->dwsBillingStatements[2]);
        });

        $this->should('use DwsBillingCopayCoordinationFinder', function (): void {
            $filterParams = [
                'dwsBillingId' => $this->examples->dwsBillingBundles[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                'userIds' => [$this->examples->users[0]->id],
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsBillingCopayCoordinationFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from(Seq::from($this->examples->dwsBillingCopayCoordinations[0]), Pagination::create()));

            $this->interactor->handle($this->context, $this->examples->dwsBillingStatements[0]);
        });
    }

    // TODO: テストが足りてないのでどこかで直したい。
}
