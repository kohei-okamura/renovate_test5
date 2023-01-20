<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportFinder;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use UseCase\Billing\GetDwsBillingInfoUseCase;
use UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\LookupDwsBillingStatementUseCase;
use UseCase\Billing\LookupDwsBillingUseCase;
use UseCase\Billing\LookupLtcsBillingStatementUseCase;
use UseCase\Billing\LookupLtcsBillingUseCase;
use UseCase\Billing\SimpleLookupDwsBillingStatementUseCase;
use UseCase\Billing\ValidateCopayCoordinationItemUseCase;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;

/**
 * 請求関連用カスタムバリデータ.
 *
 * CustomValidatorからのみuseする
 */
trait BillingValidator
{
    /**
     * 入力値の「障害福祉サービス：請求：サービス種類コード」が障害福祉サービス：請求：明細書に存在していることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsServiceDivisionCodeExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(4, $parameters, 'dws_service_division_code_exists');
        $statementId = (int)Arr::get($this->data, $parameters[0]);
        $billingId = (int)Arr::get($this->data, $parameters[1]);
        $bundleId = (int)Arr::get($this->data, $parameters[2]);
        $permission = Permission::from((string)$parameters[3]);

        if (!is_array($value)) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupDwsBillingStatementUseCase */
        $lookupDwsBillingStatementUseCase = app(LookupDwsBillingStatementUseCase::class);
        /** @var \Domain\Billing\DwsBillingStatement $entity */
        $entity = $lookupDwsBillingStatementUseCase->handle($this->context, $permission, $billingId, $bundleId, $statementId)
            ->headOption()
            ->orNull();
        if ($entity === null) {
            return true;
        }
        $aggregates = Seq::fromArray($entity->aggregates);
        foreach ($value as $input) {
            if (!is_array($input) || !array_key_exists('serviceDivisionCode', $input)) {
                continue;
            }
            if (!DwsServiceDivisionCode::isValid($input['serviceDivisionCode'])) {
                continue;
            }
            $inputServiceCode = DwsServiceDivisionCode::from($input['serviceDivisionCode']);
            if (!$aggregates->exists(
                fn (DwsBillingStatementAggregate $x): bool => $x->serviceDivisionCode === $inputServiceCode
            )) {
                return false;
            }
        }
        return true;
    }

    /**
     * 入力値の「障害福祉サービス：請求：状態」が更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingStatusCanUpdate(string $attribute, $value, array $parameters): bool
    {
        if (!DwsBillingStatus::isValid((int)$value)) {
            return true;
        }
        $updateStatus = DwsBillingStatus::from((int)$value);

        $billingId = (int)Arr::get($this->data, 'id', 0);
        if ($billingId === 0) {
            return true;
        }

        /** @var \UseCase\Billing\GetDwsBillingInfoUseCase $getBillingInfoUseCase */
        $getBillingInfoUseCase = app(GetDwsBillingInfoUseCase::class);
        $info = $getBillingInfoUseCase->handle($this->context, $billingId);
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $info['billing'];
        /** @var array|\Domain\Billing\DwsBillingStatement[] $statements */
        $statements = $info['statements'];
        /** @var array|\Domain\Billing\DwsBillingServiceReport[] $reports */
        $reports = $info['reports'];

        // 請求状態が 「無効以外 => 無効」の場合
        if ($billing->status !== DwsBillingStatus::disabled() && $updateStatus === DwsBillingStatus::disabled()) {
            return true;
        }

        // 請求状態が 「未確定 => 確定済」の場合
        if ($billing->status === DwsBillingStatus::ready() && $updateStatus === DwsBillingStatus::fixed()) {
            $canUpdateForStatements = Seq::from(...$statements)
                ->forAll(fn (DwsBillingStatement $x): bool => $x->status === DwsBillingStatus::fixed());

            $canUpdateForReports = Seq::from(...$reports)
                ->forAll(fn (DwsBillingServiceReport $x): bool => $x->status === DwsBillingStatus::fixed());

            return $canUpdateForStatements && $canUpdateForReports;
        }

        return false;
    }

    /**
     * 入力事業所の指定した年月に、障害福祉サービス：予実が存在していることを検証する.
     * 自社事業所で上限管理を行わない、かつ自費サービスのみを利用している予実は請求対象外のため、存在していないものとみなす
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsProvisionReportExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'dws_provision_report_exists');
        $transactedInArg = Arr::get($this->data, $parameters[0]);
        if (strtotime($transactedInArg) === false) {
            return true; // 日付形式エラーではバリデーションしない
        }
        $transactedIn = Carbon::parse($transactedInArg);
        $fixedAt = CarbonRange::create([
            'start' => $transactedIn->subMonth()->day(11)->startOfDay(),
            'end' => $transactedIn->day(10)->endOfDay(),
        ]);
        $officeId = (int)$value;

        /** @var \Domain\ProvisionReport\DwsProvisionReportFinder $finder */
        $finder = app(DwsProvisionReportFinder::class);

        $filterParams = [
            'officeId' => $officeId,
            'fixedAt' => $fixedAt,
            'status' => DwsProvisionReportStatus::fixed(),
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];

        /** @var \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase */
        $identifyDwsCertificationUseCase = app(IdentifyDwsCertificationUseCase::class);
        $findCopayCoordinationType = function (DwsProvisionReport $provisionReport) use ($identifyDwsCertificationUseCase) {
            $userId = $provisionReport->userId;
            $providedIn = $provisionReport->providedIn;
            $certification = $identifyDwsCertificationUseCase
                ->handle($this->context, $userId, $providedIn)
                ->getOrElseValue(null);
            return $certification->copayCoordination->copayCoordinationType ?? null;
        };

        // 処理対象の予実が含まれているかを検証する
        // 1件でも含まれていれば良いので、exists を使用している
        return $finder
            ->find($filterParams, $paginationParams)
            ->list
            ->exists(function (DwsProvisionReport $x) use ($findCopayCoordinationType) {
                // 自費サービス以外を利用している、もしくは自社事業所で上限管理を行うものが対象
                // 受給者証が特定できなかった場合は「自社事業所で上限管理を行わない」扱いになるが、この時点で特定できないことがおかしいので、
                // ここでは気にしない（必要であれば前段に別のバリデーションを追加する）
                // 余談: 受給者証が特定できないまま後続処理に進んでも算定元データ一覧組み立て時にエラーになる
                return Seq::fromArray($x->results)
                    ->filter(fn (DwsProvisionReportItem $y) => !$y->isOwnExpense())
                    ->nonEmpty() || $findCopayCoordinationType($x) === CopayCoordinationType::internal();
            });
    }

    /**
     * 入力IDのEntityが「上限管理結果」が更新可能であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateCopayCoordinationResultCanUpdate(string $attribute, $value, array $parameters): bool
    {
        $statementId = (int)Arr::get($this->data, $attribute, 0);
        /** @var \Domain\Billing\DwsBillingStatementRepository $repository */
        $repository = app(DwsBillingStatementRepository::class);
        $option = $repository->lookup($statementId)->headOption();
        if ($option->isEmpty()) {
            // IDに対するエンティティが存在しないときはバリデーションしない（OKとする）
            return true;
        }
        return $option->forAll(function (DwsBillingStatement $x): bool {
            return $x->copayCoordinationStatus !== DwsBillingStatementCopayCoordinationStatus::unapplicable()
                && $x->copayCoordinationStatus !== DwsBillingStatementCopayCoordinationStatus::unclaimable();
        });
    }

    /**
     * 「利用者負担上限額管理結果票」が更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingCopayCoordinationCanUpdate(string $attribute, $value, array $parameters): bool
    {
        $bundleId = (int)Arr::get($this->data, 'dwsBillingBundleId', 0);
        $userId = (int)Arr::get($this->data, 'userId', 0);
        /** @var \Domain\Billing\DwsBillingStatementFinder $finder */
        $finder = app(DwsBillingStatementFinder::class);
        return $finder
            ->find(
                ['dwsBillingBundleId' => $bundleId, 'userId' => $userId],
                ['all' => true, 'sortBy' => 'id']
            )
            ->list
            ->headOption()
            ->forAll(
                fn (DwsBillingStatement $x): bool => $x->status !== DwsBillingStatus::fixed()
                    && !$x->copayCoordinationStatus->isCompleted()
            );
    }

    /**
     * 「利用者負担上限額管理結果票 状態」が更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingCopayCoordinationStatusCanUpdateForBillingStatus(string $attribute, $value, array $parameters): bool
    {
        if (!DwsBillingStatus::isValid((int)$value)) {
            return true;
        }
        $updatedStatus = DwsBillingStatus::from($value);

        $billingId = (int)Arr::get($this->data, 'dwsBillingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $bundleId = (int)Arr::get($this->data, 'dwsBillingBundleId', 0);
        if ($bundleId === 0) {
            return true;
        }

        $copayCoordinationId = (int)Arr::get($this->data, 'id', 0);
        if ($copayCoordinationId === 0) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase $lookupCopayCoordinationUseCase */
        $lookupCopayCoordinationUseCase = app(LookupDwsBillingCopayCoordinationUseCase::class);
        /** @var \Domain\Billing\DwsBillingCopayCoordination $current */
        $currentEntity = $lookupCopayCoordinationUseCase
            ->handle($this->context, Permission::updateBillings(), $billingId, $bundleId, $copayCoordinationId)
            ->headOption()
            ->orNull();
        if ($currentEntity === null) {
            return true;
        }

        // 未確定 => 確定済 or 確定済 => 未確定 以外の変更は不可
        if (!(($currentEntity->status === DwsBillingStatus::ready() && $updatedStatus === DwsBillingStatus::fixed())
            || ($currentEntity->status === DwsBillingStatus::fixed() && $updatedStatus === DwsBillingStatus::ready()))) {
            return false;
        }

        /** @var \Domain\Billing\DwsBillingStatementFinder $statementFinder */
        $statementFinder = app(DwsBillingStatementFinder::class);
        // 明細書の状態が「確定済」の場合、利用者負担上限額管理結果票の状態を更新できない
        // 請求の状態が「確定済」の場合も更新できないが、請求の状態が「確定済」で明細書の状態が「未確定」という状態はありえないためチェック不要
        return $statementFinder
            ->find(['dwsBillingBundleId' => $bundleId, 'userId' => $currentEntity->user->userId], ['all' => true, 'sortBy' => 'id'])
            ->list
            ->headOption()
            ->forAll(fn (DwsBillingStatement $x): bool => $x->status !== DwsBillingStatus::fixed());
    }

    /**
     * 入力値の「障害福祉サービス：請求：明細書状態」が一括更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingStatementStatusCanBulkUpdate(string $attribute, $value, array $parameters): bool
    {
        $statusValue = Arr::get($this->data, $parameters[0]);
        if (!DwsBillingStatus::isValid((int)$statusValue)) {
            return true;
        }
        $updatedStatus = DwsBillingStatus::from($statusValue);

        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $ids = $value;
        if (!is_array($ids)) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupDwsBillingUseCase::class);
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $lookupBillingUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        // 状態一括更新APIでは、請求が存在しない場合は 400 にしたいのでエラーとする
        if ($billing === null) {
            return false;
        }

        /** @var \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase $lookupStatementUseCase */
        $lookupStatementUseCase = app(SimpleLookupDwsBillingStatementUseCase::class);
        $statements = $lookupStatementUseCase
            ->handle($this->context, Permission::updateBillings(), ...$ids)
            ->filter(fn (DwsBillingStatement $x): bool => $x->dwsBillingId === $billingId);
        // 状態一括更新APIでは、明細書が存在しない ID が含まれている場合は 400 にしたいのでエラーとする
        if ($statements->size() !== count($ids)) {
            return false;
        }
        return $this->canDwsStatementStatusUpdate($updatedStatus, $billing, $statements);
    }

    /**
     * 入力値の「障害福祉サービス：請求：明細書状態」が更新可能な値であることを検証する（障害福祉サービス：請求：状態）.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingStatementStatusCanUpdateForBillingStatus(string $attribute, $value, array $parameters): bool
    {
        if (!DwsBillingStatus::isValid((int)$value)) {
            return true;
        }
        $updatedStatus = DwsBillingStatus::from($value);

        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $bundleId = (int)Arr::get($this->data, 'billingBundleId', 0);
        if ($bundleId === 0) {
            return true;
        }

        $statementId = (int)Arr::get($this->data, 'id', 0);
        if ($statementId === 0) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupDwsBillingUseCase::class);
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $lookupBillingUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        if ($billing === null) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupStatementUseCase */
        $lookupStatementUseCase = app(LookupDwsBillingStatementUseCase::class);
        $statements = $lookupStatementUseCase
            ->handle($this->context, Permission::updateBillings(), $billingId, $bundleId, $statementId);
        // 状態更新APIでは、明細書が存在しない場合は 404 にしたいのでここではエラーとしない
        if ($statements->isEmpty()) {
            return true;
        }

        return $this->canDwsStatementStatusUpdate($updatedStatus, $billing, $statements);
    }

    /**
     * 入力値の「障害福祉サービス：請求：明細書状態」が更新可能な値であることを検証する（上限管理区分）.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingStatementStatusCanUpdateForCopayCoordinationStatus(string $attribute, $value, array $parameters): bool
    {
        if (!DwsBillingStatus::isValid((int)$value)) {
            return true;
        }

        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $bundleId = (int)Arr::get($this->data, 'billingBundleId', 0);
        if ($bundleId === 0) {
            return true;
        }

        $statementId = (int)Arr::get($this->data, 'id', 0);
        if ($statementId === 0) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupUseCase */
        $lookupUseCase = app(LookupDwsBillingStatementUseCase::class);
        /** @var \Domain\Billing\DwsBillingStatement $originalEntity */
        $originalEntity = $lookupUseCase->handle($this->context, Permission::updateBillings(), $billingId, $bundleId, $statementId)
            ->headOption()
            ->orNull();
        if ($originalEntity === null) {
            return true;
        }

        return $originalEntity->copayCoordinationStatus !== DwsBillingStatementCopayCoordinationStatus::uncreated()
            && $originalEntity->copayCoordinationStatus !== DwsBillingStatementCopayCoordinationStatus::unfilled()
            && $originalEntity->copayCoordinationStatus !== DwsBillingStatementCopayCoordinationStatus::checking();
    }

    /**
     * 入力値の「障害福祉サービス：明細書：上限管理区分」が更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateDwsBillingStatementCopayCoordinationStatusCanUpdate(string $attribute, $value, array $parameters): bool
    {
        if (!$this->validateDwsBillingStatementCopayCoordinationStatus($attribute, $value, $parameters)) {
            return true;
        }

        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $bundleId = (int)Arr::get($this->data, 'billingBundleId', 0);
        if ($bundleId === 0) {
            return true;
        }

        $statementId = (int)Arr::get($this->data, 'id', 0);
        if ($statementId === 0) {
            return true;
        }

        /** @var \UseCase\Billing\LookupDwsBillingStatementUseCase $lookupUseCase */
        $lookupUseCase = app(LookupDwsBillingStatementUseCase::class);
        /** @var \Domain\Billing\DwsBillingStatement $originalEntity */
        $originalEntity = $lookupUseCase->handle($this->context, Permission::updateBillings(), $billingId, $bundleId, $statementId)
            ->headOption()
            ->orNull();
        if ($originalEntity === null) {
            return true;
        }

        return $originalEntity->copayCoordinationStatus === DwsBillingStatementCopayCoordinationStatus::uncreated()
            && DwsBillingStatementCopayCoordinationStatus::from($value) === DwsBillingStatementCopayCoordinationStatus::unclaimable();
    }

    /**
     * 「障害福祉サービス：請求：明細書」が更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsBillingStatementCanUpdate(string $attribute, $value, array $parameters): bool
    {
        $statementId = (int)Arr::get($this->data, $attribute, 0);
        /** @var \Domain\Billing\DwsBillingStatementRepository $repository */
        $repository = app(DwsBillingStatementRepository::class);
        $option = $repository->lookup($statementId)->headOption();
        if ($option->isEmpty()) {
            // IDに対するエンティティが存在しないときはバリデーションしない（OKとする）
            return true;
        }
        foreach ($option as $statement) {
            assert($statement instanceof DwsBillingStatement);
            if ($statement->status === DwsBillingStatus::fixed()) {
                return false;
            }
        }
        return true;
    }

    /**
     * 入力値の「利用者負担上限額管理結果票：明細」と「上限管理結果」の整合性が取れていることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateItemsHaveIntegrityOfResult(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(5, $parameters, 'coordinated_copay_have_integrity_of_result');

        $resultValue = (int)Arr::get($this->data, $parameters[0]);
        // 上限管理結果が不正の場合はこのバリデーションではエラーとしない
        if (!CopayCoordinationResult::isValid($resultValue)) {
            return true;
        }
        $result = CopayCoordinationResult::from($resultValue);
        $userId = (int)Arr::get($this->data, $parameters[1]);
        $dwsBillingId = (int)Arr::get($this->data, $parameters[2]);
        $dwsBillingBundleId = (int)Arr::get($this->data, $parameters[3]);
        $permission = Permission::from((string)$parameters[4]);
        // 利用者負担上限額管理結果票：明細が配列でない場合はこのバリデーションではエラーとしない
        if (!is_array($value)) {
            return true;
        }
        // 利用者負担上限額管理結果票：明細が空の場合ここではエラーとしない
        if (count($value) === 0) {
            return true;
        }

        $items = Seq::fromArray($value);

        /** @var \UseCase\Billing\ValidateCopayCoordinationItemUseCase $validateCopayCoordinationUseCase */
        $validateCopayCoordinationUseCase = app(ValidateCopayCoordinationItemUseCase::class);
        return $validateCopayCoordinationUseCase->handle(
            $this->context,
            $items,
            $result,
            $userId,
            $dwsBillingId,
            $dwsBillingBundleId,
            $permission
        );
    }

    /**
     * 入力値の「合計」が「管理結果後利用者負担額」の合計に等しいことを検証する.
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateEqualTotalCoordinatedCopay(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'total_coordinated_copay');
        $itemsArr = Arr::get($this->data, $parameters[0]);
        // 配列でない場合ここではエラーとしない
        if (!is_array($itemsArr)) {
            return true;
        }
        return $this->equalTotal((int)$value, $itemsArr, 'subtotal.coordinatedCopay');
    }

    /**
     * 入力値の「合計」が「利用者負担額」の合計に等しいことを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateEqualTotalCopay(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'total_copay');
        $itemsArr = Arr::get($this->data, $parameters[0]);
        // 配列でない場合ここではエラーとしない
        if (!is_array($itemsArr)) {
            return true;
        }
        return $this->equalTotal((int)$value, $itemsArr, 'subtotal.copay');
    }

    /**
     * 入力値の「合計」が「総費用額」の合計に等しいことを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateEqualTotalFee(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'total_fee');
        $itemsArr = Arr::get($this->data, $parameters[0]);
        // 配列でない場合ここではエラーとしない
        if (!is_array($itemsArr)) {
            return true;
        }
        return $this->equalTotal((int)$value, $itemsArr, 'subtotal.fee');
    }

    /**
     * 入力値の「介護保険サービス：請求：明細書状態」が一括更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsBillingStatementStatusCanBulkUpdate(string $attribute, $value, array $parameters): bool
    {
        $statusValue = Arr::get($this->data, $parameters[0]);
        if (!LtcsBillingStatus::isValid((int)$statusValue)) {
            return true;
        }
        $updatedStatus = LtcsBillingStatus::from($statusValue);

        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $bundleId = (int)Arr::get($this->data, 'billingBundleId', 0);
        if ($bundleId === 0) {
            return true;
        }

        $ids = $value;
        if (!is_array($ids)) {
            return true;
        }

        /** @var \UseCase\Billing\LookupLtcsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupLtcsBillingUseCase::class);
        /** @var \Domain\Billing\LtcsBilling $billing */
        $billing = $lookupBillingUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        // 状態一括更新APIでは、請求が存在しない場合は 400 にしたいのでエラーとする
        if ($billing === null) {
            return false;
        }

        /** @var \UseCase\Billing\LookupLtcsBillingStatementUseCase $lookupStatementUseCase */
        $lookupStatementUseCase = app(LookupLtcsBillingStatementUseCase::class);
        $statements = $lookupStatementUseCase->handle($this->context, Permission::updateBillings(), $billingId, $bundleId, ...$ids);
        // 状態一括更新APIでは、明細書が存在しない場合は 400 にしたいのでエラーとする
        if ($statements->isEmpty()) {
            return false;
        }

        return $this->canStatementStatusUpdate($updatedStatus, $billing, $statements);
    }

    /**
     * 入力値の「介護保険サービス：請求：明細書状態」が更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsBillingStatementStatusCanUpdate(string $attribute, $value, array $parameters): bool
    {
        if (!LtcsBillingStatus::isValid((int)$value)) {
            return true;
        }
        $updatedStatus = LtcsBillingStatus::from($value);

        $billingId = (int)Arr::get($this->data, 'billingId', 0);
        if ($billingId === 0) {
            return true;
        }

        $bundleId = (int)Arr::get($this->data, 'billingBundleId', 0);
        if ($bundleId === 0) {
            return true;
        }

        $statementId = (int)Arr::get($this->data, 'id', 0);
        if ($statementId === 0) {
            return true;
        }

        /** @var \UseCase\Billing\LookupLtcsBillingUseCase $lookupBillingUseCase */
        $lookupBillingUseCase = app(LookupLtcsBillingUseCase::class);
        /** @var \Domain\Billing\LtcsBilling $billing */
        $billing = $lookupBillingUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        // 状態更新APIでは、請求が存在しない場合は 404 にしたいのでここではエラーとしない
        if ($billing === null) {
            return true;
        }

        /** @var \UseCase\Billing\LookupLtcsBillingStatementUseCase $lookupStatementUseCase */
        $lookupStatementUseCase = app(LookupLtcsBillingStatementUseCase::class);
        $statements = $lookupStatementUseCase->handle($this->context, Permission::updateBillings(), $billingId, $bundleId, $statementId);
        // 状態更新APIでは、明細書が存在しない場合は 404 にしたいのでここではエラーとしない
        if ($statements->isEmpty()) {
            return true;
        }

        return $this->canStatementStatusUpdate($updatedStatus, $billing, $statements);
    }

    /**
     * 入力値の「介護保険サービス：請求：状態」が更新可能な値であることを検証する.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateLtcsBillingStatusCanUpdate(string $attribute, $value, array $parameters): bool
    {
        if (!LtcsBillingStatus::isValid((int)$value)) {
            return true;
        }
        $updatedStatus = LtcsBillingStatus::from((int)$value);

        $billingId = (int)Arr::get($this->data, 'id', 0);
        if ($billingId === 0) {
            return true;
        }

        /** @var \UseCase\Billing\LookupLtcsBillingUseCase $lookupUseCase */
        $lookupUseCase = app(LookupLtcsBillingUseCase::class);
        /** @var null|\Domain\Billing\LtcsBilling $originalEntity */
        $originalEntity = $lookupUseCase->handle($this->context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->orNull();
        if ($originalEntity === null) {
            return true;
        }

        return ($originalEntity->status === LtcsBillingStatus::ready() && $updatedStatus === LtcsBillingStatus::fixed())
            || ($updatedStatus === LtcsBillingStatus::disabled());
    }

    /**
     * 「介護保険サービス：請求：明細書」の状態が更新可能であるか判定する.
     *
     * @param \Domain\Billing\LtcsBillingStatus $updatedStatus
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq $statements
     * @return bool
     */
    private function canStatementStatusUpdate(LtcsBillingStatus $updatedStatus, LtcsBilling $billing, Seq $statements): bool
    {
        // 請求の状態が「確定済」の場合、明細書の状態を更新できない
        if ($billing->status === LtcsBillingStatus::fixed()) {
            return false;
        }

        return $statements->forAll(
            fn (LtcsBillingStatement $x): bool => (
                $x->status === LtcsBillingStatus::ready() && $updatedStatus === LtcsBillingStatus::fixed()
            ) || (
                $x->status === LtcsBillingStatus::fixed() && $updatedStatus === LtcsBillingStatus::ready()
            )
        );
    }

    /**
     * 「障害福祉サービス：請求：明細書」の状態が更新可能であるか判定する.
     *
     * @param \Domain\Billing\DwsBillingStatus $updatedStatus
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements
     * @return bool
     */
    private function canDwsStatementStatusUpdate(DwsBillingStatus $updatedStatus, DwsBilling $billing, Seq $statements): bool
    {
        // 請求の状態が「確定済」の場合、明細書の状態を更新できない
        if ($billing->status === DwsBillingStatus::fixed()) {
            return false;
        }

        return $statements->forAll(
            fn (DwsBillingStatement $x): bool => (
                $x->status === DwsBillingStatus::ready() && $updatedStatus === DwsBillingStatus::fixed()
            ) || (
                $x->status === DwsBillingStatus::fixed() && $updatedStatus === DwsBillingStatus::ready()
            )
        );
    }

    /**
     * 「合計」が指定された属性を合計した値に等しいか判定する.
     *
     * @param int $total
     * @param array $itemsArr
     * @param string $attr
     * @return bool
     */
    private function equalTotal(int $total, array $itemsArr, string $attr): bool
    {
        $items = Seq::fromArray($itemsArr);
        // $attr が不正の場合ここではエラーとしない
        if ($items->exists(fn (array $item) => !is_int(Arr::get($item, $attr)))) {
            return true;
        }
        return (int)$total === Seq::fromArray($items)
            ->map(fn (array $item): int => (int)Arr::get($item, $attr))
            ->sum();
    }
}
