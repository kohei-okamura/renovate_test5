<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use UseCase\User\LookupUserUseCase;

/**
 * 利用者名を含むファイル名生成ユースケース実装.
 */
final class GenerateFileNameContainsUserNameInteractor implements GenerateFileNameContainsUserNameUseCase
{
    private GenerateFileNameUseCase $generateFileNameUseCase;
    private LookupUserUseCase $lookupUserUseCase;

    /**
     * {@link \UseCase\File\GenerateFileNameContainsUserNameUseCase} constructor.
     *
     * @param \UseCase\File\GenerateFileNameUseCase $generateFileNameUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     */
    public function __construct(
        GenerateFileNameUseCase $generateFileNameUseCase,
        LookupUserUseCase $lookupUserUseCase
    ) {
        $this->generateFileNameUseCase = $generateFileNameUseCase;
        $this->lookupUserUseCase = $lookupUserUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, string $filename, array $replaceKeyValues = []): string
    {
        $user = $this->lookupUser($context, $userId);
        return $this->generateFileNameUseCase
            ->handle(
                $filename,
                ['user' => $user->name->displayName] + $replaceKeyValues
            );
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $userId): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::updateLtcsProvisionReports(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }
}
