<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Calling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Calling\CallingLog;
use Domain\Calling\CallingType;
use Domain\Calling\SecondCallingEvent;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CallingLogRepositoryMixin;
use Tests\Unit\Mixins\CallingResponseRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\FindCallingUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SendSecondCallingUseCaseMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UrlBuilderMixin;
use Tests\Unit\Mixins\UrlShortenerGatewayMixin;
use Tests\Unit\Test;
use UseCase\Calling\SendSecondCallingInteractor;

/**
 * {@link \Usecase\Calling\SendSecondCallingInteractor} Test.
 */
class SendSecondCallingInteractorTest extends Test
{
    use CallingLogRepositoryMixin;
    use CallingResponseRepositoryMixin;
    use CarbonMixin;
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use FindCallingUseCaseMixin;
    use LoggerMixin;
    use LookupShiftUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use SendSecondCallingUseCaseMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UrlBuilderMixin;
    use UrlShortenerGatewayMixin;

    private const URL = 'https://hoge.test/callings/token';
    private const SHORT_URL = 'http://short_url.test/hogehoge';
    private SendSecondCallingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SendSecondCallingInteractorTest $self): void {
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
            $self->urlShortenerGateway
                ->allows('handle')
                ->andReturn(self::SHORT_URL)
                ->byDefault();
            $self->findCallingUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->callings[0]), Pagination::create()))
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
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->callingResponseRepository
                ->allows('lookupByCallingId')
                ->andReturn(Seq::fromArray([
                    $self->examples->callingResponses[0],
                    $self->examples->callingResponses[1],
                    $self->examples->callingResponses[2],
                ]))
                ->byDefault();
            $self->urlBuilder
                ->allows('build')
                ->andReturn(self::URL)
                ->byDefault();
            $self->urlShortenerGateway
                ->allows('handle')
                ->andReturn(self::SHORT_URL)
                ->byDefault();

            $self->interactor = app(SendSecondCallingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('process succeed', function (): void {
            $this->interactor->handle($this->context, CarbonRange::create());
        });
        $this->should('use FindCallingUseCase', function (): void {
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
            $this->urlShortenerGateway
                ->expects('handle')
                ->with(self::URL)
                ->andReturn(self::SHORT_URL);

            $this->interactor->handle($this->context, $range);
        });
        $this->should('dispatch SecondCallingEvent', function (): void {
            $this->eventDispatcher
                ->expects('dispatch')
                ->andReturnUsing(function (SecondCallingEvent $event): void {
                    $shift = $event->shift();
                    $this->assertCount(1, Seq::from($shift));
                    $this->assertModelStrictEquals($this->examples->shifts[0], $shift);
                    $this->assertModelStrictEquals($this->examples->staffs[0], $event->staff());
                });

            $this->interactor->handle($this->context, CarbonRange::create());
        });
        $this->should('use UrlShortnerGateway', function (): void {
            $this->urlShortenerGateway
                ->expects('handle')
                ->with(self::URL)
                ->andReturn(self::SHORT_URL);

            $this->interactor->handle($this->context, CarbonRange::create());
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
        $this->should('store CallingLog via repository', function (): void {
            $this->callingLogRepository
                ->expects('store')
                ->andReturnUsing(function (CallingLog $x): CallingLog {
                    $this->assertNull($x->id);
                    $this->assertSame($this->examples->callings[0]->id, $x->callingId);
                    $this->assertEquals(CallingType::sms(), $x->callingType);
                    $this->assertTrue($x->isSucceeded);
                    $this->assertEquals(Carbon::now(), $x->createdAt);

                    return $x;
                })
                ->byDefault();
            $range = CarbonRange::create();

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
