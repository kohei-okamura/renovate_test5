<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\Billing\LookupDwsBillingUseCase;

/**
 * 入力値の「利用者負担上限額管理結果票：明細」が他社のサービス提供がある場合に上限管理事業所のみでないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NotOnlyCopayCoordinationOfficeRule
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
    protected function validateNotOnlyCopayCoordinationOffice(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(3, $parameters, 'not_only_copay_coordination_office');
        $dwsBillingId = (int)Arr::get($this->data, $parameters[0]);
        $isProvided = (bool)Arr::get($this->data, $parameters[1]);
        $permission = Permission::from((string)$parameters[2]);
        // サービス提供なしの場合は上限管理事業所のみのためエラーとしない
        if (!$isProvided) {
            return true;
        }

        // 利用者負担上限額管理結果票：明細が配列でない場合ここではエラーとしない
        if (!is_array($value)) {
            return true;
        }
        // 利用者負担上限額管理結果票：明細が空の場合ここではエラーとしない
        if (count($value) === 0) {
            return true;
        }

        $items = Seq::fromArray($value);
        $headItem = $items->headOption()->orNull();

        $lookupDwsBillingUseCase = app(LookupDwsBillingUseCase::class);
        assert($lookupDwsBillingUseCase instanceof LookupDwsBillingUseCase);

        /** @var null|\Domain\Billing\DwsBilling $dwsBilling */
        $dwsBilling = $lookupDwsBillingUseCase
            ->handle($this->context, $permission, $dwsBillingId)
            ->headOption()
            ->orNull();
        // 障害福祉サービス：請求が見つからない場合ここではエラーとしない
        if ($dwsBilling === null) {
            return true;
        }

        return $dwsBilling->office->officeId !== $headItem['officeId'] || $items->size() !== 1;
    }
}
