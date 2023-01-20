<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Validator;
use ScalikePHP\Seq;
use UseCase\Shift\LookupShiftUseCase;

/**
 * 勤務シフト一括キャンセルリクエスト.
 *
 * @property-read array|int[] $ids
 * @property-read string $reason
 */
class BulkCancelShiftRequest extends StaffRequest implements ValidatesWhenResolved
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
            $idsSeq = Seq::from(...$data['ids']);
            /** @var \UseCase\Shift\LookupShiftUseCase $useCase */
            $useCase = app(LookupShiftUseCase::class);
            /** @var null|\Domain\Shift\Shift $shift */
            $shifts = $useCase->handle(
                $this->context(),
                Permission::updateShifts(),
                ...$idsSeq->map(fn ($x): int => (int)$x)->toArray(),
            );

            if ($shifts->count() !== $idsSeq->count()) {
                // ここではエラーにしない.
                return;
            }

            $pastShiftIds = $shifts->filter(function (Shift $x): bool {
                return $x->schedule->start->lt(Carbon::now());
            })
                ->map(fn (Shift $x): int => $x->id)
                ->toArray();

            if (!empty($pastShiftIds)) {
                $validator->errors()->add('ids', '過去の勤務シフト(' . implode($pastShiftIds) . ')はキャンセルできません。');
            }
        });
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'ids' => [
                'bail',
                'required',
                'array',
                'non_canceled:' . LookupShiftUseCase::class . ',' . Permission::updateShifts(),
            ],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
