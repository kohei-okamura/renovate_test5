<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Office\OfficeRepository;
use ScalikePHP\Seq;

/**
 * 事業所情報取得ユースケース実装.
 */
final class LookupOfficeInteractor implements LookupOfficeUseCase
{
    private OfficeRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Office\OfficeRepository $repository
     */
    public function __construct(OfficeRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $permissions, int ...$id): Seq
    {
        $xs = $this->repository->lookup(...$id);
        return $xs->forAll(
            fn (Office $x): bool => $context->isAccessibleTo($permissions, $x->organizationId, [$x->id])
        )
            ? $xs
            : Seq::emptySeq();
    }
}
