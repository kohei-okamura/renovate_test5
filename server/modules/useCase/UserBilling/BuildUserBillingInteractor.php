<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Closure;
use Domain\BankAccount\BankAccount;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\LtcsBillingStatement;
use Domain\Common\Carbon;
use Domain\Common\ConsumptionTaxRate;
use Domain\Common\Decimal;
use Domain\Common\Expense;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\Context\Context;
use Domain\Model;
use Domain\Office\Office;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\User\PaymentMethod;
use Domain\User\User;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingLtcsItem;
use Domain\UserBilling\UserBillingOffice;
use Domain\UserBilling\UserBillingOtherItem;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\UserBillingUser;
use Domain\UserBilling\WithdrawalResultCode;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use Lib\Math;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\BankAccount\LookupBankAccountUseCase;
use UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase;

/**
 * 利用者請求組み立てユースケース実装.
 */
final class BuildUserBillingInteractor implements BuildUserBillingUseCase
{
    /**
     * {@link \UseCase\UserBilling\BuildUserBillingInteractor} constructor.
     *
     * @param \UseCase\BankAccount\LookupBankAccountUseCase $lookupBankAccountUseCase
     * @param \UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase $lookupOwnExpenseProgramUseCase
     */
    public function __construct(
        private readonly LookupBankAccountUseCase $lookupBankAccountUseCase,
        private readonly LookupOwnExpenseProgramUseCase $lookupOwnExpenseProgramUseCase,
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        User $user,
        Office $office,
        Carbon $provideIn,
        Option $dwsBillingStatement,
        Option $ltcsBillingStatement,
        Option $dwsProvisionReport,
        Option $ltcsProvisionReport
    ): UserBilling {
        $otherItemSeq = $this->buildOtherItems($context, $dwsProvisionReport, $ltcsProvisionReport);

        $this->ensureBuildable($dwsBillingStatement, $ltcsBillingStatement, $otherItemSeq);

        $dwsItem = $this->buildDwsItem($dwsBillingStatement);
        $ltcsItem = $this->buildLtcsItem($ltcsBillingStatement);
        return UserBilling::create([
            'organizationId' => $context->organization->id,
            'userId' => $user->id,
            'officeId' => $office->id,
            'user' => $this->buildUserBillingUser($context, $user),
            'office' => UserBillingOffice::from($office),
            'dwsItem' => $dwsItem,
            'ltcsItem' => $ltcsItem,
            'otherItems' => $otherItemSeq->toArray(),
            'result' => $this->hasBillingAmount($dwsItem, $ltcsItem, $otherItemSeq)
                ? UserBillingResult::pending()
                : UserBillingResult::none(),
            'carriedOverAmount' => 0,
            'withdrawalResultCode' => WithdrawalResultCode::pending(),
            'providedIn' => $provideIn,
            'issuedOn' => Carbon::now(),
            'depositedAt' => null,
            'transactedAt' => null,
            'deductedOn' => $user->billingDestination->paymentMethod === PaymentMethod::withdrawal()
                ? Carbon::now()->firstOfMonth()->addDays(25)->getNextBusinessDay()
                : null,
            'dueDate' => Carbon::now()->endOfMonth(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * 利用者請求が組み立て可能であることを保証する.
     *
     * 以下の条件をすべてみたす場合は利用者請求を組み立てることができない.
     *
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $dwsBillingStatement
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Option $ltcsBillingStatement
     * @param \Domain\UserBilling\UserBillingOtherItem[]&\ScalikePHP\Seq $otherItemSeq
     * @return void
     */
    private function ensureBuildable(Option $dwsBillingStatement, Option $ltcsBillingStatement, Seq $otherItemSeq): void
    {
        $isNotBuildable = $dwsBillingStatement->isEmpty()
            && $ltcsBillingStatement->isEmpty()
            && $otherItemSeq->isEmpty();
        if ($isNotBuildable) {
            throw new LogicException('Cannot build UserBilling');
        }
    }

    /**
     * 利用者請求：障害福祉サービス明細を組み立てる.
     *
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $dwsBillingStatement
     * @return null|\Domain\UserBilling\UserBillingDwsItem
     */
    private function buildDwsItem(Option $dwsBillingStatement): ?UserBillingDwsItem
    {
        return $dwsBillingStatement
            ->map(fn (DwsBillingStatement $x): UserBillingDwsItem => UserBillingDwsItem::from($x))
            ->orNull();
    }

    /**
     * 利用者請求：介護保険サービス明細を組み立てる.
     *
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Option $ltcsBillingStatement
     * @return null|\Domain\UserBilling\UserBillingLtcsItem
     */
    private function buildLtcsItem(Option $ltcsBillingStatement): ?UserBillingLtcsItem
    {
        return $ltcsBillingStatement
            ->map(fn (LtcsBillingStatement $x): UserBillingLtcsItem => UserBillingLtcsItem::from($x))
            ->orNull();
    }

    /**
     * 利用者請求：利用者を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @return \Domain\UserBilling\UserBillingUser
     */
    private function buildUserBillingUser(Context $context, User $user): UserBillingUser
    {
        $bankAccount = $this->lookupBankAccount($context, $user->bankAccountId);
        return UserBillingUser::from($user, $bankAccount);
    }

    /**
     * 銀行口座を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $bankAccountId
     * @return \Domain\BankAccount\BankAccount
     */
    private function lookupBankAccount(Context $context, int $bankAccountId): BankAccount
    {
        return $this->lookupBankAccountUseCase->handle($context, $bankAccountId)
            ->headOption()
            ->getOrElse(function () use ($bankAccountId) {
                throw new NotFoundException("BankAccount({$bankAccountId}) not found");
            });
    }

    /**
     * 請求すべき金額があるかを返す
     *
     * @param null|\Domain\UserBilling\UserBillingDwsItem $dwsItem
     * @param null|\Domain\UserBilling\UserBillingLtcsItem $ltcsItem
     * @param \Domain\UserBilling\UserBillingOtherItem[]&\ScalikePHP\Seq $otherItemSeq
     * @return bool
     */
    private function hasBillingAmount(
        ?UserBillingDwsItem $dwsItem,
        ?UserBillingLtcsItem $ltcsItem,
        Seq $otherItemSeq
    ): bool {
        return Seq::from($dwsItem, $ltcsItem, ...$otherItemSeq)
            ->sumBy(fn (int $z, ?Model $x) => $z + ($x?->totalAmount ?? 0)) > 0;
    }

    /**
     * 税率ごとのその他サービス明細を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport&\ScalikePHP\Option $dwsProvisionReport
     * @param \Domain\ProvisionReport\LtcsProvisionReport&\ScalikePHP\Option $ltcsProvisionReport
     * @return \Domain\UserBilling\UserBillingOtherItem[]&\ScalikePHP\Seq
     */
    private function buildOtherItems(
        Context $context,
        Option $dwsProvisionReport,
        Option $ltcsProvisionReport
    ): Seq {
        // それぞれから 自費サービス のみを取り出す
        $filteredDwsProvisionReportItems = $dwsProvisionReport
            ->toSeq()
            ->flatMap(fn (DwsProvisionReport $x): array => $x->results)
            ->filter(fn (DwsProvisionReportItem $x): bool => !empty($x->ownExpenseProgramId));
        $filteredLtcsProvisionReportEntries = $ltcsProvisionReport
            ->toSeq()
            ->flatMap(fn (LtcsProvisionReport $x): array => $x->entries)
            ->filter(fn (LtcsProvisionReportEntry $x): bool => !empty($x->ownExpenseProgramId));

        $ownExpenseProgramIds = array_unique([
            ...$filteredDwsProvisionReportItems->map(
                fn (DwsProvisionReportItem $x): int => $x->ownExpenseProgramId
            ),
            ...$filteredLtcsProvisionReportEntries->map(
                fn (LtcsProvisionReportEntry $x): int => $x->ownExpenseProgramId
            ),
        ]);

        // 自費サービス ID が空の場合は何もしない
        if (empty($ownExpenseProgramIds)) {
            return Seq::empty();
        }

        $ownExpensePrograms = $this->lookupOwnExpenseProgram(
            $context,
            ...$ownExpenseProgramIds
        );

        /*
         * 下記の順番でその他サービス明細を生成
         * - 税率区分が「消費税」（10%）
         * - 税率区分が「消費税（軽減税率）」（8%）
         * - 税率区分が「該当なし」（0%）
         */
        return Seq::from(...TaxCategory::all())
            ->flatMap(fn (TaxCategory $x): Option => $this->buildOtherItem(
                $filteredDwsProvisionReportItems,
                $filteredLtcsProvisionReportEntries,
                $ownExpensePrograms,
                $x
            ))
            ->computed();
    }

    /**
     * その他サービス明細を組み立てる.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]&\ScalikePHP\Seq $dwsProvisionReportItems
     * @param \Domain\ProvisionReport\LtcsProvisionReportEntry[]&\ScalikePHP\Seq $ltcsProvisionReportEntries
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgram[]&\ScalikePHP\Seq $ownExpensePrograms
     * @param \Domain\Common\TaxCategory $taxCategory
     * @return \Domain\UserBilling\UserBillingOtherItem[]&\ScalikePHP\Option
     */
    private function buildOtherItem(
        Seq $dwsProvisionReportItems,
        Seq $ltcsProvisionReportEntries,
        Seq $ownExpensePrograms,
        TaxCategory $taxCategory
    ): Option {
        $taxRate = $this->getConsumptionTaxRate($taxCategory);

        // `Map` には `Seq#computed` 相当のメソッドがないので配列にしてから再度 `Map` に変換する
        $xs = $ownExpensePrograms
            ->filter(fn (OwnExpenseProgram $x): bool => $x->fee->taxCategory === $taxCategory)
            ->groupBy(fn (OwnExpenseProgram $x): int => $x->fee->taxType->value())
            ->mapValues(fn (Seq $xs): Map => $xs->toMap(fn (OwnExpenseProgram $x): int => $x->id))
            ->toAssoc();
        $map = Map::from($xs);

        $dwsItems = $dwsProvisionReportItems
            ->filter(fn (DwsProvisionReportItem $x): bool => $x->ownExpenseProgramId !== null)
            ->computed();

        $ltcsEntries = $ltcsProvisionReportEntries
            ->filter(fn (LtcsProvisionReportEntry $x): bool => $x->ownExpenseProgramId !== null)
            ->computed();

        $taxExcludedPrograms = $map->getOrElse(TaxType::taxExcluded()->value(), fn (): Map => Map::empty());
        $taxIncludedPrograms = $map->getOrElse(TaxType::taxIncluded()->value(), fn (): Map => Map::empty());
        $taxExemptedPrograms = $map->getOrElse(TaxType::taxExempted()->value(), fn (): Map => Map::empty());

        // 障害福祉サービス集計処理
        $aggregateDws = fn (Map $map, Closure $g): int => $dwsItems
            ->flatMap(function (DwsProvisionReportItem $item) use ($map, $g): Option {
                return $map
                    ->get($item->ownExpenseProgramId)
                    ->map(function (OwnExpenseProgram $x) use ($g, $item): int {
                        return $g($x->fee)
                            * Math::ceil($item->schedule->toRange()->durationMinutes() / $x->durationMinutes)
                            * $item->headcount;
                    });
            })
            ->sum();

        // 介護保険サービス集計処理
        $aggregateLtcs = fn (Map $map, Closure $g): int => $ltcsEntries
            ->flatMap(function (LtcsProvisionReportEntry $entry) use ($map, $g): Option {
                return $map
                    ->get($entry->ownExpenseProgramId)
                    ->map(function (OwnExpenseProgram $x) use ($g, $entry): int {
                        // サービスの提供時間数 / 自費サービスの単位時間数（切り上げ）を計算して一回の予実あたりの自費サービス回数を割り出す。
                        return $g($x->fee)
                            * Math::ceil($entry->slot->toMinutes() / $x->durationMinutes)
                            * count($entry->results)
                            * $entry->headcount;
                    });
            })
            ->sum();

        // 「税抜」の自費サービスの税抜金額の合計金額（A)
        $totalTaxExcluded =
            $aggregateDws($taxExcludedPrograms, fn (Expense $fee): int => $fee->taxExcluded)
            + $aggregateLtcs($taxExcludedPrograms, fn (Expense $fee): int => $fee->taxExcluded);

        // 「税込」の自費サービスの税抜金額の合計金額（C)
        $totalTaxExcludedFromTaxIncluded =
            $aggregateDws($taxIncludedPrograms, fn (Expense $fee): int => $fee->taxExcluded)
            + $aggregateLtcs($taxIncludedPrograms, fn (Expense $fee): int => $fee->taxExcluded);

        // 「税込」の自費サービスの税込金額の合計金額（D)
        $totalTaxIncluded =
            $aggregateDws($taxIncludedPrograms, fn (Expense $fee): int => $fee->taxIncluded)
            + $aggregateLtcs($taxIncludedPrograms, fn (Expense $fee): int => $fee->taxIncluded);

        // 非課税の自費サービスの税込金額の合計金額（E)
        $totalTaxExcludedFromUnapplicable =
            $aggregateDws($taxExemptedPrograms, fn (Expense $fee): int => $fee->taxIncluded)
            + $aggregateLtcs($taxExemptedPrograms, fn (Expense $fee): int => $fee->taxIncluded);

        // 税込金額
        $copayWithTax = Math::ceil($totalTaxExcluded * (100 + $taxRate->value()) / 100)
            + $totalTaxIncluded
            + $totalTaxExcludedFromUnapplicable;

        // 税抜金額
        $copayWithoutTax = $totalTaxExcluded
            + $totalTaxExcludedFromTaxIncluded
            + $totalTaxExcludedFromUnapplicable;

        return $copayWithTax === 0
            ? Option::none()
            : Option::from(UserBillingOtherItem::create([
                'score' => 0,
                'unitCost' => Decimal::zero(),
                'subtotalCost' => 0,
                'tax' => $taxRate,
                'medicalDeductionAmount' => 0,
                'totalAmount' => $copayWithoutTax,
                'copayWithoutTax' => $copayWithoutTax,
                'copayWithTax' => $copayWithTax,
            ]));
    }

    /**
     * 自費サービス情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$ids
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram[]&\ScalikePHP\Seq
     */
    private function lookupOwnExpenseProgram(Context $context, int ...$ids): Seq
    {
        return $this->lookupOwnExpenseProgramUseCase->handle(
            $context,
            Permission::createUserBillings(),
            ...$ids
        );
    }

    /**
     * 消費税率を返す.
     *
     * @param \Domain\Common\TaxCategory $taxCategory
     * @return \Domain\Common\ConsumptionTaxRate
     */
    private function getConsumptionTaxRate(TaxCategory $taxCategory): ConsumptionTaxRate
    {
        return match ($taxCategory) {
            TaxCategory::consumptionTax() => ConsumptionTaxRate::ten(),
            TaxCategory::reducedConsumptionTax() => ConsumptionTaxRate::eight(),
            default => ConsumptionTaxRate::zero(),
        };
    }
}
