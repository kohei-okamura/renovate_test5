<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use UseCase\BankAccount\LookupBankAccountUseCase;
use UseCase\Contract\FindContractUseCase;
use UseCase\DwsCertification\FindDwsCertificationUseCase;
use UseCase\LtcsInsCard\FindLtcsInsCardUseCase;
use UseCase\Project\FindDwsProjectUseCase;
use UseCase\Project\FindLtcsProjectUseCase;

/**
 * 利用者情報取得実装.
 */
final class GetUserInfoInteractor implements GetUserInfoUseCase
{
    /**
     * Constructor.
     * @param \UseCase\BankAccount\LookupBankAccountUseCase $lookupBankAccountUseCase
     * @param \UseCase\Contract\FindContractUseCase $findContractUseCase
     * @param \UseCase\DwsCertification\FindDwsCertificationUseCase $findDwsCertificationUseCase
     * @param \UseCase\Project\FindDwsProjectUseCase $findDwsProjectUseCase
     * @param \UseCase\LtcsInsCard\FindLtcsInsCardUseCase $findLtcsInsCardUseCase
     * @param \UseCase\Project\FindLtcsProjectUseCase $findLtcsProjectUseCase
     * @param \UseCase\User\FindUserDwsCalcSpecUseCase $findUserDwsCalcSpecUseCase
     * @param \UseCase\User\FindUserDwsSubsidyUseCase $findUserDwsSubsidyUseCase
     * @param \UseCase\User\FindUserLtcsCalcSpecUseCase $findUserLtcsCalcSpecUseCase
     * @param \UseCase\User\FindUserLtcsSubsidyUseCase $findUserLtcsSubsidyUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     */
    public function __construct(
        private LookupBankAccountUseCase $lookupBankAccountUseCase,
        private FindContractUseCase $findContractUseCase,
        private FindDwsCertificationUseCase $findDwsCertificationUseCase,
        private FindDwsProjectUseCase $findDwsProjectUseCase,
        private FindLtcsInsCardUseCase $findLtcsInsCardUseCase,
        private FindLtcsProjectUseCase $findLtcsProjectUseCase,
        private FindUserDwsCalcSpecUseCase $findUserDwsCalcSpecUseCase,
        private FindUserDwsSubsidyUseCase $findUserDwsSubsidyUseCase,
        private FindUserLtcsCalcSpecUseCase $findUserLtcsCalcSpecUseCase,
        private FindUserLtcsSubsidyUseCase $findUserLtcsSubsidyUseCase,
        private LookupUserUseCase $lookupUserUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): array
    {
        /** @var \Domain\User\User $user */
        $user = $this->lookupUserUseCase->handle($context, Permission::viewUsers(), $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("user with id {$id} not found");
        });
        $bankAccount = $context->isAuthorizedTo(Permission::viewUsersBankAccount())
            ? $this->lookupBankAccountUseCase->handle($context, $user->bankAccountId)->headOption()->getOrElse(function () use ($user): void {
                throw new NotFoundException("bankAccount with id {$user->bankAccountId} not found");
            })
            : null;

        $dwsCertifications = $context->isAuthorizedTo(Permission::listDwsCertifications())
            ? $this->findDwsCertificationUseCase
                ->handle($context, Permission::viewUsers(), ['userId' => $id], ['all' => true])
                ->list
                ->toArray()
            : [];

        $ltcsInsCards = $context->isAuthorizedTo(Permission::listLtcsInsCards())
            ? $this->findLtcsInsCardUseCase
                ->handle($context, Permission::viewUsers(), ['userId' => $id], ['all' => true])
                ->list
                ->toArray()
            : [];

        $dwsCalcSpecs = $context->isAuthorizedTo(Permission::listUserDwsCalcSpecs())
            ? $this
                ->findUserDwsCalcSpecUseCase
                ->handle(
                    $context,
                    Permission::viewUsers(),
                    ['userId' => $id],
                    ['sortBy' => 'effectivatedOn', 'desc' => true, 'all' => true]
                )
                ->list
                ->toArray()
            : [];

        $ltcsCalcSpecs = $context->isAuthorizedTo(Permission::listUserLtcsCalcSpecs())
            ? $this
                ->findUserLtcsCalcSpecUseCase
                ->handle(
                    $context,
                    Permission::viewUsers(),
                    ['userId' => $id],
                    ['sortBy' => 'effectivatedOn', 'desc' => true, 'all' => true]
                )
                ->list
                ->toArray()
            : [];

        $hasDwsContract = $context->isAuthorizedTo(Permission::listDwsContracts());
        $hasLtcsContract = $context->isAuthorizedTo(Permission::listLtcsContracts());
        $contracts = $this->findContractUseCase->handle(
            $context,
            Permission::viewUsers(),
            ['userId' => $id],
            ['all' => true]
        )
            ->list
            ->filter(function (Contract $x) use ($hasDwsContract, $hasLtcsContract): bool {
                if (!$hasDwsContract && $x->serviceSegment === ServiceSegment::disabilitiesWelfare()) {
                    return false;
                } elseif (!$hasLtcsContract && $x->serviceSegment === ServiceSegment::longTermCare()) {
                    return false;
                }
                return true;
            })
            ->toArray();
        $dwsProjects = $context->isAuthorizedTo(Permission::listDwsProjects())
            ? $this->findDwsProjectUseCase->handle($context, Permission::viewUsers(), ['userId' => $id], ['all' => true])->list->toArray()
            : [];
        $ltcsProjects = $context->isAuthorizedTo(Permission::listLtcsProjects())
            ? $this->findLtcsProjectUseCase->handle($context, Permission::viewUsers(), ['userId' => $id], ['all' => true])->list->toArray()
            : [];

        $dwsSubsidies = $this->findUserDwsSubsidyUseCase->handle(
            $context,
            Permission::viewUsers(),
            ['userId' => $id],
            ['all' => true]
        )->list;
        $ltcsSubsidies = $this->findUserLtcsSubsidyUseCase->handle(
            $context,
            Permission::viewUsers(),
            ['userId' => $id],
            ['all' => true]
        )->list;

        return compact(
            'bankAccount',
            'contracts',
            'dwsCalcSpecs',
            'dwsCertifications',
            'dwsProjects',
            'dwsSubsidies',
            'ltcsCalcSpecs',
            'ltcsInsCards',
            'ltcsProjects',
            'ltcsSubsidies',
            'user'
        );
    }
}
