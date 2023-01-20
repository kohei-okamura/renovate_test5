<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\LtcsInsCard\LtcsInsCardFinder;
use Domain\LtcsInsCard\LtcsInsCardRepository;
use Infrastructure\LtcsInsCard\LtcsInsCardFinderEloquentImpl;
use Infrastructure\LtcsInsCard\LtcsInsCardRepositoryEloquentImpl;
use UseCase\LtcsInsCard\CreateLtcsInsCardInteractor;
use UseCase\LtcsInsCard\CreateLtcsInsCardUseCase;
use UseCase\LtcsInsCard\DeleteLtcsInsCardInteractor;
use UseCase\LtcsInsCard\DeleteLtcsInsCardUseCase;
use UseCase\LtcsInsCard\EditLtcsInsCardInteractor;
use UseCase\LtcsInsCard\EditLtcsInsCardUseCase;
use UseCase\LtcsInsCard\FindLtcsInsCardInteractor;
use UseCase\LtcsInsCard\FindLtcsInsCardUseCase;
use UseCase\LtcsInsCard\IdentifyLtcsInsCardInteractor;
use UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase;
use UseCase\LtcsInsCard\LookupLtcsInsCardInteractor;
use UseCase\LtcsInsCard\LookupLtcsInsCardUseCase;

/**
 * LtcsInsCard Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class LtcsInsCardDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            CreateLtcsInsCardUseCase::class => CreateLtcsInsCardInteractor::class,
            DeleteLtcsInsCardUseCase::class => DeleteLtcsInsCardInteractor::class,
            EditLtcsInsCardUseCase::class => EditLtcsInsCardInteractor::class,
            FindLtcsInsCardUseCase::class => FindLtcsInsCardInteractor::class,
            IdentifyLtcsInsCardUseCase::class => IdentifyLtcsInsCardInteractor::class,
            LookupLtcsInsCardUseCase::class => LookupLtcsInsCardInteractor::class,
            LtcsInsCardFinder::class => LtcsInsCardFinderEloquentImpl::class,
            LtcsInsCardRepository::class => LtcsInsCardRepositoryEloquentImpl::class,
        ];
    }
}
