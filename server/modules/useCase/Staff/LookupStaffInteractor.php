<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use Domain\Staff\StaffRepository;
use ScalikePHP\Seq;

/**
 * スタッフ情報取得ユースケース実装.
 */
final class LookupStaffInteractor implements LookupStaffUseCase
{
    private StaffRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffRepository $repository
     */
    public function __construct(StaffRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$id): Seq
    {
        $xs = $this->repository->lookup(...$id);
        return $xs->forAll(
            fn (Staff $x): bool => $context->isAccessibleTo($permission, $x->organizationId, $x->officeIds, $x->id)
        )
            ? $xs
            : Seq::emptySeq();
    }
}
