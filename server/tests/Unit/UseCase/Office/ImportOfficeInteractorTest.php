<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\Office;
use Domain\Office\OfficeGroup;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Mockery;
use ScalikePHP\Option;
use Spatie\Snapshots\MatchesSnapshots;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\HomeHelpServiceCalcSpecRepositoryMixin;
use Tests\Unit\Mixins\HomeVisitLongTermCareCalcSpecRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeGroupRepositoryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\ReadonlyFileStorageMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\VisitingCareForPwsdCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Office\ImportOfficeInteractor;

/**
 * {@link \UseCase\Office\ImportOfficeInteractor} のテスト.
 */
final class ImportOfficeInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use HomeHelpServiceCalcSpecRepositoryMixin;
    use HomeVisitLongTermCareCalcSpecRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use OfficeGroupRepositoryMixin;
    use OfficeRepositoryMixin;
    use ReadonlyFileStorageMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use VisitingCareForPwsdCalcSpecRepositoryMixin;

    private const CSV_FILE_PATH = 'Office/office.csv';

    private SplFileInfo $file;

    private ImportOfficeInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->file = new SplFileInfo(codecept_data_dir(self::CSV_FILE_PATH));
        });
        self::beforeEachSpec(function (self $self): void {
            $self->readonlyFileStorage
                ->allows('fetch')
                ->andReturn(Option::some($self->file))
                ->byDefault();

            $self->officeGroupRepository
                ->allows('store')
                ->andReturnUsing(fn (OfficeGroup $x) => $x)
                ->byDefault();

            $self->officeRepository
                ->allows('store')
                ->andReturnUsing(fn (Office $x) => $x)
                ->byDefault();

            $self->homeVisitLongTermCareCalcSpecRepository
                ->allows('store')
                ->andReturnUsing(fn (HomeVisitLongTermCareCalcSpec $x) => $x)
                ->byDefault();

            $self->homeHelpServiceCalcSpecRepository
                ->allows('store')
                ->andReturnUsing(fn (HomeHelpServiceCalcSpec $x) => $x)
                ->byDefault();

            $self->visitingCareForPwsdCalcSpecRepository
                ->allows('store')
                ->andReturnUsing(fn (VisitingCareForPwsdCalcSpec $x) => $x)
                ->byDefault();

            $self->interactor = app(ImportOfficeInteractor::class);
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

            $this->interactor->handle($this->context, $filepath);
        });
        $this->should('store the office group', function (): void {
            $this->officeGroupRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturn($this->examples->officeGroups[0]);

            $this->interactor->handle($this->context, 'path/to/office.csv');

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('store offices', function (): void {
            $actual = [];
            $this->officeRepository
                ->expects('store')
                ->withArgs(function (Office $x) use (&$actual): bool {
                    $actual[] = $x;
                    return true;
                })
                ->andReturn($this->examples->offices[0])
                ->times(30);

            $this->interactor->handle($this->context, 'path/to/office.csv');

            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
