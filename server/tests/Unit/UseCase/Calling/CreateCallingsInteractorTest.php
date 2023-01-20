<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Calling;

use Closure;
use Domain\Calling\Calling;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\FinderResult;
use Illuminate\Support\LazyCollection;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CallingRepositoryMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ShiftFinderMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Calling\CreateCallingsInteractor;

/**
 * {@link \UseCase\Calling\CreateCallingsInteractorTest} テスト.
 */
final class CreateCallingsInteractorTest extends Test
{
    use CallingRepositoryMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use ShiftFinderMixin;
    use TokenMakerMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private CreateCallingsInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->config
                ->allows('get')
                ->with('zinger.calling.lifetime_minutes')
                ->andReturn(120)
                ->byDefault();

            $self->callingRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::none())
                ->byDefault();

            $self->callingRepository
                ->allows('store')
                ->andReturn($self->examples->callings[0])
                ->byDefault();

            $self->shiftFinder
                ->allows('cursor')
                ->andReturn(new LazyCollection([$self->examples->shifts[0]]))
                ->byDefault();

            $self->tokenMaker
                ->allows('make')
                ->andReturn(self::TOKEN)
                ->byDefault();

            $self->interactor = app(CreateCallingsInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('confirm the result after transaction begun', function (): void {
            $range = CarbonRange::create([
                'start' => Carbon::create('2040-01-01 09:55'),
                'end' => Carbon::create('2040-01-01 10:00'),
            ]);
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->shifts),
            ]);
            $expected = FinderResult::create([
                'list' => Seq::from($this->examples->shifts[1]),
                'pagination' => $pagination,
            ]);
            $this->shiftFinder
                ->expects('find')
                ->andReturn($expected);
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->callingRepository
                        ->expects('store')
                        ->andReturn($this->examples->callings[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $range);
        });
        $this->should(
            'set expiredAt using config',
            function (int $lifetime): void {
                $range = CarbonRange::create([
                    'start' => Carbon::create('2040-01-01 09:55'),
                    'end' => Carbon::create('2040-01-01 10:00'),
                ]);
                $pagination = Pagination::create([
                    'sortBy' => 'date',
                    'count' => count($this->examples->shifts),
                ]);
                $expected = FinderResult::create([
                    'list' => Seq::from($this->examples->shifts[1]),
                    'pagination' => $pagination,
                ]);
                $this->shiftFinder
                    ->expects('find')
                    ->andReturn($expected);
                $this->config
                    ->expects('get')
                    ->with('zinger.calling.lifetime_minutes')
                    ->andReturn($lifetime);
                $this->callingRepository
                    ->expects('store')
                    ->with(Mockery::capture($actual))
                    ->andReturn($this->examples->callings[0]);

                $this->interactor->handle($this->context, $range);

                $this->assertSame(
                    Carbon::now()->startOfMinute()->addMinutes($lifetime)->timestamp,
                    $actual->expiredAt->timestamp
                );
            },
            ['examples' => [[120], [30], [1440], [90]]]
        );
        $this->should('use find() of ShiftFinder with expected params', function (): void {
            $range = CarbonRange::create([
                'start' => Carbon::create('2040-01-01 09:55'),
                'end' => Carbon::create('2040-01-01 10:00'),
            ]);
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->shifts),
            ]);
            $expected = FinderResult::create([
                'list' => Seq::from($this->examples->shifts[1]),
                'pagination' => $pagination,
            ]);
            $this->shiftFinder
                ->expects('find')
                ->withArgs(function (array $filter, array $pagination) use ($range) {
                    $this->assertTrue($filter['isConfirmed']);
                    $this->assertTrue($filter['notificationEnabled']);
                    $this->assertEquals($this->examples->organizations[0]->id, $filter['organizationId']);
                    $this->assertSame($range, $filter['scheduleStart']);

                    $this->assertEquals('id', $pagination['sortBy']);
                    $this->assertTrue($pagination['all']);

                    return true;
                })
                ->andReturn($expected);

            $this->interactor->handle($this->context, $range);
        });
        $this->should('store Calling by created domain', function (): void {
            $range = CarbonRange::create([
                'start' => Carbon::create('2040-01-01 09:55'),
                'end' => Carbon::create('2040-01-01 10:00'),
            ]);

            $expectedShift = $this->examples->shifts[1];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->shifts),
            ]);
            $expected = FinderResult::create([
                'list' => Seq::from($expectedShift),
                'pagination' => $pagination,
            ]);
            $this->shiftFinder
                ->expects('find')
                ->andReturn($expected);

            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($expectedShift) {
                    $this->callingRepository
                        ->expects('store')
                        ->withArgs(function (Calling $calling) use ($expectedShift): bool {
                            // assigneesは1人しかいない
                            $this->assertEquals($expectedShift->assignees[0]->staffId, $calling->staffId);
                            $this->assertEquals(
                                [$expectedShift->id, $this->examples->shifts[0]->id],
                                $calling->shiftIds
                            );
                            $this->assertNotNull($calling->token);
                            $this->assertEquals(Carbon::now()->startOfMinute(), $calling->createdAt);
                            $this->assertEquals(Carbon::now()->startOfMinute()->addMinutes(120), $calling->expiredAt);

                            return true;
                        });
                    return $callback();
                });

            $this->interactor->handle($this->context, $range);
        });
        $this->should('store Calling by 2 ShiftIds that each shifts interval into 8 hours', function (): void {
            $range = CarbonRange::create([
                'start' => Carbon::create('2040-01-01 09:55'),
                'end' => Carbon::create('2040-01-01 10:00'),
            ]);

            $firstShift = $this->examples->shifts[1]; // 1つ目のShift（検索で見つかるやつ）
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->shifts),
            ]);
            $expected = FinderResult::create([
                'list' => Seq::from($firstShift),
                'pagination' => $pagination,
            ]);
            $this->shiftFinder
                ->expects('find')
                ->andReturn($expected);

            $secondShift = $this->examples->shifts[0]->copy([
                'schedule' => Schedule::create([
                    'date' => $firstShift->schedule->date,
                    'start' => $firstShift->schedule->end->addMinutes(10), // 8時間以内のスケジュール
                    'end' => $firstShift->schedule->end->addMinutes(20), // 10分間というSchedule
                ]),
            ]);
            $this->shiftFinder
                ->expects('cursor')
                ->andReturn(new LazyCollection([$secondShift]));

            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($firstShift, $secondShift) {
                    $this->callingRepository
                        ->expects('store')
                        ->withArgs(function (Calling $calling) use ($firstShift, $secondShift): bool {
                            // IDが2つ渡ることをAssert
                            $this->assertEquals([$firstShift->id, $secondShift->id], $calling->shiftIds);
                            return true;
                        });
                    return $callback();
                });

            $this->interactor->handle($this->context, $range);
        });
        $this->should('store Calling by 1 ShiftId that each shifts interval over 8 hours', function (): void {
            $range = CarbonRange::create([
                'start' => Carbon::create('2040-01-01 09:55'),
                'end' => Carbon::create('2040-01-01 10:00'),
            ]);

            $firstShift = $this->examples->shifts[1]; // 1つ目のShift（検索で見つかるやつ）
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->shifts),
            ]);
            $expected = FinderResult::create([
                'list' => Seq::from($firstShift),
                'pagination' => $pagination,
            ]);
            $this->shiftFinder
                ->expects('find')
                ->andReturn($expected);

            $secondShift = $this->examples->shifts[0]->copy([
                'schedule' => Schedule::create([
                    'date' => $firstShift->schedule->date,
                    'start' => $firstShift->schedule->end->addHours(10), // 8時間超え
                    'end' => $firstShift->schedule->end->addHours(20),
                ]),
            ]);
            $this->shiftFinder
                ->expects('cursor')
                ->andReturn(new LazyCollection([$secondShift]));

            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($firstShift) {
                    $this->callingRepository
                        ->expects('store')
                        ->withArgs(function (Calling $calling) use ($firstShift): bool {
                            $this->assertEquals([$firstShift->id], $calling->shiftIds); // IDが1つ渡ることをAssert

                            return true;
                        });
                    return $callback();
                });

            $this->interactor->handle($this->context, $range);
        });
        $this->should('use `cursor()` in ShiftFinder', function (): void {
            $range = CarbonRange::create([
                'start' => Carbon::create('2040-01-01 09:55'),
                'end' => Carbon::create('2040-01-01 10:00'),
            ]);

            $firstShift = $this->examples->shifts[1]; // 1つ目のShift（検索で見つかるやつ）
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->shifts),
            ]);
            $expected = FinderResult::create([
                'list' => Seq::from($firstShift),
                'pagination' => $pagination,
            ]);
            $this->shiftFinder
                ->expects('find')
                ->andReturn($expected);

            $this->shiftFinder
                ->expects('cursor')
                ->with(equalTo([
                    'isConfirmed' => true,
                    'assigneeId' => $firstShift->assignees[0]->staffId,
                    'scheduleDateBefore' => $firstShift->schedule->start->addSecond(),
                    'isCanceled' => false,
                ]), equalTo(['sortBy' => 'id']))
                ->andReturn(new LazyCollection());

            $this->interactor->handle($this->context, $range);
        });
    }
}
