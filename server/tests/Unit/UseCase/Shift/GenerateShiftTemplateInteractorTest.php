<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use Lib\Exceptions\TemporaryFileAccessException;
use Lib\RandomString;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\FindOfficeUseCaseMixin;
use Tests\Unit\Mixins\FindShiftUseCaseMixin;
use Tests\Unit\Mixins\FindStaffUseCaseMixin;
use Tests\Unit\Mixins\FindUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TemporaryFilesMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\GenerateShiftTemplateInteractor;

/**
 * {@link \UseCase\Shift\GenerateShiftTemplateInteractor} Test.
 */
class GenerateShiftTemplateInteractorTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use FindStaffUseCaseMixin;
    use FindOfficeUseCaseMixin;
    use FindShiftUseCaseMixin;
    use FindUserUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use TemporaryFilesMixin;

    public const PAGINATION_PARAMS = [
        'all' => true,
    ];

    private GenerateShiftTemplateInteractor $interactor;
    private Pagination $pagination;
    private $path;
    private CarbonRange $range;
    private array $parameters;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (GenerateShiftTemplateInteractorTest $self): void {
            $dir = sys_get_temp_dir();
            $prefix = 'zinger-';
            $suffix = '.xlsx';
            $self->path = RandomString::seq(16)
                ->map(fn (string $name): string => $dir . '/' . $prefix . $name . $suffix)
                ->find(fn (string $path): bool => !file_exists($path))
                ->getOrElse(function (): void {
                    throw new RuntimeException('Failed to create temporary file');
                });
            touch($self->path);
            chmod($self->path, 0600);
        });
        static::beforeEachSpec(function (GenerateShiftTemplateInteractorTest $self): void {
            $self->interactor = app(GenerateShiftTemplateInteractor::class);
            $self->pagination = Pagination::create(self::PAGINATION_PARAMS);
            $self->parameters = [
                'officeId' => $self->examples->offices[0]->id,
                'start' => Carbon::parse('2040-01-01'),
                'end' => Carbon::parse('2040-05-01'),
            ];
            $self->range = CarbonRange::create([
                'start' => Carbon::parse('2041-01-01'),
                'end' => Carbon::parse('2041-05-01'),
            ]);

            $self->fileStorage
                ->allows('store')
                ->andReturn(Option::some('/path/to/file'))
                ->byDefault();
            $self->findOfficeUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->offices, $self->pagination))
                ->byDefault();
            $self->findShiftUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->shifts, $self->pagination))
                ->byDefault();
            $self->findUserUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->users, $self->pagination))
                ->byDefault();
            $self->findStaffUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->staffs, $self->pagination))
                ->byDefault();
            $self->config
                ->allows('get')
                ->andReturn(base_path('resources/spreadsheets'))
                ->byDefault();
            $self->temporaryFiles
                ->allows('create')
                ->andReturn(new SplFileInfo($self->path))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a InvalidArgumentException when the officeId is empty', function (): void {
            $this->assertThrows(
                InvalidArgumentException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->range, false, []);
                }
            );
        });
        $this->should('throw a RuntimeException when thrown TemporaryFileAccessException', function (): void {
            $this->fileStorage
                ->expects('store')
                ->andReturn(Option::none());

            $this->assertThrows(
                TemporaryFileAccessException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->range, false, $this->parameters);
                }
            );
        });
        $this->should('throw a RuntimeException when the failed to load template', function (): void {
            $this->config
                ->expects('get')
                ->with('zinger.path.resources.spreadsheets')
                ->andReturn('abcdefghijk');

            $this->assertThrows(
                RuntimeException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->range, false, $this->parameters);
                }
            );
        });
        $this->should('throw a RuntimeException when the failed to save the spreadsheet', function (): void {
            $this->temporaryFiles
                ->expects('create')
                ->andReturn(new SplFileInfo(''));
            $this->assertThrows(
                RuntimeException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->range, false, $this->parameters);
                }
            );
        });
        $this->should('throw a NotFoundException when officeId not found', function (): void {
            $this->findOfficeUseCase
                ->allows('handle')
                ->andReturn(
                    FinderResult::from(Seq::from(
                        $this->examples->offices[0]->copy(['id' => self::NOT_EXISTING_ID])
                    ), $this->pagination)
                )
                ->byDefault();
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->range, false, $this->parameters);
                }
            );
        });
    }
}
