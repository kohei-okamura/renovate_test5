<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Shift\Task;
use Illuminate\Support\Arr;
use Lib\Exceptions\NotFoundException;
use UseCase\Contract\IdentifyContractUseCase;

/**
 * 入力値の利用者が存在しているかつ入力された事業所と紐付いている（＝仮契約または本契約がある）ことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBelongsToOfficeRule
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
    protected function validateUserBelongsToOffice(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(3, $parameters, 'user_belongs_to_office');
        $officeId = Arr::get($this->data, $parameters[0]);
        if ($parameters[1] === 'task') {
            $taskValue = Arr::get($this->data, 'task');
        } else {
            // 一括登録バリデーションで使う場合の処理
            preg_match('/\\.(\\d+)\\./', $attribute, $match);
            $index = $match[1];
            $taskValue = Arr::get($this->data, preg_replace('/\\.\\*\\./', ".{$index}.", $parameters[1]));
        }
        $permissionStr = $parameters[2];

        if (empty($officeId)) {
            return true;
        }
        if (!Task::isValid($taskValue)) {
            // Task が不正の場合、ここではエラーとしない
            return true;
        }
        if (!Permission::isValid($permissionStr)) {
            // Permission の指定がおかしい場合はバリデーションエラー
            return false;
        }

        $serviceSegment = Task::from($taskValue)->toServiceSegment()->orNull();
        if ($serviceSegment === null) {
            return false;
        }

        $useCase = app(IdentifyContractUseCase::class);
        assert($useCase instanceof IdentifyContractUseCase);
        try {
            return $useCase
                ->handle(
                    $this->context,
                    Permission::from($permissionStr),
                    $officeId,
                    $value,
                    $serviceSegment,
                    Carbon::now()
                )
                ->nonEmpty();
        } catch (NotFoundException $e) {
            // UseCase内で発生する NotFoundException は、UserまたはOfficeにアクセスができない場合
            return false;
        }
    }
}
