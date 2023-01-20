<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\GetLtcsBillingInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateLtcsBillingStatusInteractor;

/**
 * {@link \UseCase\Billing\UpdateLtcsBillingStatusInteractor} のテスト.
 */
final class UpdateLtcsBillingStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use ExamplesConsumer;
    use GetLtcsBillingInfoUseCaseMixin;
    use LoggerMixin;
    use LookupLtcsBillingUseCaseMixin;
    use LtcsBillingRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private LtcsBilling $billing;
    private array $infoArray;
    private Closure $dispatchClosure;
    private DomainJob $domainJob;

    private UpdateLtcsBillingStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->domainJob = DomainJob::create();
            $self->infoArray = ['response-able' => true];
            $self->billing = $self->examples->ltcsBillings[1];
            $self->dispatchClosure = function (DomainJob $domainJob): void {
            };
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->ltcsBillingRepository
                ->allows('store')
                ->andReturn($self->billing)
                ->byDefault();
            $self->ltcsBillingRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->getLtcsBillingInfoUseCase
                ->allows('handle')
                ->andReturn($self->infoArray)
                ->byDefault();
            $self->createJobUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, Closure $f) use ($self): DomainJob {
                    $f($self->domainJob);
                    return $self->domainJob;
                })
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(UpdateLtcsBillingStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return response-able array', function (): void {
            $expected = $this->infoArray + ['job' => $this->domainJob];

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    LtcsBillingStatus::fixed(),
                    $this->dispatchClosure
                );

            $this->assertEquals($expected, $actual);
        });
        $this->should('use LookupLtcsBillingUseCase', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->billing->id
                )
                ->andReturn(Seq::from($this->billing));
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    LtcsBillingStatus::fixed(),
                    $this->dispatchClosure
                );
        });
        $this->should('throw NotFoundException when LookupUseCase return none', function (): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq())
                ->byDefault();

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor
                        ->handle(
                            $this->context,
                            $this->billing->id,
                            LtcsBillingStatus::fixed(),
                            $this->dispatchClosure
                        );
                }
            );
        });
        $this->should('use LtcsBillingRepository for updating entity', function (): void {
            $this->ltcsBillingRepository
                ->expects('store')
                ->andReturnUsing(function (LtcsBilling $actual): LtcsBilling {
                    $expected = $this->billing->copy([
                        'status' => LtcsBillingStatus::fixed(),
                        'updatedAt' => Carbon::now(),
                    ]);
                    $this->assertModelStrictEquals($expected, $actual);
                    return $actual;
                });
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    LtcsBillingStatus::fixed(),
                    $this->dispatchClosure
                );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with(
                    '介護保険サービス：請求が更新されました',
                    ['id' => $this->billing->id] + $context
                )
                ->andReturnNull();
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    LtcsBillingStatus::fixed(),
                    $this->dispatchClosure
                );
        });
    }
}
