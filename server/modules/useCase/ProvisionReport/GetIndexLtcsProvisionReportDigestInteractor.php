<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardRepository;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportDigest;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\User\User;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\BuildFinderResultHolder;
use UseCase\User\FindUserUseCase;

/**
 * 介護保険サービス：予実：概要一覧取得ユースケース実装.
 */
final class GetIndexLtcsProvisionReportDigestInteractor implements GetIndexLtcsProvisionReportDigestUseCase
{
    use BuildFinderResultHolder;

    private FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase;
    private FindUserUseCase $findUserUseCase;
    private LtcsInsCardRepository $ltcsInsCardRepository;

    /**
     * Constructor.
     *
     * @param \UseCase\ProvisionReport\FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase
     * @param \UseCase\User\FindUserUseCase $findUserUseCase
     * @param \Domain\LtcsInsCard\LtcsInsCardRepository $ltcsInsCardRepository
     */
    public function __construct(
        FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase,
        FindUserUseCase $findUserUseCase,
        LtcsInsCardRepository $ltcsInsCardRepository
    ) {
        $this->findLtcsProvisionReportUseCase = $findLtcsProvisionReportUseCase;
        $this->findUserUseCase = $findUserUseCase;
        $this->ltcsInsCardRepository = $ltcsInsCardRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult
    {
        $users = $this->users($context, $filterParams);
        $userIds = $users->map(fn (User $x): int => $x->id)->toArray();
        $providedIn = $filterParams['providedIn'];
        $ltcsInsCards = $this->ltcsInsCards($users, $providedIn);
        $ltcsProvisionReports = $this->ltcsProvisionReports($context, (int)$filterParams['officeId'], $userIds, $providedIn);
        $ltcsProvisionReportDigests = $this->createLtcsProvisionReportDigests(
            $users,
            $ltcsInsCards,
            $ltcsProvisionReports,
            Option::fromArray($filterParams, 'status')
        );

        return $this->buildFinderResult(
            $ltcsProvisionReportDigests,
            $paginationParams,
            $paginationParams['sortBy'] ?? 'name'
        );
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array $filterParams
     * @return \ScalikePHP\Seq
     */
    private function users(Context $context, array $filterParams): Seq
    {
        $filter = array_key_exists('q', $filterParams) ? ['q' => $filterParams['q']] : [];

        return $this->findUserUseCase
            ->handle(
                $context,
                Permission::listLtcsProvisionReports(),
                [
                    'isContractingWith' => [
                        'officeId' => $filterParams['officeId'],
                        'date' => $filterParams['providedIn'],
                        'serviceSegment' => ServiceSegment::longTermCare(),
                    ],
                ] + $filter,
                ['all' => true],
            )
            ->list
            ->sortBy(fn (User $user): string => $user->name->phoneticDisplayName);
    }

    /**
     * 介護保険被保険者証を取得する.
     *
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Map
     */
    private function ltcsInsCards(Seq $users, Carbon $providedIn): Map
    {
        $ltcsInsCardMap = $this->ltcsInsCardRepository
            ->lookupByUserId(...$users->map(fn (User $x): int => $x->id)->toArray());

        return $users->flatMap(
            fn (User $x): Option => $ltcsInsCardMap
                ->getOrElse($x->id, fn (): Seq => Seq::empty())
                ->find(
                    fn (LtcsInsCard $x): bool => $x->status === LtcsInsCardStatus::approved()
                        && $x->effectivatedOn <= $providedIn->endOfMonth()->startOfDay()
                )
        )
            ->toMap('userId');
    }

    /**
     * 介護保険サービス：予実を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param array $userIds
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Map
     */
    private function ltcsProvisionReports(Context $context, int $officeId, array $userIds, Carbon $providedIn): Map
    {
        return $this->findLtcsProvisionReportUseCase
            ->handle(
                $context,
                Permission::listLtcsProvisionReports(),
                ['officeId' => $officeId, 'userIds' => $userIds, 'providedIn' => $providedIn],
                ['all' => true]
            )
            ->list
            ->toMap('userId');
    }

    /**
     * 介護保険サービス：予実：概要を生成する.
     *
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Map $ltcsInsCards
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Map $ltcsProvisionReports
     * @param \Domain\ProvisionReport\LtcsProvisionReportStatus[]&\ScalikePHP\Option $status
     * @return \Domain\ProvisionReport\LtcsProvisionReportDigest[]&\ScalikePHP\Seq
     */
    private function createLtcsProvisionReportDigests(
        Seq $users,
        Map $ltcsInsCards,
        Map $ltcsProvisionReports,
        Option $status
    ): Seq {
        $digests = $users->flatMap(
            function (User $user) use ($ltcsInsCards, $ltcsProvisionReports): Option {
                $insNumberOption = $ltcsInsCards->get($user->id);
                if ($insNumberOption->isEmpty()) {
                    return Option::none();
                }
                $insNumber = $insNumberOption
                    ->map(fn (LtcsInsCard $x): string => $x->insNumber)
                    ->getOrElseValue(''); // 自費で、被保険者証がない場合
                $status = $ltcsProvisionReports->get($user->id)
                    ->map(fn (LtcsProvisionReport $x): LtcsProvisionReportStatus => $x->status)
                    ->getOrElseValue(LtcsProvisionReportStatus::notCreated()); // 自費で、被保険者証がない場合
                return Option::from(LtcsProvisionReportDigest::create([
                    'userId' => $user->id,
                    'name' => $user->name,
                    'insNumber' => $insNumber,
                    'isEnabled' => $user->isEnabled,
                    'status' => $status,
                ]));
            }
        );
        return $status
            ->map(function (LtcsProvisionReportStatus $x) use ($digests): Seq {
                return $digests->filter(fn (LtcsProvisionReportDigest $y): bool => $y->status === $x);
            })
            ->getOrElseValue($digests);
    }
}
