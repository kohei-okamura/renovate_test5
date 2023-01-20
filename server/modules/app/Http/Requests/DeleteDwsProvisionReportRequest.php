<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Validation\Validator;
use ScalikePHP\Option;
use UseCase\ProvisionReport\GetDwsProvisionReportUseCase;

/**
 * 障害福祉サービス：予実削除リクエスト.
 *
 * @property-read int $officeId
 * @property-read int $userId
 * @property-read string $providedIn
 */
class DeleteDwsProvisionReportRequest extends StaffRequest implements ValidatesWhenResolved
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
                ->map(function (DwsProvisionReport $x) use ($validator): void {
                    if ($x->status === DwsProvisionReportStatus::fixed()) {
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
     * 障害福祉サービス：予実取得ユースケースを取得.
     *
     * @return \UseCase\ProvisionReport\GetDwsProvisionReportUseCase
     */
    private function getUseCase(): GetDwsProvisionReportUseCase
    {
        return app(GetDwsProvisionReportUseCase::class);
    }

    /**
     * エンティティを取得.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @return \Domain\ProvisionReport\DwsProvisionReport|\ScalikePHP\Option
     */
    private function getEntity(int $officeId, int $userId, string $providedIn): Option
    {
        return $this->getUseCase()->handle(
            $this->context(),
            Permission::updateDwsProvisionReports(),
            $officeId,
            $userId,
            Carbon::parse($providedIn)
        );
    }
}
