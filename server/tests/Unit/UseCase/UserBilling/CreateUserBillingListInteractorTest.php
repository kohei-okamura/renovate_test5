<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use PHPUnit\Framework\AssertionFailedError;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateUserBillingUseCaseMixin;
use Tests\Unit\Mixins\DwsBillingBundleFinderMixin;
use Tests\Unit\Mixins\DwsBillingFinderMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\DwsProvisionReportFinderMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingBundleFinderMixin;
use Tests\Unit\Mixins\LtcsBillingFinderMixin;
use Tests\Unit\Mixins\LtcsBillingStatementFinderMixin;
use Tests\Unit\Mixins\LtcsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\CreateUserBillingListInteractor;

/**
 * {@link \UseCase\UserBilling\CreateUserBillingListInteractor} のテスト.
 */
final class CreateUserBillingListInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateUserBillingUseCaseMixin;
    use DwsBillingBundleFinderMixin;
    use DwsBillingFinderMixin;
    use DwsBillingStatementFinderMixin;
    use DwsProvisionReportFinderMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LtcsBillingBundleFinderMixin;
    use LtcsBillingFinderMixin;
    use LtcsBillingStatementFinderMixin;
    use LtcsProvisionReportFinderMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateUserBillingListInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserBillingListInteractorTest $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::fromArray($self->examples->users))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::fromArray($self->examples->offices))
                ->byDefault();
            $self->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->dwsProvisionReports, Pagination::create([])))
                ->byDefault();
            $self->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->ltcsProvisionReports, Pagination::create([])))
                ->byDefault();
            $self->createUserBillingUseCase
                ->allows('handle')
                ->andReturn($self->examples->userBillings[0])
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->dwsBillingStatements, Pagination::create([])))
                ->byDefault();
            $self->ltcsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->ltcsBillingStatements, Pagination::create([])))
                ->byDefault();
            $self->dwsBillingBundleFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->dwsBillingBundles, Pagination::create([])))
                ->byDefault();
            $self->ltcsBillingBundleFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->ltcsBillingBundles, Pagination::create([])))
                ->byDefault();
            $self->dwsBillingFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->dwsBillings, Pagination::create([])))
                ->byDefault();
            $self->ltcsBillingFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->ltcsBillings, Pagination::create([])))
                ->byDefault();
            $self->logger
                ->allows('error')
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(CreateUserBillingListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn(Seq::empty());
            $this->lookupUserUseCase
                ->expects('handle')
                ->never();
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->never();
            $this->createUserBillingUseCase
                ->allows('handle')
                ->never();

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use createUserBillingUseCase two times', function (): void {
            $this->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->dwsProvisionReports[0],
                            $this->examples->dwsProvisionReports[1],
                        ],
                        Pagination::create([])
                    )
                );
            $this->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from([], Pagination::create([])));
            $this->createUserBillingUseCase
                ->expects('handle')
                ->andReturn($this->examples->userBillings[0])
                ->twice();

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use lookupUserUseCase', function (): void {
            $this->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->dwsProvisionReports[0],
                        ],
                        Pagination::create([])
                    )
                );
            $this->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->ltcsProvisionReports[0],
                        ],
                        Pagination::create([])
                    )
                );

            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::createUserBillings(), $this->examples->dwsProvisionReports[0]->userId)
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use lookupOfficeUseCase', function (): void {
            $this->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->dwsProvisionReports[0],
                        ],
                        Pagination::create([])
                    )
                );
            $this->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->ltcsProvisionReports[0],
                        ],
                        Pagination::create([])
                    )
                );

            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::createUserBillings()], $this->examples->dwsProvisionReports[0]->officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use dwsBillingFinder', function (): void {
            $filterParams = [
                'organizationId' => $this->context->organization->id,
                'transactedIn' => Carbon::now()->addMonth()->firstOfMonth(),
                'status' => DwsBillingStatus::fixed(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
                'desc' => true,
            ];
            $this->dwsBillingFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->examples->dwsBillings, Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use ltcsBillingFinder', function (): void {
            $filterParams = [
                'organizationId' => $this->context->organization->id,
                'transactedIn' => Carbon::now()->addMonth()->firstOfMonth(),
                'status' => LtcsBillingStatus::fixed(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
                'desc' => true,
            ];
            $this->ltcsBillingFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->examples->ltcsBillings, Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use dwsBillingBundleFinder', function (): void {
            $ids = Seq::fromArray($this->examples->dwsBillings)
                ->distinctBy(function (DwsBilling $x) {
                    return $x->office->officeId;
                })
                ->map(function (DwsBilling $x) {
                    return $x->id;
                })
                ->distinct();
            $filterParams = [
                'organizationId' => $this->context->organization->id,
                'providedIn' => Carbon::now(),
                'dwsBillingIds' => $ids->toArray(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsBillingBundleFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->examples->dwsBillingBundles, Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use ltcsBillingBundleFinder', function (): void {
            $ids = Seq::fromArray($this->examples->ltcsBillings)
                ->distinctBy(function (LtcsBilling $x) {
                    return $x->office->officeId;
                })
                ->map(function (LtcsBilling $x) {
                    return $x->id;
                })
                ->distinct();
            $filterParams = [
                'organizationId' => $this->context->organization->id,
                'providedIn' => Carbon::now(),
                'billingIds' => $ids->toArray(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->ltcsBillingBundleFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->examples->ltcsBillingBundles, Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use DwsBillingStatementFinder', function (): void {
            $filterParams = [
                'dwsBillingBundleIds' => Seq::fromArray($this->examples->dwsBillingBundles)
                    ->map(function (DwsBillingBundle $x) {
                        return $x->id;
                    })
                    ->distinct()
                    ->toArray(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->examples->dwsBillingStatements, Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use LtcsBillingStatementFinder', function (): void {
            $filterParams = [
                'bundleIds' => Seq::fromArray($this->examples->ltcsBillingBundles)
                    ->map(function (LtcsBillingBundle $x) {
                        return $x->id;
                    })
                    ->distinct()
                    ->toArray(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->ltcsBillingStatementFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->examples->ltcsBillingStatements, Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use dwsProvisionReportFinder', function (): void {
            $filterParams = [
                'providedIn' => Carbon::now(),
                'status' => DwsProvisionReportStatus::fixed(),
                'organizationId' => $this->context->organization->id,
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->examples->dwsProvisionReports, Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('use ltcsProvisionReportFinder', function (): void {
            $filterParams = [
                'providedIn' => Carbon::now(),
                'status' => LtcsProvisionReportStatus::fixed(),
                'organizationId' => $this->context->organization->id,
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->ltcsProvisionReportFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->examples->ltcsProvisionReports, Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('not throw an LogicException when createUserBillingUseCase throws it', function (): void {
            $this->createUserBillingUseCase
                ->expects('handle')
                ->andThrow(LogicException::class);
            $this->createUserBillingUseCase
                ->expects('handle')
                ->andReturn($this->examples->userBillings[0])
                ->times(6);

            try {
                $this->interactor->handle(
                    $this->context,
                    Carbon::now(),
                );
                $this->assertTrue(true);
            } catch (AssertionFailedError $error) {
                throw $error;
            } catch (LogicException $e) {
                $this->fail('must not throw LogicException');
            }
        });
        $this->should('throw an NotFoundException when lookupUserUseCase return empty', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->createUserBillingUseCase
                ->expects('handle')
                ->never();

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    Carbon::now(),
                );
            });
        });
        $this->should('throw an NotFoundException when lookupOfficeUseCase return empty', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->createUserBillingUseCase
                ->expects('handle')
                ->never();

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    Carbon::now(),
                );
            });
        });
        $this->should('log using error', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $user = $this->examples->users[0];
            $office = $this->examples->offices[0];
            $e = new LogicException("Cannot build UserBilling User（{$user->id}） Office（{$office->id}）");
            $this->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->dwsProvisionReports[0],
                        ],
                        Pagination::create([])
                    )
                );
            $this->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->ltcsProvisionReports[0],
                        ],
                        Pagination::create([])
                    )
                );
            $this->context
                ->allows('logContext')
                ->andReturn($context);
            $this->createUserBillingUseCase
                ->expects('handle')
                ->andThrow($e);
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->logger
                ->expects('error')
                ->with($e);

            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $user = $this->examples->users[0];
            $this->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->dwsProvisionReports[0],
                        ],
                        Pagination::create([])
                    )
                );
            $this->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->ltcsProvisionReports[0],
                        ],
                        Pagination::create([])
                    )
                );
            $this->context
                ->allows('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with(
                    '利用者請求生成開始',
                    ['userId' => $user->id] + $context
                );
            $this->logger
                ->expects('info')
                ->with(
                    '利用者請求生成：障害福祉サービス予実',
                    ['id' => $this->examples->dwsProvisionReports[0]->id] + $context
                );
            $this->logger
                ->expects('info')
                ->with(
                    '利用者請求生成：介護保険サービス予実',
                    ['id' => $this->examples->ltcsProvisionReports[0]->id] + $context
                );
            $this->logger
                ->expects('info')
                ->with(
                    '利用者請求生成：障害福祉サービス明細書',
                    ['id' => $this->examples->dwsBillingStatements[0]->id] + $context
                );
            $this->logger
                ->expects('info')
                ->with(
                    '利用者請求生成：介護保険サービス明細書',
                    ['id' => ''] + $context
                );
            $this->logger
                ->expects('info')
                ->with(
                    '利用者請求生成終了',
                    ['userId' => $user->id] + $context
                );
            $this->interactor->handle(
                $this->context,
                Carbon::now(),
            );
        });
    }
}
