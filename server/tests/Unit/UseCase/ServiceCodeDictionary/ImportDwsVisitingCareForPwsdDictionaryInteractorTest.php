<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ServiceCodeDictionary;

use Closure;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Lib\Csv;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DwsVisitingCareForPwsdDictionaryEntryRepositoryMixin;
use Tests\Unit\Mixins\DwsVisitingCareForPwsdDictionaryRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ReadonlyFileStorageMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\ImportDwsVisitingCareForPwsdDictionaryInteractor;

/**
 * ImportDwsVisitingCareForPwsdDictionaryInteractorのテスト.
 */
class ImportDwsVisitingCareForPwsdDictionaryInteractorTest extends Test
{
    use CarbonMixin;
    use DwsVisitingCareForPwsdDictionaryRepositoryMixin;
    use DwsVisitingCareForPwsdDictionaryEntryRepositoryMixin;
    use ExamplesConsumer;
    use ReadonlyFileStorageMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const FILEPATH = 'ServiceCodeDictionary/dws-visiting-care-for-pwsd-dictionary.csv';
    private DwsVisitingCareForPwsdDictionary $dwsVisitingCareForPwsdDictionary;
    private ImportDwsVisitingCareForPwsdDictionaryInteractor $interactor;
    private SplFileInfo $file;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ImportDwsVisitingCareForPwsdDictionaryInteractorTest $self): void {
            $self->file = new SplFileInfo(codecept_data_dir(self::FILEPATH));
            $self->dwsVisitingCareForPwsdDictionary = $self->examples->dwsVisitingCareForPwsdDictionaries[0];
            $self->dwsVisitingCareForPwsdDictionaryRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->dwsVisitingCareForPwsdDictionary))
                ->byDefault();
            $self->dwsVisitingCareForPwsdDictionaryRepository
                ->allows('store')
                ->andReturn($self->dwsVisitingCareForPwsdDictionary)
                ->byDefault();
            $self->dwsVisitingCareForPwsdDictionaryEntryRepository
                ->allows('store')
                ->andReturn($self->examples->dwsVisitingCareForPwsdDictionaryEntries)
                ->byDefault();
            $self->readonlyFileStorage
                ->allows('fetch')
                ->andReturn(Option::from($self->file))
                ->byDefault();
            $self->interactor = app(ImportDwsVisitingCareForPwsdDictionaryInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use FileStorage', function (): void {
            $this->readonlyFileStorage
                ->expects('fetch')
                ->with(self::FILEPATH)
                ->andReturn(Option::from($this->file));

            $this->interactor->handle(
                $this->dwsVisitingCareForPwsdDictionary->id,
                self::FILEPATH,
                Carbon::now()->format('Y/m/d'),
                'test_name'
            );
        });
        $this->should('throw NotFountException when file is not found', function (): void {
            $this->readonlyFileStorage
                ->expects('fetch')
                ->with(self::FILEPATH)
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->dwsVisitingCareForPwsdDictionary->id,
                        self::FILEPATH,
                        Carbon::now()->format('Y/m/d'),
                        'test_name'
                    );
                }
            );
        });
        $this->should('call lookup in DwsVisitingCareForPwsdDictionaryRepository', function (): void {
            $this->dwsVisitingCareForPwsdDictionaryRepository
                ->expects('lookup')
                ->with($this->dwsVisitingCareForPwsdDictionary->id)
                ->andReturn(Seq::from($this->dwsVisitingCareForPwsdDictionary));

            $this->interactor->handle(
                $this->dwsVisitingCareForPwsdDictionary->id,
                self::FILEPATH,
                Carbon::now()->format('Y/m/d'),
                'test_name'
            );
        });
        $this->should('call store in DwsVisitingCareForPwsdDictionaryRepository', function (): void {
            $effectivatedOn = Carbon::now()->format('Y/m/d');
            $store = DwsVisitingCareForPwsdDictionary::create([
                'id' => $this->dwsVisitingCareForPwsdDictionary->id,
                'effectivatedOn' => Carbon::parse($effectivatedOn),
                'name' => 'test_name',
                'version' => $this->dwsVisitingCareForPwsdDictionary->version + 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($store) {
                    $this->dwsVisitingCareForPwsdDictionaryRepository
                        ->expects('store')
                        ->with(equalTo($store))
                        ->andReturn($this->dwsVisitingCareForPwsdDictionary);
                    return $callback();
                });

            $this->interactor->handle(
                $this->dwsVisitingCareForPwsdDictionary->id,
                self::FILEPATH,
                $effectivatedOn,
                'test_name'
            );
        });
        $this->should('call store in DwsVisitingCareForPwsdDictionaryEntryRepository', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->dwsVisitingCareForPwsdDictionaryEntryRepository
                        ->expects('store')
                        ->with(anInstanceOf(DwsVisitingCareForPwsdDictionaryEntry::class))
                        ->times(Csv::read($this->file->getPathname())->count());
                    return $callback();
                });

            $this->interactor->handle(
                $this->dwsVisitingCareForPwsdDictionary->id,
                self::FILEPATH,
                Carbon::now()->format('Y/m/d'),
                'test_name'
            );
        });
    }
}
