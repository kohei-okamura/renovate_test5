<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;

/**
 * 利用者保証実装.
 */
class EnsureUserInteractor implements EnsureUserUseCase
{
    private LookupUserUseCase $useCase;

    /**
     * Constructor.
     * @param \UseCase\User\LookupUserUseCase $useCase
     */
    public function __construct(LookupUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $id): void
    {
        $this->useCase
            ->handle($context, $permission, $id)
            ->headOption()
            ->getOrElse(function () use ($id) {
                throw new NotFoundException("User[{$id}] is not found");
            });
    }
}
