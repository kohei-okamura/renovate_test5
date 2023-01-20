<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalResultCode;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DownloadStorageUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\ResolveUserBillingsFromZenginFormatUseCaseMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserBillingRepositoryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\ImportWithdrawalTransactionFileInteractor;

/**
 * {@link \UseCase\UserBilling\ImportWithdrawalTransactionFileInteractor} のテスト.
 */
final class ImportWithdrawalTransactionFileInteractorTest extends Test
{
    use ContextMixin;
    use CarbonMixin;
    use DownloadStorageUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupUserBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use ResolveUserBillingsFromZenginFormatUseCaseMixin;
    use TransactionManagerMixin;
    use UserBillingRepositoryMixin;
    use UnitSupport;

    private Carbon $deductedOn;
    private Map $userBillingIdToResultCodeMap;
    private SplFileInfo $splFileInfo;
    private string $path = 'path/to/file';
    private UserBilling $userBilling;
    private ImportWithdrawalTransactionFileInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->deductedOn = Carbon::parse('2020-10-10');
            $self->userBillingIdToResultCodeMap = Map::from([
                $self->examples->userBillings[0]->id => [WithdrawalResultCode::done(), $self->deductedOn],
                $self->examples->userBillings[1]->id => [WithdrawalResultCode::shortage(), $self->deductedOn],
            ]);
            $self->splFileInfo = new SplFileInfo('dummy');

            $self->downloadStorageUseCase
                ->allows('handle')
                ->andReturn($self->splFileInfo)
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0], $self->examples->userBillings[1]))
                ->byDefault();
            $self->resolveUserBillingsFromZenginFormatUseCase
                ->allows('handle')
                ->andReturn($self->userBillingIdToResultCodeMap)
                ->byDefault();
            $self->userBillingRepository
                ->allows('store')
                ->andReturn($self->examples->userBillings[0])
                ->byDefault();
            $self->userBillingRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(ImportWithdrawalTransactionFileInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use DownloadStorageUseCase after transaction begun', function (): void {
            $this->downloadStorageUseCase
                ->expects('handle')
                ->with($this->context, $this->path)
                ->andReturn($this->splFileInfo);

            $this->interactor->handle($this->context, $this->path);
        });
        $this->should('use LookupUserBillingUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupUserBillingUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::downloadWithdrawalTransactions(),
                            $this->examples->userBillings[0]->id,
                            $this->examples->userBillings[1]->id
                        )
                        ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[1]));
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->path);
        });
        $this->should('use UserRepository after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->userBillingRepository
                        ->expects('store')
                        ->with(equalTo($this->examples->userBillings[0]->copy([
                            'depositedAt' => $this->userBillingIdToResultCodeMap->get($this->examples->userBillings[0]->id)->orNull()[0] === WithdrawalResultCode::done()
                                ? $this->deductedOn
                                : null,
                            'result' => $this->userBillingIdToResultCodeMap->get($this->examples->userBillings[0]->id)->orNull()[0] === WithdrawalResultCode::done()
                                ? UserBillingResult::paid()
                                : UserBillingResult::unpaid(),
                            'withdrawalResultCode' => $this->userBillingIdToResultCodeMap->get($this->examples->userBillings[0]->id)->orNull()[0],
                            'transactedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->examples->userBillings[0]);
                    $this->userBillingRepository
                        ->expects('store')
                        ->with(equalTo($this->examples->userBillings[1]->copy([
                            'depositedAt' => $this->userBillingIdToResultCodeMap->get($this->examples->userBillings[1]->id)->orNull()[0] === WithdrawalResultCode::done()
                                ? $this->deductedOn
                                : null,
                            'result' => $this->userBillingIdToResultCodeMap->get($this->examples->userBillings[1]->id)->orNull()[0] === WithdrawalResultCode::done()
                                ? UserBillingResult::paid()
                                : UserBillingResult::unpaid(),
                            'withdrawalResultCode' => $this->userBillingIdToResultCodeMap->get($this->examples->userBillings[1]->id)->orNull()[0],
                            'transactedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->examples->userBillings[1]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->path);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('利用者請求が更新されました', ['ids' => $this->examples->userBillings[0]->id . ',' . $this->examples->userBillings[0]->id] + $context);

            $this->interactor->handle($this->context, $this->path);
        });
    }
}
