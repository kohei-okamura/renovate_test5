<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use Generator;
use Lib\Arrays;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use Lib\Exceptions\SetupException;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書取得ユースケース実装.
 */
final class GetDwsBillingStatementInfoInteractor implements GetDwsBillingStatementInfoUseCase
{
    private DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder;
    private DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder;
    private LookupDwsBillingUseCase $lookupDwsBillingUseCase;
    private LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase;
    private LookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase;

    /**
     * constructor.
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder
     * @param \UseCase\Billing\LookupDwsBillingUseCase $lookupDwsBillingUseCase
     * @param \UseCase\Billing\LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase
     * @param \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase
     */
    public function __construct(
        DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder,
        DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder,
        LookupDwsBillingUseCase $lookupDwsBillingUseCase,
        LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase,
        LookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase
    ) {
        $this->homeHelpServiceDictionaryEntryFinder = $homeHelpServiceDictionaryEntryFinder;
        $this->visitingCareForPwsdDictionaryEntryFinder = $visitingCareForPwsdDictionaryEntryFinder;
        $this->lookupDwsBillingUseCase = $lookupDwsBillingUseCase;
        $this->lookupDwsBillingBundleUseCase = $lookupDwsBillingBundleUseCase;
        $this->lookupDwsBillingStatementUseCase = $lookupDwsBillingStatementUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $dwsBillingStatementId
    ): array {
        // Billing 取得
        $billing = $this->lookupDwsBillingUseCase
            ->handle($context, Permission::viewBillings(), $dwsBillingId)
            ->headOption()
            ->getOrElse(function () use ($dwsBillingId): void {
                throw new NotFoundException("DwsBilling({$dwsBillingId}) not found.");
            });

        // BillingBundle 取得
        $bundle = $this->lookupDwsBillingBundleUseCase
            ->handle($context, Permission::viewBillings(), $dwsBillingId, $dwsBillingBundleId)
            ->headOption()
            ->getOrElse(function () use ($dwsBillingBundleId): void {
                throw new NotFoundException("DwsBillingBundle({$dwsBillingBundleId}) not found.");
            });

        // Statement 取得
        $statement = $this->lookupDwsBillingStatementUseCase
            ->handle($context, Permission::viewBillings(), $dwsBillingId, $dwsBillingBundleId, $dwsBillingStatementId)
            ->headOption()
            ->getOrElse(function () use ($dwsBillingStatementId): void {
                throw new NotFoundException("DwsBillingStatement({$dwsBillingStatementId}) not found.");
            });

        // Dictionary 取得
        $serviceCodeDictionary = $this->findServiceCode($bundle, $statement);

        return compact('billing', 'bundle', 'statement', 'serviceCodeDictionary');
    }

    /**
     * サービスコード一覧を取得する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return array|array[]<'code', 'name'>
     */
    private function findServiceCode(DwsBillingBundle $bundle, DwsBillingStatement $statement): array
    {
        return Arrays::generate(function () use ($bundle, $statement): iterable {
            yield from $this->findHomeHelpServiceCode($bundle, $statement);
            yield from $this->findVisitingCareForPwsdServiceCode($bundle, $statement);
        });
    }

    /**
     * 居宅介護サービスコード一覧を取得する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @throws \Exception
     * @return \Generator
     */
    private function findHomeHelpServiceCode(
        DwsBillingBundle $bundle,
        DwsBillingStatement $statement
    ): Generator {
        $serviceCodes = Seq::fromArray($statement->items)
            ->filter(fn (DwsBillingStatementItem $x): bool => $x->serviceCode->serviceDivisionCode
                === DwsServiceDivisionCode::homeHelpService()->value())
            ->map(fn (DwsBillingStatementItem $x): string => $x->serviceCode->toString());

        if ($serviceCodes->count() === 0) {
            return;
        }

        $entries = $this->homeHelpServiceDictionaryEntryFinder
            ->find(
                ['providedIn' => $bundle->providedIn, 'serviceCodes' => $serviceCodes->toArray()],
                ['all' => true, 'sortBy' => 'id']
            )
            ->list;
        if ($serviceCodes->count() !== $entries->count()) {
            throw new SetupException("Invalid ServiceCode exists in Statement({$statement->id})");
        }
        yield from $entries
            ->toMap(fn (DwsHomeHelpServiceDictionaryEntry $x): string => $x->serviceCode->toString())
            ->mapValues(fn (DwsHomeHelpServiceDictionaryEntry $x): string => $x->name);
    }

    /**
     * 重度訪問介護サービスコード一覧を取得する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @throws \Exception
     * @return \Generator
     */
    private function findVisitingCareForPwsdServiceCode(
        DwsBillingBundle $bundle,
        DwsBillingStatement $statement
    ): Generator {
        $serviceCodes = Seq::fromArray($statement->items)
            ->filter(fn (DwsBillingStatementItem $x): bool => $x->serviceCode->serviceDivisionCode
                === DwsServiceDivisionCode::visitingCareForPwsd()->value())
            ->map(fn (DwsBillingStatementItem $x): string => $x->serviceCode->toString());

        if ($serviceCodes->count() === 0) {
            return;
        }

        $entries = $this->visitingCareForPwsdDictionaryEntryFinder
            ->find(
                ['providedIn' => $bundle->providedIn, 'serviceCodes' => $serviceCodes->toArray()],
                ['all' => true, 'sortBy' => 'id']
            )
            ->list;
        if ($serviceCodes->count() !== $entries->count()) {
            throw new RuntimeException("Invalid ServiceCode exists in Statement({$statement->id})");
        }
        yield from $entries
            ->toMap(fn (DwsVisitingCareForPwsdDictionaryEntry $x): string => $x->serviceCode->toString())
            ->mapValues(fn (DwsVisitingCareForPwsdDictionaryEntry $x): string => $x->name);
    }
}
