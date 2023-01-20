<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\SetupException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\BuildLtcsServiceDetailListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyLtcsAreaGradeFeeUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\LtcsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UpdateLtcsBillingInvoiceListUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\RefreshLtcsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\RefreshLtcsBillingStatementInteractor} のテスト.
 */
final class RefreshLtcsBillingStatementInteractorTest extends Test
{
    use BuildLtcsBillingStatementUseCaseMixin;
    use BuildLtcsServiceDetailListUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use IdentifyLtcsAreaGradeFeeUseCaseMixin;
    use LoggerMixin;
    use LookupLtcsBillingBundleUseCaseMixin;
    use LookupLtcsBillingUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LtcsBillingBundleRepositoryMixin;
    use LtcsBillingStatementRepositoryMixin;
    use LtcsProvisionReportFinderMixin;
    use MockeryMixin;
    use SimpleLookupLtcsBillingStatementUseCaseMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UpdateLtcsBillingInvoiceListUseCaseMixin;

    private LtcsBilling $billing;
    private Seq $statements;
    private Seq $reports;
    private RefreshLtcsBillingStatementInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->ltcsProvisionReports, Pagination::create()))
                ->byDefault();
            $self->simpleLookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->ltcsBillingStatements[0],
                    $self->examples->ltcsBillingStatements[1]
                ))
                ->byDefault();
            $self->lookupLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillingBundles[0]->copy([
                    'details' => Seq::from(...$self->examples->ltcsBillingBundles[0]->details)
                        ->map(fn (LtcsBillingServiceDetail $x): LtcsBillingServiceDetail => $x->copy([
                            'userId' => 4,
                        ]))
                        ->toArray(),
                ])))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[3]))
                ->byDefault();
            $self->buildLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->examples->ltcsBillingStatements[0])
                ->byDefault();
            $self->identifyLtcsAreaGradeFeeUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->ltcsAreaGradeFees[0]))
                ->byDefault();
            $self->buildLtcsServiceDetailListUseCase
                ->allows('handle')
                ->andReturn(
                    Seq::from(...$self->examples->ltcsBillingBundles[0]->details)
                        ->map(fn (LtcsBillingServiceDetail $x): LtcsBillingServiceDetail => $x->copy(['userId' => 4]))
                        ->toArray()
                )
                ->byDefault();
            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillings[0]))
                ->byDefault();
            $self->updateLtcsBillingInvoiceListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillingInvoices[0]))
                ->byDefault();
            $self->ltcsBillingStatementRepository
                ->allows('store')
                ->andReturn($self->examples->ltcsBillingStatements[0])
                ->byDefault();
            $self->ltcsBillingStatementRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->ltcsBillingBundleRepository
                ->allows('store')
                ->andReturn($self->examples->ltcsBillingBundles[0])
                ->byDefault();
            $self->ltcsBillingBundleRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->billing = $self->examples->ltcsBillings[0];
            $self->statements = Seq::from(
                $self->examples->ltcsBillingStatements[0],
                $self->examples->ltcsBillingStatements[1],
            );
            $self->reports = Seq::fromArray($self->examples->ltcsProvisionReports);
            $self->interactor = app(RefreshLtcsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->specify('トランザクション内で実行される', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn([$this->examples->ltcsBillingBundles[0], Seq::from(...$this->statements)]);
            $this->ltcsProvisionReportFinder->expects('find')->never();
            $this->simpleLookupLtcsBillingStatementUseCase->expects('handle')->never();
            $this->lookupLtcsBillingBundleUseCase->expects('handle')->never();
            $this->lookupOfficeUseCase->expects('handle')->never();
            $this->lookupUserUseCase->expects('handle')->never();
            $this->buildLtcsBillingStatementUseCase->expects('handle')->never();
            $this->identifyLtcsAreaGradeFeeUseCase->expects('handle')->never();
            $this->buildLtcsServiceDetailListUseCase->expects('handle')->never();
            $this->lookupLtcsBillingUseCase->expects('handle')->never();
            $this->updateLtcsBillingInvoiceListUseCase->expects('handle')->never();
            $this->ltcsBillingStatementRepository->expects('store')->never();
            $this->ltcsBillingBundleRepository->expects('store')->never();

            $this->interactor->handle(
                context: $this->context,
                billingId: 14141356,
                statementIds: [1, 2, 3, 4, 5]
            );
        });
        $this->specify('引数で指定された LtcsBilling をリポジトリから取得する', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), 14141356)
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]));

            $this->interactor->handle(
                context: $this->context,
                billingId: 14141356,
                statementIds: [1, 2, 3, 4, 5]
            );
        });
        $this->specify('LtcsBilling が見つからないときは NotFoundException を投げる', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: [1, 2, 3, 4, 5]
                );
            });
        });
        $this->specify(
            '引数で指定された複数の LtcsBillingStatement をすべてリポジトリから取得する',
            function (): void {
                $ids = [1, 3, 5, 7, 9, 2, 4, 8];
                $this->simpleLookupLtcsBillingStatementUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::updateBillings(), ...$ids)
                    ->andReturn(Seq::from($this->examples->ltcsBillingStatements[0]));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: $ids
                );
            }
        );
        $this->specify(
            '取得した LtcsBillingStatement に対応する LtcsBillingBundle をすべてリポジトリから取得する',
            function (): void {
                $this->lookupLtcsBillingBundleUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        Permission::updateBillings(),
                        $this->billing,
                        ...$this->statements->map(fn (LtcsBillingStatement $x) => $x->bundleId)->distinct()
                    )
                    ->andReturn(Seq::from($this->examples->ltcsBillingBundles[0]->copy([
                        'details' => Seq::from(...$this->examples->ltcsBillingBundles[0]->details)
                            ->map(fn (LtcsBillingServiceDetail $x): LtcsBillingServiceDetail => $x->copy([
                                'userId' => 4,
                            ]))
                            ->toArray(),
                    ])));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: [1, 2, 3, 4, 5]
                );
            }
        );
        $this->specify('LtcsBillingBundle が1件も見つからないときは NotFoundException を投げる', function (): void {
            $this->lookupLtcsBillingBundleUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: [1, 2, 3, 4, 5]
                );
            });
        });
        $this->specify('取得した LtcsBilling に対応する Office をリポジトリから取得する', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::updateBillings()],
                    $this->billing->office->officeId
                )
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle(
                context: $this->context,
                billingId: 14141356,
                statementIds: [1, 2, 3, 4, 5]
            );
        });
        $this->specify('Office が見つからないときは NotFoundException を投げる', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: [1, 2, 3, 4, 5]
                );
            });
        });
        $this->specify(
            '取得した LtcsBillingStatement に対応する LtcsProvisionReport をすべて取得する',
            function (): void {
                $filterParams = [
                    'officeId' => $this->billing->office->officeId,
                    'userIds' => $this->statements->map(fn (LtcsBillingStatement $x) => $x->user->userId)->toArray(),
                    'providedIn' => $this->examples->ltcsBillingBundles[0]->providedIn,
                    'fixedAt' => CarbonRange::create([
                        'start' => $this->billing->transactedIn->subMonth()->day(11),
                        'end' => $this->billing->transactedIn->day(10)->endOfDay(),
                    ]),
                    'status' => LtcsProvisionReportStatus::fixed(),
                ];
                $paginationParams = [
                    'all' => true,
                    'sortBy' => 'id',
                ];
                $this->ltcsProvisionReportFinder
                    ->expects('find')
                    ->with(
                        $filterParams,
                        $paginationParams
                    )
                    ->andReturn(FinderResult::from($this->examples->ltcsProvisionReports, Pagination::create()));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: [1, 2, 3, 4, 5]
                );
            }
        );
        $this->specify('LtcsProvisionReport が1件も見つからない場合は NotFoundException を投げる', function (): void {
            $this->ltcsProvisionReportFinder
                ->expects('find')
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: [1, 2, 3, 4, 5]
                );
            });
        });
        $this->specify(
            '取得した LtcsProvisionReport の一覧に対応する LtcsBillingServiceDetail の一覧を組み立てる',
            function (): void {
                $this->buildLtcsServiceDetailListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        equalTo($this->examples->ltcsBillingBundles[0]->providedIn),
                        equalTo(Seq::from(...$this->examples->ltcsProvisionReports)),
                        equalTo(Seq::from($this->examples->users[3])),
                    )
                    ->andReturn(
                        Seq::from(...$this->examples->ltcsBillingBundles[0]->details)
                            ->map(fn (LtcsBillingServiceDetail $x): LtcsBillingServiceDetail => $x->copy([
                                'userId' => 4,
                            ]))
                            ->toArray()
                    );

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: [1, 2, 3, 4, 5]
                );
            }
        );
        $this->specify('Office に対応する LtcsAreaGradeFee を特定する', function (): void {
            $this->identifyLtcsAreaGradeFeeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId,
                    equalTo($this->examples->ltcsBillingBundles[0]->providedIn)
                )
                ->andReturn(Option::some($this->examples->ltcsAreaGradeFees[0]));

            $this->interactor->handle(
                context: $this->context,
                billingId: 14141356,
                statementIds: [1, 2, 3, 4, 5]
            );
        });
        $this->specify('LtcsAreaGradeFee が見つからないときは SetupException を投げる', function (): void {
            $this->identifyLtcsAreaGradeFeeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId,
                    equalTo($this->examples->ltcsBillingBundles[0]->providedIn)
                )
                ->andReturn(Option::none());

            $this->assertThrows(SetupException::class, function (): void {
                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    statementIds: [1, 2, 3, 4, 5]
                );
            });
        });
        $this->specify('LtcsBillingStatement を組み立てる', function (): void {
            $newDetails = Seq::from(...$this->examples->ltcsBillingBundles[0]->details)
                ->map(fn (LtcsBillingServiceDetail $x): LtcsBillingServiceDetail => $x->copy([
                    'userId' => 4,
                ]))
                ->toArray();
            $userIdToDetailsMap = Seq::from(...$newDetails)->groupBy('userId');
            $updateDetails = Seq::from(...$newDetails)
                ->groupBy('userId')
                ->mapValues(fn (Seq $xs, int $userId): Seq => $userIdToDetailsMap->getOrElse(
                    $userId,
                    fn (): Seq => $xs
                ))
                ->values()
                ->flatten();
            $updateBundle = $this->examples->ltcsBillingBundles[0]->copy([
                'details' => $updateDetails->toArray(),
                'updatedAt' => Carbon::now(),
            ]);
            $this->buildLtcsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo($updateBundle),
                    $this->examples->users[3],
                    $this->examples->offices[0],
                    equalTo($updateDetails->computed()),
                    $this->examples->ltcsAreaGradeFees[0]->fee,
                    equalTo(Seq::from(...$this->examples->ltcsProvisionReports)),
                )
                ->andReturn($this->examples->ltcsBillingStatements[0]);

            $this->interactor->handle(
                context: $this->context,
                billingId: 14141356,
                statementIds: [1, 2, 3, 4, 5]
            );
        });
        $this->specify('LtcsBillingBundle をリポジトリに格納する', function (): void {
            $newDetails = Seq::from(...$this->examples->ltcsBillingBundles[0]->details)
                ->map(fn (LtcsBillingServiceDetail $x): LtcsBillingServiceDetail => $x->copy([
                    'userId' => 4,
                ]))
                ->toArray();
            $userIdToDetailsMap = Seq::from(...$newDetails)->groupBy('userId');
            $updateDetails = Seq::from(...$newDetails)
                ->groupBy('userId')
                ->mapValues(fn (Seq $xs, int $userId): Seq => $userIdToDetailsMap->getOrElse(
                    $userId,
                    fn (): Seq => $xs
                ))
                ->values()
                ->flatten();
            $updateBundle = $this->examples->ltcsBillingBundles[0]->copy([
                'details' => $updateDetails->toArray(),
                'updatedAt' => Carbon::now(),
            ]);
            $this->ltcsBillingBundleRepository
                ->expects('store')
                ->with(equalTo($updateBundle))
                ->andReturn($this->examples->ltcsBillingBundles[0]);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->statements->map(fn (LtcsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->specify('LtcsBillingStatement をリポジトリに格納する', function (): void {
            $newDetails = Seq::from(...$this->examples->ltcsBillingBundles[0]->details)
                ->map(fn (LtcsBillingServiceDetail $x): LtcsBillingServiceDetail => $x->copy([
                    'userId' => 4,
                ]))
                ->toArray();
            $userIdToDetailsMap = Seq::from(...$newDetails)
                ->groupBy('userId');
            $updateDetails = Seq::from(...$newDetails)
                ->groupBy('userId')
                ->mapValues(fn (Seq $xs, int $userId): Seq => $userIdToDetailsMap->getOrElse(
                    $userId,
                    fn (): Seq => $xs
                ))
                ->values()
                ->flatten();
            $updateStatement = $updateDetails
                ->groupBy('userId')
                ->mapValues(fn (
                    Seq $xs,
                    int $userId
                ): LtcsBillingStatement => $this->examples->ltcsBillingStatements[0])
                ->getOrElse(4, fn () => null);
            $this->ltcsBillingStatementRepository
                ->expects('store')
                ->with(equalTo($this->statements[0]->copy(
                    [
                        'insurerNumber' => $updateStatement->insurerNumber,
                        'insurerName' => $updateStatement->insurerName,
                        'user' => $updateStatement->user,
                        'carePlanAuthor' => $updateStatement->carePlanAuthor,
                        'agreedOn' => $updateStatement->agreedOn,
                        'expiredOn' => $updateStatement->expiredOn,
                        'expiredReason' => $updateStatement->expiredReason,
                        'insurance' => $updateStatement->insurance,
                        'subsidies' => $updateStatement->subsidies,
                        'items' => $updateStatement->items,
                        'aggregates' => $updateStatement->aggregates,
                        'status' => LtcsBillingStatus::ready(),
                        'fixedAt' => $updateStatement->fixedAt,
                        'updatedAt' => Carbon::now(),
                    ]
                )))
                ->andReturn($this->examples->ltcsBillingStatements[0]);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->statements->map(fn (LtcsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->specify('処理結果をログに出力する', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $statementIds = implode(
                ',',
                [$this->examples->ltcsBillingStatements[0]->id, $this->examples->ltcsBillingStatements[1]->id]
            );
            $this->ltcsBillingStatementRepository
                ->expects('store')
                ->andReturn($this->examples->ltcsBillingStatements[0]);
            $this->ltcsBillingStatementRepository
                ->expects('store')
                ->andReturn($this->examples->ltcsBillingStatements[1]);
            $this->context
                ->expects('logContext')
                ->andReturn($context)
                ->twice();
            $this->logger
                ->expects('info')
                ->with(
                    '介護保険サービス：請求単位が更新されました',
                    ['id' => $this->examples->ltcsBillingBundles[0]->id] + $context
                );
            $this->logger
                ->expects('info')
                ->with('介護保険サービス：明細書が更新されました', ['id' => $statementIds] + $context);
            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->statements->map(fn (LtcsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->specify('throw NotFoundException when UserMap return empty', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->billing->id,
                    $this->statements->map(fn (LtcsBillingStatement $x): int => $x->id)->toArray()
                );
            });
        });
    }
}
