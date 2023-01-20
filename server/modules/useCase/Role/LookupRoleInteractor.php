<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;
use Domain\Role\Role;
use Domain\Role\RoleRepository;
use ScalikePHP\Seq;

/**
 * ロール情報取得ユースケース実装.
 */
final class LookupRoleInteractor implements LookupRoleUseCase
{
    private RoleRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Role\RoleRepository $repository
     */
    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$id): Seq
    {
        $xs = $this->repository->lookup(...$id);
        return $xs->forAll(fn (Role $x): bool => $x->organizationId === $context->organization->id)
            ? $xs
            : Seq::emptySeq();
    }
}
