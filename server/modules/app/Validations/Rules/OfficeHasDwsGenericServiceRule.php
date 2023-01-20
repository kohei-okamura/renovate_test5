<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Office\Office;
use UseCase\Office\GetOfficeListUseCase;

/**
 * 入力値の「事業所ID」の事業所が障害福祉サービスを提供していることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait OfficeHasDwsGenericServiceRule
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
    protected function validateOfficeHasDwsGenericService(string $attribute, $value, array $parameters): bool
    {
        // 入力値の officeId が整数でない場合、ここではエラーとしない
        if (!$this->validateInteger($attribute, $value)) {
            return true;
        }

        /** @var \UseCase\Office\GetOfficeListUseCase $useCase */
        $useCase = app(GetOfficeListUseCase::class);
        $officeOption = $useCase->handle($this->context, $value)->headOption();
        // 事業所が存在しない場合、ここではエラーとしない
        if ($officeOption->isEmpty()) {
            return true;
        }

        return $officeOption->forAll(fn (Office $x): bool => $x->dwsGenericService !== null);
    }
}
