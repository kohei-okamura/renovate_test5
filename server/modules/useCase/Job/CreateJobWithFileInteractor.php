<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\Job\Job as DomainJob;
use Lib\Exceptions\TemporaryFileAccessException;
use Lib\RandomString;
use UseCase\File\UploadStorageUseCase;

/**
 * ファイルアップロードジョブ登録ユースケース実装.
 */
class CreateJobWithFileInteractor implements CreateJobWithFileUseCase
{
    private const TMPDIR_NAME_LENGTH = 32;
    private UploadStorageUseCase $uploadUseCase;
    private CreateJobUseCase $createJobUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\File\UploadStorageUseCase $uploadUseCase
     * @param \UseCase\Job\CreateJobUseCase $createJobUseCase
     */
    public function __construct(UploadStorageUseCase $uploadUseCase, CreateJobUseCase $createJobUseCase)
    {
        $this->uploadUseCase = $uploadUseCase;
        $this->createJobUseCase = $createJobUseCase;
    }

    /**
     * @param \Domain\Context\Context $context
     * @param \Domain\File\FileInputStream $stream
     * @param callable $f
     * @return \Domain\Job\Job
     */
    public function handle(Context $context, FileInputStream $stream, callable $f): DomainJob
    {
        $path = $this->uploadUseCase
            ->handle(
                $context,
                RandomString::generate(self::TMPDIR_NAME_LENGTH, RandomString::DEFAULT_TABLE),
                $stream
            )
            ->getOrElse(function (): void {
                throw new TemporaryFileAccessException();
            });
        return $this->createJobUseCase->handle($context, function (DomainJob $job) use ($f, $path): void {
            $f($job, $path);
        });
    }
}
