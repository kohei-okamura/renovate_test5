<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\File\FileStorage;
use Domain\File\ReadonlyFileStorage;
use Domain\File\TemporaryFiles;
use Infrastructure\File\FileStorageImpl;
use Infrastructure\File\ReadonlyFileStorageImpl;
use Infrastructure\File\TemporaryFilesImpl;
use UseCase\File\DownloadFileInteractor;
use UseCase\File\DownloadFileUseCase;
use UseCase\File\DownloadStorageInteractor;
use UseCase\File\DownloadStorageUseCase;
use UseCase\File\GenerateFileNameContainsUserNameInteractor;
use UseCase\File\GenerateFileNameContainsUserNameUseCase;
use UseCase\File\GenerateFileNameInteractor;
use UseCase\File\GenerateFileNameUseCase;
use UseCase\File\StoreCsvInteractor;
use UseCase\File\StoreCsvUseCase;
use UseCase\File\StorePdfInteractor;
use UseCase\File\StorePdfUseCase;
use UseCase\File\UploadStorageInteractor;
use UseCase\File\UploadStorageUseCase;

/**
 * File Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class FileDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            DownloadStorageUseCase::class => DownloadStorageInteractor::class,
            DownloadFileUseCase::class => DownloadFileInteractor::class,
            FileStorage::class => FileStorageImpl::class,
            GenerateFileNameContainsUserNameUseCase::class => GenerateFileNameContainsUserNameInteractor::class,
            GenerateFileNameUseCase::class => GenerateFileNameInteractor::class,
            ReadonlyFileStorage::class => ReadonlyFileStorageImpl::class,
            StoreCsvUseCase::class => StoreCsvInteractor::class,
            StorePdfUseCase::class => StorePdfInteractor::class,
            TemporaryFiles::class => TemporaryFilesImpl::class,
            UploadStorageUseCase::class => UploadStorageInteractor::class,
        ];
    }
}
