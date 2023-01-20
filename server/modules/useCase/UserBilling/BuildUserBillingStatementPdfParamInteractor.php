<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\User\User;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingStatementPdf;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Billing\LookupLtcsBillingBundleUseCase;
use UseCase\Billing\LookupLtcsBillingUseCase;
use UseCase\Billing\SimpleLookupDwsBillingStatementUseCase;
use UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase;
use UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesUseCase;
use UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 利用者請求：介護サービス利用明細書 PDFパラメータ組み立てユースケース実装.
 */
class BuildUserBillingStatementPdfParamInteractor implements BuildUserBillingStatementPdfParamUseCase
{
    /**
     * Constructor.
     *
     * @param \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase
     * @param \UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase $lookupLtcsBillingStatementUseCase
     * @param \UseCase\Billing\LookupLtcsBillingUseCase $lookupLtcsBillingUseCase
     * @param \UseCase\Billing\LookupLtcsBillingBundleUseCase $lookupLtcsBillingBundleUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param ResolveDwsNameFromServiceCodesUseCase $resolveDwsNameFromServiceCodesUseCase
     * @param ResolveLtcsNameFromServiceCodesUseCase $resolveLtcsNameFromServiceCodesUseCase
     */
    public function __construct(
        private readonly SimpleLookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase,
        private readonly SimpleLookupLtcsBillingStatementUseCase $lookupLtcsBillingStatementUseCase,
        private readonly LookupLtcsBillingUseCase $lookupLtcsBillingUseCase,
        private readonly LookupLtcsBillingBundleUseCase $lookupLtcsBillingBundleUseCase,
        private readonly LookupUserUseCase $lookupUserUseCase,
        private readonly ResolveDwsNameFromServiceCodesUseCase $resolveDwsNameFromServiceCodesUseCase,
        private readonly ResolveLtcsNameFromServiceCodesUseCase $resolveLtcsNameFromServiceCodesUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Seq $userBillings, Carbon $issuedOn): array
    {
        /** @var array|int[] $userIds */
        $userIds = $userBillings->map(fn (UserBilling $x): int => $x->userId)->distinct();
        /** @var int[]|\ScalikePHP\Seq $dwsStatementIds */
        $dwsStatementIds = $userBillings
            ->filter(fn (UserBilling $x): bool => $x->dwsItem !== null)
            ->map(fn (UserBilling $x): int => $x->dwsItem->dwsStatementId)
            ->distinct();
        /** @var int[]|\ScalikePHP\Seq $ltcsStatementIds */
        $ltcsStatementIds = $userBillings
            ->filter(fn (UserBilling $x): bool => $x->ltcsItem !== null)
            ->map(fn (UserBilling $x): int => $x->ltcsItem->ltcsStatementId)
            ->distinct();
        $users = $this->lookupUser($context, ...$userIds);
        $dwsStatements = !$dwsStatementIds->isEmpty()
            ? $this->lookupDwsBillingStatement($context, ...$dwsStatementIds)
            : Seq::empty();
        $ltcsStatements = !$ltcsStatementIds->isEmpty()
            ? $this->lookupLtcsBillingStatement($context, ...$ltcsStatementIds)
            : Seq::empty();
        $dwsServiceCodeMap = $this->getDwsServiceCodeMap($context, $dwsStatements);
        $ltcsServiceCodeBundlesMap = $this->getLtcsServiceCodeMap($context, $ltcsStatements);
        return [
            'billings' => $userBillings
                ->sortBy(fn (UserBilling $x): string => $x->user->name->phoneticDisplayName)
                ->flatMap(function (UserBilling $userBilling) use ($users, $issuedOn, $dwsStatements, $ltcsStatements, $dwsServiceCodeMap, $ltcsServiceCodeBundlesMap): Seq {
                    $dwsStatement = $userBilling->dwsItem === null
                        ? Option::none()
                        : $dwsStatements->find(fn (DwsBillingStatement $xx): bool => $userBilling->dwsItem->dwsStatementId === $xx->id);
                    $ltcsStatement = $userBilling->ltcsItem === null
                        ? Option::none()
                        : $ltcsStatements->find(fn (LtcsBillingStatement $xx): bool => $userBilling->ltcsItem->ltcsStatementId === $xx->id);
                    $user = $users->find(fn (User $x): bool => $x->id === $userBilling->userId)
                        ->getOrElse(function () use ($userBilling): void {
                            throw new LogicException("user({$userBilling->userId}) must be found");
                        });
                    $ltcsServiceCodeMap = $ltcsStatement->nonEmpty()
                        ? $ltcsServiceCodeBundlesMap->getOrElse($ltcsStatement->get()->bundleId, fn (): Map => Map::empty())
                        : Map::empty();
                    return UserBillingStatementPdf::from(
                        $user,
                        $userBilling,
                        $issuedOn,
                        $dwsStatement,
                        $ltcsStatement,
                        $dwsServiceCodeMap,
                        $ltcsServiceCodeMap
                    );
                }),
        ];
    }

    /**
     * 利用者を取得する.
     *
     * @param Context $context
     * @param int[] $ids
     * @return \Domain\User\User[]&\ScalikePHP\Seq
     */
    private function lookupUser(Context $context, int ...$ids): Seq
    {
        $users = $this->lookupUserUseCase->handle($context, Permission::viewUserBillings(), ...$ids);
        if ($users->isEmpty()) {
            $x = implode(',', $ids);
            throw new NotFoundException("User ({$x}) not found");
        }
        return $users;
    }

