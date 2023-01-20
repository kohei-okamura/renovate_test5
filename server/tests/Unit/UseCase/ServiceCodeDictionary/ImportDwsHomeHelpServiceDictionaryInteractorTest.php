<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ServiceCodeDictionary;

use Closure;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;
use Lib\Csv;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceDictionaryEntryRepositoryMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceDictionaryRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ReadonlyFileStorageMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\ServiceCodeDictionary\ImportDwsHomeHelpServiceDictionaryInteractor;

/**
 * ImportDwsHomeHelpServiceDictionaryInteractorのテスト.
 */
class ImportDwsHomeHelpServiceDictionaryInteractorTest extends Test
{
    use CarbonMixin;
    use DwsHomeHelpServiceDictionaryRepositoryMixin;
    use DwsHomeHelpServiceDictionaryEntryRepositoryMixin;
    use ExamplesConsumer;
    use ReadonlyFileStorageMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const FILEPATH = 'ServiceCodeDictionary/dws-home-help-dictionary.csv';
    private DwsHomeHelpServiceDictionary $dwsHomeHelpServiceDictionary;
    private ImportDwsHomeHelpServiceDictionaryInteractor $interactor;
    private SplFileInfo $file;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ImportDwsHomeHelpServiceDictionaryInteractorTest $self): void {
            $self->file = new SplFileInfo(codecept_data_dir(self::FILEPATH));
            $self->dwsHomeHelpServiceDictionary = $self->examples->dwsHomeHelpServiceDictionaries[0];
            $self->dwsHomeHelpServiceDictionaryRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->dwsHomeHelpServiceDictionary))
                ->byDefault();
            $self->dwsHomeHelpServiceDictionaryRepository
                ->allows('store')
                ->andReturn($self->dwsHomeHelpServiceDictionary)
                ->byDefault();
            $self->dwsHomeHelpServiceDictionaryEntryRepository
                ->allows('store')
                ->andReturn($self->examples->dwsHomeHelpServiceDictionaryEntries)
                ->byDefault();
            $self->readonlyFileStorage
                ->allows('fetch')
                ->andReturn(Option::from($self->file))
                ->byDefault();
            $self->interactor = app(ImportDwsHomeHelpServiceDictionaryInteractor::class);
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
                $this->dwsHomeHelpServiceDictionary->id,
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
                        $this->dwsHomeHelpServiceDictionary->id,
                        self::FILEPATH,
                        Carbon::now()->format('Y/m/d'),
                        'test_name'
                    );
                }
            );
        });
        $this->should('call lookup in DwsHomeHelpServiceDictionaryRepository', function (): void {
            $this->dwsHomeHelpServiceDictionaryRepository
                ->expects('lookup')
                ->with($this->dwsHomeHelpServiceDictionary->id)
                ->andReturn(Seq::from($this->dwsHomeHelpServiceDictionary));

            $this->interactor->handle(
                $this->dwsHomeHelpServiceDictionary->id,
                self::FILEPATH,
                Carbon::now()->format('Y/m/d'),
                'test_name'
            );
        });
        $this->should('call store in DwsHomeHelpServiceDictionaryRepository', function (): void {
            $effectivatedOn = Carbon::now()->format('Y/m/d');
            $store = DwsHomeHelpServiceDictionary::create([
                'id' => $this->dwsHomeHelpServiceDictionary->id,
                'effectivatedOn' => Carbon::parse($effectivatedOn),
                'name' => 'test_name',
                'version' => $this->dwsHomeHelpServiceDictionary->version + 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($store) {
                    $this->dwsHomeHelpServiceDictionaryRepository
                        ->expects('store')
                        ->with(equalTo($store))
                        ->andReturn($this->dwsHomeHelpServiceDictionary);
                    return $callback();
                });

            $this->interactor->handle(
                $this->dwsHomeHelpServiceDictionary->id,
                self::FILEPATH,
                $effectivatedOn,
                'test_name'
            );
        });
        $this->should('call store in DwsHomeHelpServiceDictionaryEntryRepository', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->dwsHomeHelpServiceDictionaryEntryRepository
                        ->expects('store')
                        ->with(anInstanceOf(DwsHomeHelpServiceDictionaryEntry::class))
                        ->times(Csv::read($this->file->getPathname())->count());
                    return $callback();
                });

            $this->interactor->handle(
                $this->dwsHomeHelpServiceDictionary->id,
                self::FILEPATH,
                Carbon::now()->format('Y/m/d'),
                'test_name'
            );
        });
    }
}
