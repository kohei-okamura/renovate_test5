<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\OwnExpenseProgram;

use Domain\Context\Context;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\OwnExpenseProgram\OwnExpenseProgramRepository;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 自費サービス情報取得ユースケース実装.
 */
final class LookupOwnExpenseProgramInteractor implements LookupOwnExpenseProgramUseCase
{
    private OwnExpenseProgramRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgramRepository $repository
     */
    public function __construct(OwnExpenseProgramRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$id): Seq
    {
        $xs = $this->repository->lookup(...$id);
        return $xs->forAll(fn (OwnExpenseProgram $x): bool => $this->canUse($context, $permission, $x))
            ? $xs
            : Seq::empty();
    }

    /**
     * 自費サービスが使用可能かを返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgram $program
     * @return bool
     */
    private function canUse(Context $context, Permission $permission, OwnExpenseProgram $program): bool
    {
        return $context->organization->id === $program->organizationId
            && ($program->isForAllOffices() || $context->isAccessibleTo($permission, $program->organizationId, [$program->officeId]));
    }
}
