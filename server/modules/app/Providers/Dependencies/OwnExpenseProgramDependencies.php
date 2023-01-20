<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\OwnExpenseProgram\OwnExpenseProgramFinder;
use Domain\OwnExpenseProgram\OwnExpenseProgramRepository;
use Infrastructure\OwnExpenseProgram\OwnExpenseProgramFinderEloquentImpl;
use Infrastructure\OwnExpenseProgram\OwnExpenseProgramRepositoryEloquentImpl;
use UseCase\OwnExpenseProgram\CreateOwnExpenseProgramInteractor;
use UseCase\OwnExpenseProgram\CreateOwnExpenseProgramUseCase;
use UseCase\OwnExpenseProgram\EditOwnExpenseProgramInteractor;
use UseCase\OwnExpenseProgram\EditOwnExpenseProgramUseCase;
use UseCase\OwnExpenseProgram\FindOwnExpenseProgramInteractor;
use UseCase\OwnExpenseProgram\FindOwnExpenseProgramUseCase;
use UseCase\OwnExpenseProgram\LookupOwnExpenseProgramInteractor;
use UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase;

/**
 * OwnExpenseProgram Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class OwnExpenseProgramDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            CreateOwnExpenseProgramUseCase::class => CreateOwnExpenseProgramInteractor::class,
            EditOwnExpenseProgramUseCase::class => EditOwnExpenseProgramInteractor::class,
            FindOwnExpenseProgramUseCase::class => FindOwnExpenseProgramInteractor::class,
            LookupOwnExpenseProgramUseCase::class => LookupOwnExpenseProgramInteractor::class,
            OwnExpenseProgramFinder::class => OwnExpenseProgramFinderEloquentImpl::class,
            OwnExpenseProgramRepository::class => OwnExpenseProgramRepositoryEloquentImpl::class,
        ];
    }
}
