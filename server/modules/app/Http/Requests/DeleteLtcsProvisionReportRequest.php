<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Validator;
use ScalikePHP\Option;
use UseCase\ProvisionReport\GetLtcsProvisionReportUseCase;

/**
 * 介護保険サービス：予実削除リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read string $providedIn
 */
class DeleteLtcsProvisionReportRequest extends StaffRequest implements ValidatesWhenResolved
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
            $officeId = (int)$data['officeId'];
            $userId = (int)$data['userId'];
            $providedIn = $data['providedIn'];

            $this->getEntity($officeId, $userId, $providedIn)
                ->map(function (LtcsProvisionReport $x) use ($validator): void {
                    if ($x->status === LtcsProvisionReportStatus::fixed()) {
                        $validator->errors()->add('plans', '確定済みの予実は編集できません。');
                    }
                })
                ->getOrElse(function (): void {
                    // 何もしない
                });
        });
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [];
    }

    /**
     * 介護保険サービス：予実取得ユースケースを取得.
     *
     * @return \UseCase\ProvisionReport\GetLtcsProvisionReportUseCase
     */
    private function getUseCase(): GetLtcsProvisionReportUseCase
    {
        return app(GetLtcsProvisionReportUseCase::class);
    }

    /**
     * エンティティを取得.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @return \Domain\ProvisionReport\LtcsProvisionReport|\ScalikePHP\Option
     */
    private function getEntity(int $officeId, int $userId, string $providedIn): Option
    {
        return $this->getUseCase()->handle(
            $this->context(),
            Permission::updateLtcsProvisionReports(),
            $officeId,
            $userId,
            Carbon::parse($providedIn)
        );
    }
}
