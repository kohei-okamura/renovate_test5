<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingSource;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\User\User;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase;
use UseCase\User\IdentifyUserDwsCalcSpecUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 障害福祉サービス：サービス詳細一覧組み立てユースケース実装.
 */
final class BuildDwsBillingServiceDetailListInteractor implements BuildDwsBillingServiceDetailListUseCase
{
    /**
     * {@link \UseCase\Billing\BuildDwsBillingServiceDetailListInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsHomeHelpServiceServiceDetailListUseCase $buildHomeHelpServiceServiceDetailListUseCase
     * @param \UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListUseCase $buildVisitingCareForPwsdServiceDetailListUseCase
     * @param \UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase $identifyHomeHelpServiceCalcSpecUseCase
     * @param \UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase $identifyVisitingCareForPwsdCalcSpecUseCase
     * @param \UseCase\User\IdentifyUserDwsCalcSpecUseCase $identifyUserDwsCalcSpecUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     */
    public function __construct(
        private BuildDwsHomeHelpServiceServiceDetailListUseCase $buildHomeHelpServiceServiceDetailListUseCase,
        private BuildDwsVisitingCareForPwsdServiceDetailListUseCase $buildVisitingCareForPwsdServiceDetailListUseCase,
        private IdentifyHomeHelpServiceCalcSpecUseCase $identifyHomeHelpServiceCalcSpecUseCase,
        private IdentifyVisitingCareForPwsdCalcSpecUseCase $identifyVisitingCareForPwsdCalcSpecUseCase,
        private IdentifyUserDwsCalcSpecUseCase $identifyUserDwsCalcSpecUseCase,
        private LookupUserUseCase $lookupUserUseCase,
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Office $office, Carbon $providedIn, Seq $sources): Seq
    {
        $xs = Seq::from(
            ...$this->buildHomeHelpServiceServiceDetailList($context, $office, $providedIn, $sources),
            ...$this->buildVisitingCareForPwsdServiceDetailList($context, $office, $providedIn, $sources),
        );
        return $xs
            ->groupBy(fn (array $data): string => $data['cityCode'])
            ->mapValues(function (Seq $seq): array {
                assert($seq->nonEmpty());
                $head = $seq->head();
                return [
                    'cityCode' => $head['cityCode'],
                    'cityName' => $head['cityName'],
                    'details' => [...$seq->flatMap(fn (array $data): iterable => $data['list'])],
                ];
            })
            ->values()
            ->computed();
    }

    /**
     * 居宅介護のサービス詳細一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsBillingSource[]|\ScalikePHP\Seq $sources
     * @throws \Throwable
     * @return array[]|iterable
     */
    private function buildHomeHelpServiceServiceDetailList(
        Context $context,
        Office $office,
        Carbon $providedIn,
        Seq $sources
    ): iterable {
        $specOption = $this->identifyHomeHelpServiceCalcSpecUseCase
            ->handle($context, $office, $providedIn);
        $lastOfMonth = $providedIn->lastOfMonth();
        $userIds = $sources->map(fn (DwsBillingSource $x): int => $x->provisionReport->userId)->toArray();
        $users = $this->lookupUser($context, $userIds);
        foreach ($sources as $source) {
            $certification = $source->certification;
            $provisionReport = $source->provisionReport;
            $user = $users
                ->find(fn (User $x): bool => $x->id === $provisionReport->userId)
                ->getOrElse(function (): never {
                    throw new LogicException('user Not Found');
                });
            $userSpecOption = $this->identifyUserDwsCalcSpec($context, $user, $lastOfMonth);
            $previousProvisionReport = $source->previousProvisionReport;
            yield [
                'cityCode' => $certification->cityCode,
                'cityName' => $certification->cityName,
                'list' => $this->buildHomeHelpServiceServiceDetailListUseCase->handle(
                    $context,
                    $providedIn,
                    $specOption,
                    $userSpecOption,
                    $certification,
                    $provisionReport,
                    $previousProvisionReport
                ),
            ];
        }
    }

    /**
     * 重度訪問介護のサービス詳細一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsBillingSource[]|\ScalikePHP\Seq $sources
     * @throws \Throwable
     * @return array[]|iterable
     */
    private function buildVisitingCareForPwsdServiceDetailList(
        Context $context,
        Office $office,
        Carbon $providedIn,
        Seq $sources
    ): iterable {
        $specOption = $this->identifyVisitingCareForPwsdCalcSpecUseCase
            ->handle($context, $office, $providedIn);
        $lastOfMonth = $providedIn->lastOfMonth();
        $userIds = $sources->map(fn (DwsBillingSource $x): int => $x->provisionReport->userId)->toArray();
        $users = $this->lookUpUser($context, $userIds);
        foreach ($sources as $source) {
            $certification = $source->certification;
            $provisionReport = $source->provisionReport;
            $user = $users
                ->find(fn (User $x): bool => $x->id === $provisionReport->userId)
                ->getOrElse(function (): never {
                    throw new LogicException('user Not Found');
                });
            $userSpecOption = $this->identifyUserDwsCalcSpec($context, $user, $lastOfMonth);
            yield [
                'cityCode' => $certification->cityCode,
                'cityName' => $certification->cityName,
                'list' => $this->buildVisitingCareForPwsdServiceDetailListUseCase->handle(
                    $context,
                    $providedIn,
                    $specOption,
                    $userSpecOption,
                    $certification,
                    $provisionReport
                ),
            ];
        }
    }

    /**
     * 障害福祉サービス：利用者別算定情報を特定する
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\User\UserDwsCalcSpec[]&\ScalikePHP\Option
     */
    private function identifyUserDwsCalcSpec(Context $context, User $user, Carbon $targetDate): Option
    {
        return $this->identifyUserDwsCalcSpecUseCase->handle($context, $user, $targetDate);
    }

    /**
     * 利用者を取得する.
     *
     * @param Context $context
     * @param array $userIds
     * @return \Domain\User\User[]&\ScalikePHP\Seq
     */
    private function lookUpUser(Context $context, array $userIds): Seq
    {
        $users = $this->lookupUserUseCase
            ->handle($context, Permission::createBillings(), ...$userIds);
        if ($users->isEmpty()) {
            $x = implode(',', $userIds);
            throw new NotFoundException("User ({$x}) not found");
        }
        return $users;
    }
}
