<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryRepositoryMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ReadonlyFileStorageMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryInteractor;

/**
 * {@link \UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryInteractor} のテスト.
 */
final class ImportLtcsHomeVisitLongTermCareDictionaryInteractorTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use LtcsHomeVisitLongTermCareDictionaryEntryRepositoryMixin;
    use LtcsHomeVisitLongTermCareDictionaryRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use ReadonlyFileStorageMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const CSV_FILE_PATH = 'ServiceCodeDictionary/ltcs-home-visit-long-term-care-dictionary-csv-test.csv';

    private LtcsHomeVisitLongTermCareDictionary $dictionary;
    /** @var array|\Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[] */
    private array $entries;
    private SplFileInfo $file;

    private ImportLtcsHomeVisitLongTermCareDictionaryInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (ImportLtcsHomeVisitLongTermCareDictionaryInteractorTest $self): void {
            $self->dictionary = $self->examples->ltcsHomeVisitLongTermCareDictionaries[0];
            $self->entries = Seq::fromArray($self->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->map(function (LtcsHomeVisitLongTermCareDictionaryEntry $x) use ($self) {
                    return $x->copy(['dictionaryId' => $self->dictionary->id]);
                })
                ->toArray();
            $self->file = new SplFileInfo(codecept_data_dir(self::CSV_FILE_PATH));
        });
        self::beforeEachSpec(function (ImportLtcsHomeVisitLongTermCareDictionaryInteractorTest $self): void {
            $self->readonlyFileStorage
                ->allows('fetch')
                ->andReturn(Option::some($self->file))
                ->byDefault();

            $self->ltcsHomeVisitLongTermCareDictionaryRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->dictionary))
                ->byDefault();

            $self->ltcsHomeVisitLongTermCareDictionaryRepository
                ->allows('remove')
                ->byDefault();

            $self->ltcsHomeVisitLongTermCareDictionaryRepository
                ->allows('store')
                ->andReturnUsing(fn (LtcsHomeVisitLongTermCareDictionary $x) => $x)
                ->byDefault();

            $self->ltcsHomeVisitLongTermCareDictionaryEntryRepository
                ->allows('store')
                ->andReturnUsing(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x) => $x)
                ->byDefault();

            $self->interactor = app(ImportLtcsHomeVisitLongTermCareDictionaryInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('fetch the csv file from ReadonlyFileStorage', function (): void {
            $filepath = 'some/awesome.csv';
            $this->readonlyFileStorage->expects('fetch')->with($filepath)->andReturn(Option::some($this->file));

            $this->interactor->handle(
                $filepath,
                $this->dictionary->id,
                Carbon::create(2021, 4, 1),
                '令和3年4月改訂版'
            );
        });
        $this->should('update the dictionary when it exists', function (): void {
            /** @var \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary $actual */
            $actual = null;
            $this->ltcsHomeVisitLongTermCareDictionaryRepository
                ->expects('lookup')
                ->with($this->dictionary->id)
                ->andReturn(Seq::from($this->dictionary));
            $this->ltcsHomeVisitLongTermCareDictionaryRepository
                ->expects('remove')
                ->with($this->dictionary);
            $this->ltcsHomeVisitLongTermCareDictionaryRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturn($this->dictionary);

            $this->interactor->handle(
                'some/file.csv',
                $this->dictionary->id,
                Carbon::create(2021, 4, 1),
                '令和3年4月改訂版'
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('create the dictionary when it is not exists', function (): void {
            /** @var \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary $actual */
            $actual = null;
            $id = 1999;
            $this->ltcsHomeVisitLongTermCareDictionaryRepository
                ->expects('lookup')
                ->andReturn(Seq::empty());
            $this->ltcsHomeVisitLongTermCareDictionaryRepository
                ->expects('remove')
                ->never();
            $this->ltcsHomeVisitLongTermCareDictionaryRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturn($this->dictionary);

            $this->interactor->handle(
                'some/file.csv',
                $id,
                Carbon::create(2021, 4, 1),
                '令和3年4月改訂版'
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('store entries', function (): void {
            $actual = [];
            $this->ltcsHomeVisitLongTermCareDictionaryEntryRepository
                ->expects('store')
                ->withArgs(function (LtcsHomeVisitLongTermCareDictionaryEntry $x) use (&$actual): bool {
                    $actual[] = $x;
                    return true;
                })
                ->andReturn($this->entries[0])
                ->times(3);

            $this->interactor->handle(
                'some/file.csv',
                $this->dictionary->id,
                Carbon::create(2021, 4, 1),
                '令和3年4月改訂版'
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return the number of stored entries', function (): void {
            $actual = $this->interactor->handle(
                'some/file.csv',
                $this->dictionary->id,
                Carbon::create(2021, 4, 1),
                '令和3年4月改訂版'
            );
            $this->assertSame(3, $actual);
        });
    }
}
