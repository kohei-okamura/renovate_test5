<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Contract\ContractFinder;
use Domain\Contract\ContractRepository;
use Infrastructure\Contract\ContractFinderEloquentImpl;
use Infrastructure\Contract\ContractRepositoryEloquentImpl;
use UseCase\Contract\CreateContractInteractor;
use UseCase\Contract\CreateContractUseCase;
use UseCase\Contract\EditContractInteractor;
use UseCase\Contract\EditContractUseCase;
use UseCase\Contract\FindContractInteractor;
use UseCase\Contract\FindContractUseCase;
use UseCase\Contract\GetOverlapContractInteractor;
use UseCase\Contract\GetOverlapContractUseCase;
use UseCase\Contract\IdentifyContractInteractor;
use UseCase\Contract\IdentifyContractUseCase;
use UseCase\Contract\LookupContractInteractor;
use UseCase\Contract\LookupContractUseCase;

/**
 * Contract Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class ContractDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            ContractFinder::class => ContractFinderEloquentImpl::class,
            ContractRepository::class => ContractRepositoryEloquentImpl::class,
            CreateContractUseCase::class => CreateContractInteractor::class,
            EditContractUseCase::class => EditContractInteractor::class,
            FindContractUseCase::class => FindContractInteractor::class,
            GetOverlapContractUseCase::class => GetOverlapContractInteractor::class,
            IdentifyContractUseCase::class => IdentifyContractInteractor::class,
            LookupContractUseCase::class => LookupContractInteractor::class,
        ];
    }
}
