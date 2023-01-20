<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingStatus;
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
use Tests\Unit\Mixins\DwsBillingRepositoryMixin;
use Tests\Unit\Mixins\GetDwsBillingInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateDwsBillingStatusInteractor;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingStatusInteractor} のテスト.
 */
final class UpdateDwsBillingStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use DwsBillingRepositoryMixin;
    use ExamplesConsumer;
    use GetDwsBillingInfoUseCaseMixin;
    use LoggerMixin;
    use LookupDwsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private array $infoArray;
    private Closure $dispatchClosure;
    private DomainJob $domainJob;

    private UpdateDwsBillingStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = DomainJob::create();
            $self->infoArray = ['response-able' => true];
            $self->billing = $self->examples->dwsBillings[1];
            $self->dispatchClosure = function (DomainJob $domainJob): void {
            };

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->dwsBillingRepository
                ->allows('store')
                ->andReturn($self->billing)
                ->byDefault();
            $self->dwsBillingRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->getDwsBillingInfoUseCase
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

            $self->interactor = app(UpdateDwsBillingStatusInteractor::class);
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
                    DwsBillingStatus::fixed(),
                    $this->dispatchClosure
                );

            $this->assertEquals($expected, $actual);
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupDwsBillingUseCase
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
                    DwsBillingStatus::fixed(),
                    $this->dispatchClosure
                );
        });
        $this->should('throw NotFoundException when LookupUseCase return none', function (): void {
            $this->lookupDwsBillingUseCase
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
                            DwsBillingStatus::fixed(),
                            $this->dispatchClosure
                        );
                }
            );
        });
        $this->should('use DwsBillingRepository for updating entity', function (): void {
            $this->dwsBillingRepository
                ->expects('store')
                ->andReturnUsing(function (DwsBilling $actual): DwsBilling {
                    $expected = $this->billing->copy([
                        'status' => DwsBillingStatus::fixed(),
                        'updatedAt' => Carbon::now(),
                    ]);
                    $this->assertModelStrictEquals($expected, $actual);
                    return $actual;
                });
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    DwsBillingStatus::fixed(),
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
                    '障害福祉サービス：請求が更新されました',
                    ['id' => $this->billing->id] + $context
                )
                ->andReturnNull();
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    DwsBillingStatus::fixed(),
                    $this->dispatchClosure
                );
        });
        $this->should('use CreateJobUseCase', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                ->andReturnUsing(function (Context $context, Closure $f): DomainJob {
                    $this->assertSame($this->context, $context);
                    $this->assertEquals($this->dispatchClosure, $f);
                    $f($this->domainJob);
                    return $this->domainJob;
                });

            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    DwsBillingStatus::fixed(),
                    $this->dispatchClosure
                );
        });
        $this->should('not use CreateJobUseCase when status is not fixed', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                ->times(0);

            $response = $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    DwsBillingStatus::ready(),
                    $this->dispatchClosure
                );

            $this->assertArrayNotHasKey('job', $response);
        });
    }
}
