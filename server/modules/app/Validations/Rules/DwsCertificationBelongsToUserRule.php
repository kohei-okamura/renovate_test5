<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use UseCase\DwsCertification\LookupDwsCertificationUseCase;

/**
 * 入力値の受給者証が存在しているかつ入力された利用者と紐付いていることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsCertificationBelongsToUserRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsCertificationBelongsToUser(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'dws_certification_belongs_to_user');
        $userId = Arr::get($this->data, $parameters[0]);
        $permission = Permission::from((string)$parameters[1]);

        if (empty($userId)) {
            return true;
        }

        $useCase = app(LookupDwsCertificationUseCase::class);
        assert($useCase instanceof LookupDwsCertificationUseCase);
        return $useCase->handle($this->context, $permission, $userId, $value)->nonEmpty();
    }
}
