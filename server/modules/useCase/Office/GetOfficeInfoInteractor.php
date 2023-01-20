<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;

/**
 * 事業所情報取得ユースケース実装.
 */
class GetOfficeInfoInteractor implements GetOfficeInfoUseCase
{
    private FindHomeHelpServiceCalcSpecUseCase $findHomeHelpServiceCalcSpec;
    private FindHomeVisitLongTermCareCalcSpecUseCase $findHomeVisitLongTermCareCalcSpec;
    private FindVisitingCareForPwsdCalcSpecUseCase $findVisitingCareForPwsdCalcSpec;
    private LookupOfficeGroupUseCase $lookupOfficeGroupUseCase;
    private LookupOfficeUseCase $lookupOfficeUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Office\FindHomeHelpServiceCalcSpecUseCase $findHomeHelpServiceCalcSpec
     * @param \UseCase\Office\FindHomeVisitLongTermCareCalcSpecUseCase $findHomeVisitLongTermCareCalcSpec
     * @param \UseCase\Office\FindVisitingCareForPwsdCalcSpecUseCase $findVisitingCareForPwsdCalcSpec
     * @param \UseCase\Office\LookupOfficeGroupUseCase $lookupOfficeGroupUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     */
    public function __construct(
        FindHomeHelpServiceCalcSpecUseCase $findHomeHelpServiceCalcSpec,
        FindHomeVisitLongTermCareCalcSpecUseCase $findHomeVisitLongTermCareCalcSpec,
        FindVisitingCareForPwsdCalcSpecUseCase $findVisitingCareForPwsdCalcSpec,
        LookupOfficeGroupUseCase $lookupOfficeGroupUseCase,
        LookupOfficeUseCase $lookupOfficeUseCase
    ) {
        $this->findHomeHelpServiceCalcSpec = $findHomeHelpServiceCalcSpec;
        $this->findHomeVisitLongTermCareCalcSpec = $findHomeVisitLongTermCareCalcSpec;
        $this->findVisitingCareForPwsdCalcSpec = $findVisitingCareForPwsdCalcSpec;
        $this->lookupOfficeGroupUseCase = $lookupOfficeGroupUseCase;
        $this->lookupOfficeUseCase = $lookupOfficeUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): array
    {
        /** @var \Domain\Office\Office $office */
        $office = $this->lookupOfficeUseCase
            ->handle($context, [Permission::viewInternalOffices(), Permission::viewExternalOffices()], $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("Office({$id}) not found");
            });

        // 事業所グループ
        $officeGroup = $office->officeGroupId
            ? $this->lookupOfficeGroupUseCase
                ->handle($context, $office->officeGroupId)
                ->headOption()
                ->getOrElse(function () use ($office): void {
                    throw new RuntimeException("Related OfficeGroup({$office->officeGroupId}) not found");
                })
            : null;

        // 自社事業所権限を持っていない場合に算定情報を取得しようとすると例外が投げられるため、取得処理を行わずに空で返す
        if (!$context->isAuthorizedTo(Permission::viewInternalOffices())) {
            return [
                'office' => $office,
                'officeGroup' => $officeGroup,
                'homeHelpServiceCalcSpecs' => [],
                'homeVisitLongTermCareCalcSpecs' => [],
                'visitingCareForPwsdCalcSpecs' => [],
            ];
        }
        // 加算情報
        // 下記のような並び順にしたいので sortBy は使用しない（それぞれの Finder で指定している）
        // 1. 適用期間開始日の降順
        // 2. 適用期間終了日の降順
        // 3. 登録日時（または主キー）の降順
        $homeHelpServiceCalcSpecs = $this->findHomeHelpServiceCalcSpec
            ->handle($context, Permission::viewInternalOffices(), ['officeId' => $id], ['all' => true])
            ->list->toArray();
        $homeVisitLongTermCareCalcSpecs = $this->findHomeVisitLongTermCareCalcSpec
            ->handle($context, Permission::viewInternalOffices(), ['officeId' => $id], ['all' => true])
            ->list->toArray();
        $visitingCareForPwsdCalcSpecs = $this->findVisitingCareForPwsdCalcSpec
            ->handle($context, Permission::viewInternalOffices(), ['officeId' => $id], ['all' => true])
            ->list->toArray();

        return compact(
            'office',
            'officeGroup',
            'homeHelpServiceCalcSpecs',
            'homeVisitLongTermCareCalcSpecs',
            'visitingCareForPwsdCalcSpecs'
        );
    }
}
