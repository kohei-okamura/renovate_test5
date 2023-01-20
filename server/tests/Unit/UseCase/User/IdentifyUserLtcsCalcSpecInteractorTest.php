<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\User\UserLtcsCalcSpec;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserLtcsCalcSpecFinderMixin;
use Tests\Unit\Test;
use UseCase\User\IdentifyUserLtcsCalcSpecInteractor;

/**
 * {@link \UseCase\User\IdentifyUserLtcsCalcSpecInteractor} のテスト.
 */
final class IdentifyUserLtcsCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;
    use UserLtcsCalcSpecFinderMixin;

    private IdentifyUserLtcsCalcSpecInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (IdentifyUserLtcsCalcSpecInteractorTest $self): void {
            $self->userLtcsCalcSpecFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from(...$self->examples->userLtcsCalcSpecs),
                    Pagination::create()
                ))
                ->byDefault();

            $self->interactor = app(IdentifyUserLtcsCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return some of UserLtcsCalcSpec', function (): void {
            $user = $this->examples->users[0];
            $targetDate = Carbon::now();

            $actual = $this->interactor->handle($this->context, $user, $targetDate);

            $this->assertSome($actual, function (UserLtcsCalcSpec $LtcsCalcSpec): void {
                $this->assertModelStrictEquals($this->examples->userLtcsCalcSpecs[0], $LtcsCalcSpec);
            });
        });
        $this->should('call finder with specified parameters', function (): void {
            $user = $this->examples->users[0];
            $targetDate = Carbon::now();
            $filterParams = [
                'userId' => $user->id,
                'effectivatedOnBefore' => $targetDate,
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'effectivatedOn',
                'desc' => true,
            ];
            $this->userLtcsCalcSpecFinder
                ->expects('find')
                ->with(equalTo($filterParams), equalTo($paginationParams))
                ->andReturn(FinderResult::from(
                    Seq::from(...$this->examples->userLtcsCalcSpecs),
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, $user, $targetDate);
        });
        $this->should('return None when failed to identify', function (): void {
            $user = $this->examples->users[0];
            $targetDate = Carbon::now();
            $this->userLtcsCalcSpecFinder
                ->expects('find')
                ->andReturn(FinderResult::from([], Pagination::create([])));

            $actual = $this->interactor->handle($this->context, $user, $targetDate);

            $this->assertNone($actual);
        });
    }
}
