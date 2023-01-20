<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Contract\ContractFinder;
use Domain\Contract\ContractStatus;
use Illuminate\Support\Arr;

/**
 * 利用者の有効フラグに更新可能か検証する.
 *
 * - 有効フラグを false に更新するのは契約状態が無効・契約終了のみの場合のみ可能
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserStatusCanUpdateToFalseRule
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
    protected function validateUserStatusCanUpdateToFalse(string $attribute, $value, array $parameters): bool
    {
        // 有効フラグがtrueの場合は常にtrue
        if ($value) {
            return true;
        } else {
            $this->requireParameterCount(1, $parameters, 'user_status_can_update_to_false');
            $userId = Arr::get($this->data, $parameters[0]);
            /** @var \Domain\Contract\ContractFinder $finder */
            $finder = app(ContractFinder::class);

            $filterParams = ['userId' => $userId, 'status' => [ContractStatus::formal(), ContractStatus::provisional()]];

            return $finder->find($filterParams, ['all' => true, 'sortBy' => 'id'])->list->isEmpty();
        }
    }
}
