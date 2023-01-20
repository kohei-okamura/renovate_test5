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
use Domain\User\UserDwsSubsidy;
use ScalikePHP\None;
use ScalikePHP\Seq;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\UserDwsSubsidyFinderMixin;
use Tests\Unit\Test;
use UseCase\User\IdentifyUserDwsSubsidyInteractor;

/**
 * {@link \UseCase\User\IdentifyUserDwsSubsidyInteractor} のテスト.
 */
final class IdentifyUserDwsSubsidyInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use UserDwsSubsidyFinderMixin;

    private IdentifyUserDwsSubsidyInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (IdentifyUserDwsSubsidyInteractorTest $self): void {
            $self->userDwsSubsidyFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from(...$self->examples->userDwsSubsidies),
                    Pagination::create()
                ))
                ->byDefault();

            $self->interactor = app(IdentifyUserDwsSubsidyInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call finder with specified parameters', function (): void {
            $expected = Seq::from(...$this->examples->userDwsSubsidies);
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
            $this->userDwsSubsidyFinder
                ->expects('find')
                ->with(equalTo($filterParams), equalTo($paginationParams))
                ->andReturn(FinderResult::from($expected, Pagination::create()));

            $this->interactor->handle($this->context, $user, $targetDate);
        });
        $this->should('return Some of UserDwsSubsidy when identified', function (): void {
            $user = $this->examples->users[0];
            $targetDate = Carbon::now();

            $actual = $this->interactor->handle($this->context, $user, $targetDate);

            $this->assertInstanceOf(Some::class, $actual);
            $this->assertNotEmpty($actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof UserDwsSubsidy);
        });
        $this->should('return None when failed to identify', function (): void {
            $user = $this->examples->users[0];
            $targetDate = Carbon::now();
            $this->userDwsSubsidyFinder->expects('find')->andReturn(FinderResult::from([], Pagination::create([])));

            $actual = $this->interactor->handle($this->context, $user, $targetDate);

            $this->assertInstanceOf(None::class, $actual);
        });
    }
}
