<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use App\Concretes\PermanentDatabaseTransactionManager;
use Lib\Exceptions\PhpSpreadsheetException;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use ScalikePHP\Seq;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DownloadStorageUseCaseMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\LoadShiftUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ShiftRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\ImportShiftInteractor;

/**
 * ImportShiftInteractor のテスト.
 */
class ImportShiftInteractorTest extends Test
{
    use ContextMixin;
    use DownloadStorageUseCaseMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use LoadShiftUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use ShiftRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const SHEET_INDEX_SHIFT = 0;
    private const SHEET_INDEX_NOT_EXISTING = 999;
    private const VALID_SHIFT_FILE = 'Shift/valid-shifts.xlsx';

    private ImportShiftInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ImportShiftInteractorTest $self): void {
            $self->downloadStorageUseCase
                ->allows('handle')
                ->andReturn(new SplFileInfo(codecept_data_dir(self::VALID_SHIFT_FILE)))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->shiftRepository
                ->allows('store')
                ->andReturn($self->examples->shifts[0])
                ->byDefault();
            $self->shiftRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();

            $self->interactor = app(ImportShiftInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'store shifts',
            function () {
                $this->loadShiftUseCase
                    ->expects('handle')
                    ->andReturn(Seq::fromArray($this->examples->shifts));
                $this->interactor->handle(
                    $this->context,
                    codecept_data_dir(self::VALID_SHIFT_FILE)
                );
            }
        );
        $this->should(
            'throw PhpSpreadsheetException when PhpOffice\PhpSpreadsheet\Exception is thrown',
            function () {
                $this->assertThrows(PhpSpreadsheetException::class, function () {
                    $file = codecept_data_dir(self::VALID_SHIFT_FILE);
                    $this->loadShiftUseCase
                        ->expects('handle')
                        ->andThrow(Exception::class);
                    $this->interactor->handle(
                        $this->context,
                        $file
                    );
                });
            }
        );
        $this->should(
            'throw PhpSpreadsheetException when accessed a non-existent sheet index in the spreadsheet',
            function () {
                $this->assertThrows(PhpSpreadsheetException::class, function () {
                    $file = codecept_data_dir(self::VALID_SHIFT_FILE);
                    $this->loadShiftUseCase
                        ->expects('handle')
                        ->andReturnUsing(function () use ($file) {
                            $spreadsheet = (new XlsxReader())->load($file);
                            $spreadsheet->getSheet(self::SHEET_INDEX_NOT_EXISTING);
                        });
                    $this->interactor->handle(
                        $this->context,
                        $file
                    );
                });
            }
        );
        $this->should(
            'throw PhpSpreadsheetException when a string that is not a cell number is specified',
            function () {
                $this->assertThrows(PhpSpreadsheetException::class, function () {
                    $file = codecept_data_dir(self::VALID_SHIFT_FILE);
                    $this->loadShiftUseCase
                        ->expects('handle')
                        ->andReturnUsing(function () use ($file) {
                            $spreadsheet = (new XlsxReader())->load($file);
                            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);
                            $worksheet->getCell('error');
                        });
                    $this->interactor->handle(
                        $this->context,
                        $file
                    );
                });
            }
        );
        $this->should(
            'throw PhpSpreadsheetException when a string containing $ is specified',
            function () {
                $this->assertThrows(PhpSpreadsheetException::class, function () {
                    $file = codecept_data_dir(self::VALID_SHIFT_FILE);
                    $this->loadShiftUseCase
                        ->expects('handle')
                        ->andReturnUsing(function () use ($file) {
                            $spreadsheet = (new XlsxReader())->load($file);
                            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);
                            $worksheet->getCell('$B3');
                        });
                    $this->interactor->handle(
                        $this->context,
                        $file
                    );
                });
            }
        );
    }
}
