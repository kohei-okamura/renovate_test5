<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\User\UserLtcsSubsidy;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserLtcsSubsidyFinderMixin;
use Tests\Unit\Test;
use UseCase\User\IdentifyUserLtcsSubsidyInteractor;

/**
 * {@link \UseCase\User\IdentifyUserLtcsSubsidyInteractor} のテスト.
 */
final class IdentifyUserLtcsSubsidyInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use UserLtcsSubsidyFinderMixin;

    private IdentifyUserLtcsSubsidyInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (IdentifyUserLtcsSubsidyInteractorTest $self): void {
            $self->userLtcsSubsidyFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($self->examples->userLtcsSubsidies[0]),
                    Pagination::create()
                ))
                ->byDefault();

            $self->interactor = app(IdentifyUserLtcsSubsidyInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Seq of Option of UserLtcsSubsidy', function (): void {
            $user = $this->examples->users[0];
            $targetDate = Carbon::now();

            $actual = $this->interactor->handle($this->context, $user, $targetDate);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(expected: 3, actual: $actual->size());
            $this->assertForAll($actual, fn ($x): bool => $x instanceof Option);
            $this->assertSome($actual[0], function (UserLtcsSubsidy $actual): void {
                $this->assertSame(
                    expected: $this->examples->userLtcsSubsidies[0],
                    actual: $actual
                );
            });
            $this->assertNone($actual[1]);
            $this->assertNone($actual[2]);
        });
        $this->should('call finder with specified parameters', function (): void {
            $expected = Seq::from(...$this->examples->userLtcsSubsidies);
            $user = $this->examples->users[0];
            $targetDate = Carbon::now();
            $filterParams = [
                'period' => $targetDate,
                'userId' => $user->id,
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->userLtcsSubsidyFinder
                ->expects('find')
                ->with(equalTo($filterParams), equalTo($paginationParams))
                ->andReturn(FinderResult::from($expected, Pagination::create()));

            $this->interactor->handle($this->context, $user, $targetDate);
        });
    }
}
