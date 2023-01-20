<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingBundleFinderMixin;
use Tests\Unit\Mixins\LtcsBillingStatementFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\GetLtcsBillingInfoInteractor;

/**
 * {@link \UseCase\Billing\GetLtcsBillingInfoInteractor} のテスト.
 */
final class GetLtcsBillingInfoInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsBillingBundleFinderMixin;
    use LtcsBillingStatementFinderMixin;
    use LookupLtcsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private GetLtcsBillingInfoInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillings[0]))
                ->byDefault();
            $self->ltcsBillingBundleFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->ltcsBillingBundles[1]), Pagination::create()))
                ->byDefault();
            $self->ltcsBillingStatementFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(Seq::from($self->examples->ltcsBillingStatements[2]), Pagination::create())
                )
                ->byDefault();

            $self->interactor = app(GetLtcsBillingInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('run normally', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->ltcsBillings[0]->id
            );

            $this->assertArrayHasKey('billing', $actual);
            $this->assertArrayHasKey('bundles', $actual);
            $this->assertArrayHasKey('statements', $actual);

            $this->assertModelStrictEquals($this->examples->ltcsBillings[0], $actual['billing']);
            $this->assertArrayStrictEquals([$this->examples->ltcsBillingBundles[1]], $actual['bundles']);
            $this->assertArrayStrictEquals([$this->examples->ltcsBillingStatements[2]], $actual['statements']);
        });
        $this->should('use LookupBillingUseCase', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->examples->ltcsBillings[0]->id)
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]));

            $this->interactor->handle(
                $this->context,
                $this->examples->ltcsBillings[0]->id
            );
        });
        $this->should('throw NotFoundException when LookupBillingUseCase return empty', function (): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->ltcsBillings[0]->id
                    );
                }
            );
        });
        $this->should('use LtcsBundleFinder', function (): void {
            $this->ltcsBillingBundleFinder
                ->expects('find')
                ->with(equalTo(
                    ['billingId' => $this->examples->ltcsBillings[0]->id]
                ), equalTo(
                    ['all' => true, 'sortBy' => 'id']
                ))
                ->andReturn(FinderResult::from(Seq::from($this->examples->ltcsBillingBundles[1]), Pagination::create()));

            $this->interactor->handle(
                $this->context,
                $this->examples->ltcsBillings[0]->id
            );
        });
        $this->should('use LtcsBillingStatementFinder', function (): void {
            $this->ltcsBillingStatementFinder
                ->expects('find')
                ->with(equalTo(
                    ['bundleIds' => [$this->examples->ltcsBillingBundles[1]->id]]
                ), equalTo(
                    ['all' => true, 'sortBy' => 'id']
                ))
                ->andReturn(FinderResult::from(Seq::from($this->examples->ltcsBillingStatements[1]), Pagination::create()));

            $this->interactor->handle(
                $this->context,
                $this->examples->ltcsBillings[0]->id
            );
        });
    }
}
