<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use ScalikePHP\Seq;
use UseCase\Office\FindOfficeGroupUseCase;

/**
 * 入力値の「事業所グループID」を親事業所グループとする事業所グループがないことを確認する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NotParentOfficeGroupRule
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
    protected function validateNotParentOfficeGroup(string $attribute, $value, array $parameters): bool
    {
        $officeGroupIds = Seq::fromArray(is_array($value) ? $value : [$value]);
        if ($officeGroupIds->exists(fn ($x): bool => !is_numeric($x))) {
            return false;
        }

        /** @var \UseCase\Office\FindOfficeGroupUseCase $useCase */
        $useCase = app(FindOfficeGroupUseCase::class);
        return $useCase->handle($this->context, ['parentOfficeGroupIds' => $officeGroupIds->toArray()], ['all' => true])
            ->list
            ->isEmpty();
    }
}
