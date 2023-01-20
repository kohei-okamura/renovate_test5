<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Office\Purpose;
use Domain\Permission\Permission;

/**
 * 事業所一覧取得ユースケース実装.
 */
final class GetIndexOfficeInteractor implements GetIndexOfficeUseCase
{
    private FindOfficeUseCase $findOfficeUseCase;

    /**
     * {@link \UseCase\Office\GetIndexOfficeInteractor} Constructor.
     *
     * @param \UseCase\Office\FindOfficeUseCase $findOfficeUseCase
     */
    public function __construct(FindOfficeUseCase $findOfficeUseCase)
    {
        $this->findOfficeUseCase = $findOfficeUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        array $permissions,
        array $filterParams,
        array $paginationParams
    ): FinderResult {
        $filter = (function () {
            return [];
//            $hasInternal = $context->isAuthorizedTo(Permission::listInternalOffices());
//            $hasExternal = $context->isAuthorizedTo(Permission::listExternalOffices());
//            // 両方の権限を持つ場合はパラメーターで指定した条件に従うため指定不要
//            // どちらの権限も持たない場合は他のところで権限エラーになるためここでは指定不要
//            if (($hasInternal && $hasExternal) || (!$hasInternal && !$hasExternal)) {
//                return [];
//            }
//            return ['purpose' => $hasInternal ? Purpose::internal() : Purpose::external()];
        })();
        return $this->findOfficeUseCase
            ->handle(
                $context,
                $permissions,
                [...$filterParams, ...$filter],
                $paginationParams
            );
    }
}
