<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\User;
use ScalikePHP\Seq;

/**
 * 利用者選択肢一覧取得ユースケース実装.
 */
class GetIndexUserOptionInteractor implements GetIndexUserOptionUseCase
{
    private FindUserUseCase $findUserUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\User\FindUserUseCase $findUserUseCase
     */
    public function __construct(FindUserUseCase $findUserUseCase)
    {
        $this->findUserUseCase = $findUserUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, array $officeIds): Seq
    {
        return $this->findUserUseCase->handle(
            $context,
            $permission,
            ['officeIds' => $officeIds],
            ['all' => true]
        )
            ->list
            ->map(fn (User $user): array => [
                'text' => $user->name->displayName,
                'value' => $user->id,
            ]);
    }
}
