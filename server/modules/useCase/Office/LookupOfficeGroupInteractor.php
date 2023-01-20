<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\OfficeGroup;
use Domain\Office\OfficeGroupRepository;
use ScalikePHP\Seq;

/**
 * 事業所グループ情報取得ユースケース実装.
 */
final class LookupOfficeGroupInteractor implements LookupOfficeGroupUseCase
{
    private OfficeGroupRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Office\OfficeGroupRepository $repository
     */
    public function __construct(OfficeGroupRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$id): Seq
    {
        $xs = $this->repository->lookup(...$id);
        return $xs->forAll(fn (OfficeGroup $x): bool => $x->organizationId === $context->organization->id)
            ? $xs
            : Seq::emptySeq();
    }
}
