<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingOffice;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\EnsureDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\GetDwsBillingCopayCoordinationInfoUseCaseMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\EditDwsBillingCopayCoordinationInteractor;

/**
 * {@link \UseCase\Billing\EditDwsBillingCopayCoordinationInteractor} のテスト.
 */
final class EditDwsBillingCopayCoordinationInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingCopayCoordinationRepositoryMixin;
    use DwsBillingStatementFinderMixin;
    use EnsureDwsBillingBundleUseCaseMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use GetDwsBillingCopayCoordinationInfoUseCaseMixin;
    use GetOfficeListUseCaseMixin;
    use LoggerMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsBillingCopayCoordination $copayCoordination;
    private Office $serviceOffice;
    private array $inputItemAssoc;

    private EditDwsBillingCopayCoordinationInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->copayCoordination = $self->examples->dwsBillingCopayCoordinations[0];
            $self->serviceOffice = $self->examples->offices[2];
            $self->inputItemAssoc = [
                'itemNumber' => 3,
                'officeId' => $self->serviceOffice->id,
                'subtotal' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 20000,
                    'copay' => 10000,
                    'coordinatedCopay' => 9000,
                ]),
            ];

            $self->ensureDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->getDwsBillingCopayCoordinationInfoUseCase
                ->allows('handle')
                ->andReturn(['result' => 'data'])
                ->byDefault();
            $self->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->serviceOffice))
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->copayCoordination))
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('store')
                ->andReturnUsing(fn (DwsBillingCopayCoordination $x): DwsBillingCopayCoordination => $x)
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->dwsBillingStatements[0]), Pagination::create()))
                ->byDefault();

            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(EditDwsBillingCopayCoordinationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function test_handle(): void
    {
        $this->should('run normally', function (): void {
            $this->assertSame(
                ['result' => 'data'],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->examples->dwsBillingBundles[1]->id,
                    $this->examples->dwsBillingCopayCoordinations[2]->id,
                    $this->copayCoordination->user->userId,
                    CopayCoordinationResult::coordinated(),
                    DwsBillingCopayCoordinationExchangeAim::declaration(),
                    [$this->inputItemAssoc]
                )
            );
        });
        $this->should('use EnsureDwsBillingBundleUseCase', function (): void {
            $this->ensureDwsBillingBundleUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->examples->dwsBillings[0]->id,
                    $this->examples->dwsBillingBundles[1]->id
                )
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingBundles[1]->id,
                $this->examples->dwsBillingCopayCoordinations[2]->id,
                $this->copayCoordination->user->userId,
                CopayCoordinationResult::coordinated(),
                DwsBillingCopayCoordinationExchangeAim::declaration(),
                [$this->inputItemAssoc]
            );
        });
        $this->should('use EnsureUserUseCase', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->copayCoordination->user->userId
                )
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingBundles[1]->id,
                $this->examples->dwsBillingCopayCoordinations[2]->id,
                $this->copayCoordination->user->userId,
                CopayCoordinationResult::coordinated(),
                DwsBillingCopayCoordinationExchangeAim::declaration(),
                [$this->inputItemAssoc]
            );
        });
        $this->should('use GetOfficeListUseCase', function (): void {
            $this->getOfficeListUseCase
                ->expects('handle')
                ->with($this->context, $this->serviceOffice->id)
                ->andReturn(Seq::from($this->serviceOffice));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingBundles[1]->id,
                $this->examples->dwsBillingCopayCoordinations[2]->id,
                $this->copayCoordination->user->userId,
                CopayCoordinationResult::coordinated(),
                DwsBillingCopayCoordinationExchangeAim::declaration(),
                [$this->inputItemAssoc]
            );
        });
        $this->should('lookup entity via Repository', function (): void {
            $this->dwsBillingCopayCoordinationRepository
                ->expects('lookup')
                ->with($this->examples->dwsBillingCopayCoordinations[2]->id)
                ->andReturn(Seq::from($this->copayCoordination));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingBundles[1]->id,
                $this->examples->dwsBillingCopayCoordinations[2]->id,
                $this->copayCoordination->user->userId,
                CopayCoordinationResult::coordinated(),
                DwsBillingCopayCoordinationExchangeAim::declaration(),
                [$this->inputItemAssoc]
            );
        });
        $this->should('store entity via repository', function (): void {
            $updateResult = CopayCoordinationResult::coordinated();
            $this->dwsBillingCopayCoordinationRepository
                ->expects('store')
                ->andReturnUsing(function (DwsBillingCopayCoordination $actual) use ($updateResult): DwsBillingCopayCoordination {
                    $expect = $this->copayCoordination->copy([
                        'result' => $updateResult,
                        'items' => [
                            DwsBillingCopayCoordinationItem::create([
                                'itemNumber' => $this->inputItemAssoc['itemNumber'],
                                'office' => DwsBillingOffice::from($this->serviceOffice),
                                'subtotal' => $this->inputItemAssoc['subtotal'],
                            ]),
                        ],
                        'total' => $this->inputItemAssoc['subtotal'],
                        'updatedAt' => Carbon::now(),
                    ]);
                    $this->assertModelStrictEquals($expect, $actual);
                    return $actual;
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->examples->dwsBillingBundles[1]->id,
                $this->examples->dwsBillingCopayCoordinations[2]->id,
                $this->copayCoordination->user->userId,
                $updateResult,
                DwsBillingCopayCoordinationExchangeAim::declaration(),
                [$this->inputItemAssoc]
            );
        });
        $this->should('throw NotFoundException when userId is invalid', function (): void {
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->dwsBillings[0]->id,
                        $this->examples->dwsBillingBundles[1]->id,
                        $this->examples->dwsBillingCopayCoordinations[2]->id,
                        self::NOT_EXISTING_ID, // DwsBillingCopayCoordination のUserではない
                        CopayCoordinationResult::coordinated(),
                        DwsBillingCopayCoordinationExchangeAim::declaration(),
                        [$this->inputItemAssoc]
                    );
                }
            );
        });
        $this->should('throw NotFoundException when Office is invalid', function (): void {
            $this->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->dwsBillings[0]->id,
                        $this->examples->dwsBillingBundles[1]->id,
                        $this->examples->dwsBillingCopayCoordinations[2]->id,
                        $this->examples->dwsBillingCopayCoordinations[2]->user->userId,
                        CopayCoordinationResult::coordinated(),
                        DwsBillingCopayCoordinationExchangeAim::declaration(),
                        [$this->inputItemAssoc]
                    );
                }
            );
        });
    }
}
