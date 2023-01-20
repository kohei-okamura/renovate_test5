<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryInteractor;

/**
 * {@link \UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryInteractor} のテスト.
 */
final class GetLtcsHomeVisitLongTermCareDictionaryEntryInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
    use FindLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private GetLtcsHomeVisitLongTermCareDictionaryEntryInteractor $interactor;
    private FinderResult $entryResult;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->entryResult = FinderResult::from(
                $self->examples->ltcsHomeVisitLongTermCareDictionaryEntries,
                Pagination::create()
            );
            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::fromArray($self->examples->ltcsHomeVisitLongTermCareDictionaryEntries),
                    Pagination::create()
                ))
                ->byDefault();
            $self->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->allows('handle')
                ->andReturn($self->entryResult)
                ->byDefault();
            $self->interactor = app(GetLtcsHomeVisitLongTermCareDictionaryEntryInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use findDictionaryEntryUseCase', function (): void {
            $this->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    ['q' => '111111', 'providedIn' => Carbon::parse('2021-11')],
                    ['all' => true]
                )
                ->andReturn($this->entryResult);

            $this->interactor
                ->handle(
                    $this->context,
                    '111111',
                    Carbon::parse('2021-11')
                );
        });
        $this->should('return none when findDictionaryEntryUseCase return list of empty', function (): void {
            $this->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    ['q' => '111111', 'providedIn' => Carbon::parse('2021-11')],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from(
                    [],
                    Pagination::create()
                ));

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    '111111',
                    Carbon::parse('2021-11')
                );
            $this->assertNone($actual);
        });
        $this->should('return some of LtcsHomeVisitLongTermCareDictionaryEntry', function (): void {
            $this->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->expects('handle')
                ->andReturn($this->entryResult);

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    '111111',
                    Carbon::parse('2021-11')
                );
            $this->assertSome($actual, function ($actualValue) {
                $this->assertModelStrictEquals(
                    $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0],
                    $actualValue
                );
            });
        });
    }
}
