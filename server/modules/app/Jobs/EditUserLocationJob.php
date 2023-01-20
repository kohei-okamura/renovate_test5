<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Common\LocationResolver;
use Domain\Context\Context;
use Domain\User\User;
use UseCase\User\EditUserUseCase;

/**
 * 利用者位置情報更新ジョブ.
 */
final class EditUserLocationJob extends Job
{
    private Context $context;
    private User $user;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     */
    public function __construct(Context $context, User $user)
    {
        $this->context = $context;
        $this->user = $user;
    }

    /**
     * 住所情報から位置情報を取得し更新する.
     *
     * @param \Domain\Common\LocationResolver $resolver
     * @param \UseCase\User\EditUserUseCase $useCase
     * @return void
     */
    public function handle(
        LocationResolver $resolver,
        EditUserUseCase $useCase
    ): void {
        $locationOption = $resolver->resolve($this->context, $this->user->addr);
        if ($locationOption->nonEmpty()) {
            $location = $locationOption->head();
            $useCase->handle($this->context, $this->user->id, compact('location'), function (User $user): void {
                // 特に何もしない
            });
        }
    }
}
