<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Calling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Calling\CallingLog;
use Domain\Calling\CallingType;
use Domain\Calling\FourthCallingEvent;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CallingLogRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\FindCallingUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Calling\SendFourthCallingInteractor;

/**
 * {@link \Usecase\Calling\SendFourthCallingInteractor} Test.
 */
class SendFourthCallingInteractorTest extends Test
{
    use CarbonMixin;
    use CallingLogRepositoryMixin;
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use FindCallingUseCaseMixin;
    use LoggerMixin;
    use LookupShiftUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CarbonRange $range;
    private SendFourthCallingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SendFourthCallingInteractorTest $self): void {
            $self->callingLogRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->callingLogRepository
                ->allows('store')
                ->andReturn($self->examples->callingLogs[0])
                ->byDefault();
            $self->eventDispatcher
                ->allows('dispatch')
                ->andReturnNull()
                ->byDefault();
            $self->findCallingUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from([$self->examples->callings[0]], Pagination::create()))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->shifts[0]))
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();

            $self->range = CarbonRange::create();
            $self->interactor = app(SendFourthCallingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use FindCallingUseCase', function (): void {
            $this->findCallingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewStaffs(),
                    [
                        'expiredRange' => $this->range,
                        'response' => false,
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'id',
                    ],
                )
                ->andReturn(FinderResult::from([$this->examples->callings[0]], Pagination::create()));

            $this->interactor->handle($this->context, $this->range);
        });
        $this->should('use LookupShiftUseCase', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewStaffs(), ...$this->examples->callings[0]->shiftIds)
                ->andReturn(Seq::from($this->examples->shifts[0]));

            $this->interactor->handle($this->context, $this->range);
        });
        $this->should('use LookupStaffUseCase', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewStaffs(), $this->examples->shifts[0]->assignerId)
                ->andReturn(Seq::from($this->examples->staffs[0]));

            $this->interactor->handle($this->context, $this->range);
        });
        $this->should('use EventDispatcher', function (): void {
            $this->eventDispatcher
                ->expects('dispatch')
                ->with(equalTo(new FourthCallingEvent($this->context, $this->examples->staffs[0])))
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->range);
        });
        $this->should('store the CallingLog after transaction begun', function () {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->callingLogRepository
                        ->expects('store')
                        ->with(
                            equalTo(CallingLog::create([
                                'callingId' => $this->examples->callings[0]->id,
                                'callingType' => CallingType::telephoneCallAssigner(),
                                'isSucceeded' => true,
                                'createdAt' => Carbon::now(),
                            ]))
                        )
                        ->andReturn($this->examples->callingLogs[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->range);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('出勤確認送信履歴が登録されました', ['id' => $this->examples->callingLogs[0]->id] + $context)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->range);
        });
    }
}
