<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsServiceDetailListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateLtcsBillingBundleInteractor;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingBundleInteractor} のテスト.
 */
final class CreateLtcsBillingBundleInteractorTest extends Test
{
    use BuildLtcsServiceDetailListUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsBillingBundleRepositoryMixin;
    use LookupUserUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Carbon $carbon;
    private LtcsBilling $billing;
    private LtcsBillingBundle $bundle;
    private Seq $users;

    private CreateLtcsBillingBundleInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (CreateLtcsBillingBundleInteractorTest $self): void {
            $self->carbon = Carbon::create(2008, 5, 17);
            $self->billing = $self->examples->ltcsBillings[0];
            $self->bundle = $self->examples->ltcsBillingBundles[0];
        });
        self::beforeEachSpec(function (CreateLtcsBillingBundleInteractorTest $self): void {
            $self->users = Seq::from($self->examples->users[0]);
            $self->buildLtcsServiceDetailListUseCase
                ->allows('handle')
                ->andReturn($self->bundle->details)
                ->byDefault();

            $self->ltcsBillingBundleRepository
                ->allows('store')
                ->andReturn($self->bundle)
                ->byDefault();

            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();

            $self->interactor = app(CreateLtcsBillingBundleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('should run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn($this->examples->ltcsBillingBundles[0]);
            $this->buildLtcsServiceDetailListUseCase->expects('handle')->never();
            $this->ltcsBillingBundleRepository->expects('store')->never();

            $this->interactor->handle($this->context, $this->billing, $this->carbon, Seq::emptySeq());
        });
        $this->should('create service details using CreateLtcsServiceDetailListUseCase', function (): void {
            $providedIn = $this->carbon;
            $reports = Seq::from(...$this->examples->ltcsProvisionReports);
            $this->buildLtcsServiceDetailListUseCase
                ->expects('handle')
                ->with($this->context, $providedIn, $reports, Mockery::capture($actual))
                ->andReturn($this->bundle->details);

            $this->interactor->handle($this->context, $this->billing, $providedIn, $reports);
            $this->assertArrayStrictEquals($this->users->toArray(), $actual->toArray());
        });
        $this->should('store the bundle to repository', function (): void {
            $providedIn = $this->carbon;
            $reports = Seq::from(...$this->examples->ltcsProvisionReports);
            $bundle = LtcsBillingBundle::create([
                'billingId' => $this->billing->id,
                'providedIn' => $providedIn,
                'details' => $this->bundle->details,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $this->ltcsBillingBundleRepository
                ->expects('store')
                ->withArgs(fn (LtcsBillingBundle $x): bool => $x->equals($bundle))
                ->andReturn($bundle);
            $this->interactor->handle($this->context, $this->billing, $providedIn, $reports);
        });
        $this->should('return the bundle', function (): void {
            $expected = $this->examples->ltcsBillingBundles[2];
            $this->ltcsBillingBundleRepository->expects('store')->andReturn($expected);

            $actual = $this->interactor->handle($this->context, $this->billing, $this->carbon, Seq::emptySeq());

            $this->assertModelStrictEquals($expected, $actual);
        });
        $this->should('use LookupUserUseCase', function (): void {
            $providedIn = $this->carbon;
            $reports = Seq::from(...$this->examples->ltcsProvisionReports);
            $userIds = $reports->map(fn (LtcsProvisionReport $x): int => $x->userId)->toArray();
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::createBillings(), ...$userIds)
                ->andReturn($this->users);
            $this->interactor->handle($this->context, $this->billing, $providedIn, $reports);
        });
    }
}
