<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Validator;
use UseCase\Shift\LookupShiftUseCase;

/**
 * 勤務シフトキャンセルリクエスト.
 *
 * @property-read int $id
 * @property-read string $reason
 */
class CancelShiftRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * バリデータインスタンスの設定.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->messages()->isNotEmpty()) {
                // すでにfailの場合は実行しない
                return;
            }
            // $this ではリクエストパラメータが入ってこないので、validator の値を使って検証
            $data = $validator->getData();
            /** @var \UseCase\Shift\LookupShiftUseCase $useCase */
            $useCase = app(LookupShiftUseCase::class);
            /** @var null|\Domain\Shift\Shift $shift */
            $shift = $useCase->handle(
                $this->context(),
                Permission::updateShifts(),
                (int)$data['id'],
            )
                ->headOption()
                ->getOrElseValue(null);

            if ($shift === null) {
                // ここではエラーにしない.
                return;
            }

            if ($shift->schedule->start->lt(Carbon::now())) {
                $validator->errors()->add('id', '過去の勤務シフトはキャンセルできません。');
            }
        });
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'id' => ['non_canceled:' . LookupShiftUseCase::class . ',' . Permission::updateShifts()],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
