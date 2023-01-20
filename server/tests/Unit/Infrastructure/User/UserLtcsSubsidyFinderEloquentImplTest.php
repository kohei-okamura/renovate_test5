<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\User;

use Closure;
use Domain\Common\Carbon;
use Domain\User\UserLtcsSubsidy;
use Infrastructure\User\UserLtcsSubsidyFinderEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\User\UserLtcsSubsidyFinderEloquentImpl} のテスト.
 */
final class UserLtcsSubsidyFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private UserLtcsSubsidyFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (UserLtcsSubsidyFinderEloquentImplTest $self): void {
            $self->finder = app(UserLtcsSubsidyFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $period = Carbon::parse('2020-05-17');
        $userId = $this->examples->users[1]->id;
        $examples = [
            'specified period' => [
                ['period' => $period],
                fn (UserLtcsSubsidy $x): bool => $x->period->contains($period),
            ],
            'specified userId' => [
                ['userId' => $userId],
                fn (UserLtcsSubsidy $x): bool => $x->userId === $userId,
            ],
        ];
        $this->should(
            'return a FinderResult of UserLtcsSubsidy with given parameters',
            function (array $condition, Closure $assert): void {
                $subsides = $this->examples->userLtcsSubsidies;

                $actual = $this->finder->find($condition, ['all' => true, 'sortBy' => 'id']);

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertForAll($actual->list, $assert);
                $this->assertExists($subsides, $this->invert($assert));
            },
            compact('examples')
        );
        $this->should(
            'return a FinderResult of UserLtcsSubsidy with unknown parameter',
            function (): void {
                $subsides = $this->examples->userLtcsSubsidies;

                $actual = $this->finder->find(
                    ['invalid' => 'value'],
                    ['all' => true, 'sortBy' => 'id'],
                );

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertArrayStrictEquals($subsides, $actual->list->toArray());
            }
        );
    }
}
