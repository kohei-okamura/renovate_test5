<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Job;

use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\Job\Job;
use Illuminate\Http\UploadedFile;
use Lib\Exceptions\TemporaryFileAccessException;
use Mockery;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\UploadUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Job\CreateJobWithFileInteractor;

/**
 * {@link \UseCase\Job\CreateJobWithFileInteractor} Test.
 */
class CreateJobWithFileInteractorTest extends Test
{
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use UploadUseCaseMixin;

    private const FILE_NAME = 'upload_file';

    /**
     * @var callable|\Closure|\Mockery\MockInterface
     */
    private $callable;
    private CreateJobWithFileInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateJobWithFileInteractorTest $self): void {
            $self->createJobUseCase
                ->allows('handle')
                ->andReturn(Job::create())
                ->byDefault();
            $self->uploadUseCase
                ->allows('handle')
                ->andReturn(Option::from(self::FILE_NAME))
                ->byDefault();

            $self->callable = Mockery::spy(fn (Job $job) => 'RUN CALLBACK');

            $self->interactor = app(CreateJobWithFileInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('succeed process normally with callback called', function (): void {
            $stream = FileInputStream::fromFile(UploadedFile::fake()->create('example.xlsx'));
            $this->createJobUseCase
                ->expects('handle')
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, $f): Job {
                    $f($this->examples->jobs[0]);
                    return $this->examples->jobs[0];
                });

            $this->interactor->handle($this->context, $stream, $this->callable);
        });

        $this->should('store upload file use UploadUseCase', function (): void {
            $stream = FileInputStream::fromFile(UploadedFile::fake()->create('example.xlsx'));
            $this->uploadUseCase
                ->expects('handle')
                ->with($this->context, typeOf('string'), $stream)
                ->andReturn(Option::from(self::FILE_NAME));

            $this->interactor->handle($this->context, $stream, $this->callable);
        });

        $this->should('throw TemporsryFileAccessException when UploadUseCase return none', function (): void {
            $stream = FileInputStream::fromFile(UploadedFile::fake()->create('example.xlsx'));
            $this->uploadUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(
                TemporaryFileAccessException::class,
                function () use ($stream): void {
                    $this->interactor->handle($this->context, $stream, $this->callable);
                }
            );
        });
    }
}