    /**
     * 障害福祉サービス請求：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $ids
     * @return \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq
     */
    private function lookupDwsBillingStatement(Context $context, int ...$ids): Seq
    {
        $entities = $this->lookupDwsBillingStatementUseCase->handle($context, Permission::viewUserBillings(), ...$ids);
        if (count($entities) !== count($ids)) {
            $x = implode(',', $ids);
            throw new NotFoundException("DwsBillingStatement ({$x}) not found");
        }
        return $entities;
    }

    /**
     * 介護保険サービス請求：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $ids
     * @return \Domain\Billing\LtcsBillingBundle[]&\ScalikePHP\Seq
     */
    private function lookupLtcsBillingStatement(Context $context, int ...$ids): Seq
    {
        $entities = $this->lookupLtcsBillingStatementUseCase->handle($context, Permission::viewUserBillings(), ...$ids);
        if (count($entities) !== count($ids)) {
            $x = implode(',', $ids);
            throw new NotFoundException("LtcsBillingStatement ({$x}) not found");
        }
        return $entities;
    }

    /**
     * 介護保険サービス請求：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Billing\LtcsBilling
     */
    private function lookupLtcsBilling(Context $context, int $id): LtcsBilling
    {
        return $this->lookupLtcsBillingUseCase->handle($context, Permission::viewUserBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id) {
                throw new NotFoundException("LtcsBilling ({$id}) not found");
            });
    }

    /**
     * 介護保険サービス請求：請求単位を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $ids
     * @param LtcsBilling $ltcsBilling
     * @return \Domain\Billing\LtcsBillingBundle[]&\ScalikePHP\Seq
     */
    private function lookupLtcsBillingBundle(Context $context, LtcsBilling $ltcsBilling, int ...$ids): Seq
    {
        $entities = $this->lookupLtcsBillingBundleUseCase->handle($context, Permission::viewUserBillings(), $ltcsBilling, ...$ids);
        if (count($entities) !== count($ids)) {
            $x = implode(',', $ids);
            throw new NotFoundException("LtcsBillingBundle ({$x}) not found");
        }
        return $entities;
    }

    /**
     * 障害福祉サービスコード => 辞書エントリ の Map を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq $statements
     * @return \ScalikePHP\Map
     */
    private function getDwsServiceCodeMap(Context $context, Seq $statements): Map
    {
        $homeHelpServiceServiceCodes = $statements
            ->flatMap(fn (DwsBillingStatement $statement): array => $statement->items)
            ->filter(fn (DwsBillingStatementItem $item): bool => $item->serviceCode->serviceDivisionCode === DwsServiceDivisionCode::homeHelpService()->value())
            ->map(fn (DwsBillingStatementItem $item): ServiceCode => $item->serviceCode);
        $visitingCareForPwsdServiceCodes = $statements
            ->flatMap(fn (DwsBillingStatement $statement): array => $statement->items)
            ->filter(fn (DwsBillingStatementItem $item): bool => $item->serviceCode->serviceDivisionCode === DwsServiceDivisionCode::visitingCareForPwsd()->value())
            ->map(fn (DwsBillingStatementItem $item): ServiceCode => $item->serviceCode);

        return $this->resolveDwsNameFromServiceCodesUseCase
            ->handle($context, Seq::from(...$homeHelpServiceServiceCodes, ...$visitingCareForPwsdServiceCodes));
    }

    /**
     * 介護保険サービスコード => サービス名称 の Map を 請求単位ごとに生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq $statements
     * @return \ScalikePHP\Map
     */
    private function getLtcsServiceCodeMap(Context $context, Seq $statements): Map
    {
        if ($statements->nonEmpty()) {
            $billingIds = $statements->map(fn (LtcsBillingStatement $x): int => $x->billingId)->distinct()->toArray();
            $bundles = Seq::fromArray($billingIds)->flatMap(function (int $id) use ($context, $statements) {
                $billing = $this->lookupLtcsBilling($context, $id);
                $bundleIds = $statements->filter(fn (LtcsBillingStatement $x): bool => $x->billingId === $id)
                    ->map(fn (LtcsBillingStatement $x): int => $x->bundleId)
                    ->distinct()
                    ->toArray();
                return $this->lookupLtcsBillingBundle($context, $billing, ...$bundleIds);
            })->computed();
            $map = $statements
                ->groupBy(fn (LtcsBillingStatement $statement): int => $statement->bundleId)
                ->mapValues(function (Seq $xs, int $bundleId) use ($bundles, $context): Map {
                    $serviceCodes = $xs->flatMap(fn (LtcsBillingStatement $statement): array => $statement->items)
                        ->map(fn (LtcsBillingStatementItem $x): ServiceCode => $x->serviceCode)
                        ->computed();
                    return $this->resolveLtcsNameFromServiceCodesUseCase->handle(
                        $context,
                        $serviceCodes,
                        $bundles->find(fn (LtcsBillingBundle $x): bool => $x->id === $bundleId)->get()->providedIn
                    );
                });
            // $map を return しても動作は変わらないが実行の遅延によりテストで呼び出しの検査がうまくできないので以下のようにする。
            return Map::from($map->toAssoc());
        } else {
            return Map::empty();
        }
    }
}
