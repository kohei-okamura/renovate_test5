<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Map;

/**
 * 入力値がスタッフが持っている権限か検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait AuthorizedPermissionsRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param array $values
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateAuthorizedPermissions(string $attribute, array $values, array $parameters): bool
    {
        try {
            $permissions = Map::from($values)
                ->filter(fn ($enabled) => $enabled)
                ->map(fn ($enabled, $key) => [$key, Permission::from($key)])
                ->values()
                ->toArray();
            return $this->context->isAuthorizedTo(...$permissions);
        } catch (InvalidArgumentException $exception) {
            return false;
        }
    }
}
