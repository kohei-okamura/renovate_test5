<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use UseCase\Office\FindOfficeUseCase;

/**
 * 入力値の「事業所グループID」に紐づく事業所がないことを確認する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NonRelationToOfficesRule
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
    protected function validateNonRelationToOffices(string $attribute, $value, array $parameters): bool
    {
        $officeGroupIds = Seq::fromArray(is_array($value) ? $value : [$value]);
        if ($officeGroupIds->exists(fn ($x): bool => !is_numeric($x))) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'non_relation_to_offices');
        $permission = Permission::from((string)$parameters[0]);
        /** @var \UseCase\Office\FindOfficeUseCase $useCase */
        $useCase = app(FindOfficeUseCase::class);
        return $useCase
            ->handle($this->context, [$permission], ['officeGroupIds' => $officeGroupIds->toArray()], ['all' => true])
            ->list
            ->isEmpty();
    }
}
