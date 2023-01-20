<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Calling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Calling\CallingLog;
use Domain\Calling\FirstCallingEvent;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CallingLogRepositoryMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\FindCallingUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UrlBuilderMixin;
use Tests\Unit\Test;
use UseCase\Calling\SendFirstCallingInteractor;

/**
 * {@link \Usecase\Calling\SendFirstCallingInteractor} Test.
 */
class SendFirstCallingInteractorTest extends Test
{
    use CallingLogRepositoryMixin;
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use FindCallingUseCaseMixin;
    use LoggerMixin;
    use LookupStaffUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UrlBuilderMixin;

    private const URL = 'https://hoge.test/callings/token';
    private SendFirstCallingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SendFirstCallingInteractorTest $self): void {
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
                ->andReturn(FinderResult::from(Seq::from($self->examples->callings[1]), Pagination::create()))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->urlBuilder
                ->allows('build')
                ->andReturn(self::URL)
                ->byDefault();

            $self->interactor = app(SendFirstCallingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('process succeed', function (): void {
            $range = CarbonRange::create();
            $this->findCallingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listShifts(),
                    equalTo([
                        'expiredRange' => $range,
                        'response' => false,
                    ]),
                    equalTo([
                        'all' => true,
                        'sortBy' => 'id',
                    ]),
                )
                ->andReturn(FinderResult::from(Seq::from($this->examples->callings[0]), Pagination::create()));
            $this->eventDispatcher
                ->expects('dispatch')
                ->with(equalTo(new FirstCallingEvent(
                    $this->context,
                    $this->examples->callings[0],
                    $this->examples->staffs[0],
                    self::URL
                )));

            $this->interactor->handle($this->context, $range);
        });
        $this->should('not operate anything when Calling and Staff are not related.', function (): void {
            $range = CarbonRange::create();
            $this->findCallingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listShifts(),
                    equalTo([
                        'expiredRange' => $range,
                        'response' => false,
                    ]),
                    equalTo([
                        'all' => true,
                        'sortBy' => 'id',
                    ]),
                )
                ->andReturn(FinderResult::from(Seq::from($this->examples->callings[1]), Pagination::create()));
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewStaffs(), $this->examples->callings[1]->staffId)
                ->andReturn(Seq::emptySeq());
            $this->eventDispatcher
                ->expects('handle')
                ->times(0); // 呼ばれないことを検証

            $this->interactor->handle($this->context, $range);
        });
        $this->should('use UrlBuilder', function (): void {
            $range = CarbonRange::create();
            $this->findCallingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listShifts(),
                    equalTo([
                        'expiredRange' => $range,
                        'response' => false,
                    ]),
                    equalTo([
                        'all' => true,
                        'sortBy' => 'id',
                    ]),
                )
                ->andReturn(FinderResult::from(Seq::from($this->examples->callings[0]), Pagination::create()));
            $this->urlBuilder
                ->expects('build')
                ->with($this->context, "/callings/{$this->examples->callings[0]->token}")
                ->andReturn(self::URL);
            $this->eventDispatcher
                ->expects('dispatch')
                ->andReturnUsing(function (FirstCallingEvent $event): void {
                    $this->assertSame(self::URL, $event->url());
                });

            $this->interactor->handle($this->context, $range);
        });
        $this->should('log using info', function (): void {
            $storedId = 10;
            $this->callingLogRepository
                ->allows('store')
                ->andReturnUsing(function (CallingLog $x) use ($storedId): CallingLog {
                    return $x->copy(['id' => $storedId]);
                });
            $context = [];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('出勤確認送信履歴が登録されました', ['id' => $storedId] + $context)
                ->andReturnNull();
            $range = CarbonRange::create();

            $this->interactor->handle($this->context, $range);
        });
    }
}
