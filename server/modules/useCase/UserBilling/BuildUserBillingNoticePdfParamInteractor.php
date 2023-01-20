<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Billing\DwsBillingStatement;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingNoticePdf;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use UseCase\Billing\LookupDwsBillingBundleUseCase;
use UseCase\Billing\SimpleLookupDwsBillingStatementUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 代理受領額通知書パラメータ組み立てユースケース実装.
 */
final class BuildUserBillingNoticePdfParamInteractor implements BuildUserBillingNoticePdfParamUseCase
{
    /**
     * {@link \UseCase\UserBilling\BuildUserBillingNoticePdfParamInteractor} constructor.
     *
     * @param \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     */
    public function __construct(
        private SimpleLookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase,
        private LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase,
        private LookupUserUseCase $lookupUserUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Seq $userBillings, Carbon $issuedOn): array
    {
        $statementIds = $userBillings->map(fn (UserBilling $x): int => $x->dwsItem->dwsStatementId)->toArray();
        $statements = $this->lookupDwsBillingStatements($context, ...$statementIds);
        $statementMap = $statements->toMap('id');

        // TODO: 明細書1件ごとに請求単位を取得してるので効率悪そう。なんとかしたい。
        $bundleMap = $statements
            ->flatMap(fn (DwsBillingStatement $x): Seq => $this->lookupDwsBillingBundle(
                $context,
                $x->dwsBillingId,
                $x->dwsBillingBundleId
            ))
            ->toMap('id');

        // FYI: 以下の2行を明細書の取得より前に持っていくとテストが死ぬ.
        $userIds = $userBillings->map(fn (UserBilling $x): int => $x->userId)->distinct()->toArray();
        $userMap = $this->lookupUsers($context, ...$userIds)->toMap('id');

        // [FYI]
        // - `UserBilling` に対応する `User` が存在することは `lookupUsers` で保証済.
        // - `UserBilling` に対応する `DwsBillingStatement` が存在することは `lookupDwsBillingStatements` で保証済.
        $notices = $userBillings
            ->sortBy(fn (UserBilling $x): string => $x->user->name->phoneticDisplayName)
            ->flatMap(
                fn (UserBilling $userBilling): Seq => $statementMap
                    ->get($userBilling->dwsItem->dwsStatementId)
                    ->toSeq()
                    ->flatMap(fn (DwsBillingStatement $statement): Seq => UserBillingNoticePdf::from(
                        $userMap->getOrElse($statement->user->userId, function () use ($userBilling, $statement): void {
                            throw new LogicException("Failed to get User({$statement->user->userId}) for UserBilling({$userBilling->id})");
                        }),
                        $statement,
                        $bundleMap->getOrElse($statement->dwsBillingBundleId, function () use ($statement): void {
                            throw new LogicException("Failed to get DwsBillingBundle({$statement->dwsBillingBundleId}) for DwsBillingStatement({$statement->id})");
                        }),
                        $userBilling,
                        $issuedOn,
                    ))
            )
            ->computed();

        return compact('notices');
    }

    /**
     * 障害福祉サービス請求：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $ids
     * @return \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq
     */
    private function lookupDwsBillingStatements(Context $context, int ...$ids): Seq
    {
        $entities = $this->lookupDwsBillingStatementUseCase->handle($context, Permission::viewUserBillings(), ...$ids);
        if (count($entities) !== count($ids)) {
            $x = implode(',', $ids);
            throw new NotFoundException("DwsBillingStatement ({$x}) not found");
        }
        return $entities;
    }

    /**
     * 利用者を取得する.
     *
     * @param Context $context
     * @param int[] $ids
     * @return \Domain\User\User&\ScalikePHP\Seq
     */
    private function lookupUsers(Context $context, int ...$ids): Seq
    {
        $entities = $this->lookupUserUseCase->handle($context, Permission::viewUserBillings(), ...$ids);
        if (count($entities) !== count($ids)) {
            $x = implode(',', $ids);
            throw new NotFoundException("User ({$x}) not found");
        }
        return $entities;
    }

    /**
     * 障害福祉サービス: 請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int[] $bundleIds
     * @return \Domain\Billing\DwsBillingBundle&\ScalikePHP\Seq
     */
    private function lookupDwsBillingBundle(Context $context, int $billingId, int ...$bundleIds): Seq
    {
        $entities = $this->lookupDwsBillingBundleUseCase->handle(
            $context,
            Permission::viewUserBillings(),
            $billingId,
            ...$bundleIds
        );
        if (count($entities) !== count($bundleIds)) {
            $x = implode(',', $bundleIds);
            throw new NotFoundException("DwsBillingBundle ({$x}) not found");
        }
        return $entities;
    }
}
