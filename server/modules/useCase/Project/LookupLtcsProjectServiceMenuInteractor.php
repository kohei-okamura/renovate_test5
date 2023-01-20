<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Project\LtcsProjectServiceMenuRepository;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：計画：サービス内容取得実装.
 */
final class LookupLtcsProjectServiceMenuInteractor implements LookupLtcsProjectServiceMenuUseCase
{
    private LtcsProjectServiceMenuRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Project\LtcsProjectServiceMenuRepository $repository
     */
    public function __construct(LtcsProjectServiceMenuRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$ids): Seq
    {
        return $this->repository->lookup(...$ids);
    }
}
