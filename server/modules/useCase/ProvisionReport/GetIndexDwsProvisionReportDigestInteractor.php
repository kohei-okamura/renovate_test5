<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationRepository;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportDigest;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\User\User;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\BuildFinderResultHolder;
use UseCase\User\FindUserUseCase;

/**
 * 障害福祉サービス：予実：概要一覧取得ユースケース実装.
 */
final class GetIndexDwsProvisionReportDigestInteractor implements GetIndexDwsProvisionReportDigestUseCase
{
    use BuildFinderResultHolder;

    private FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase;
    private FindUserUseCase $findUserUseCase;
    private DwsCertificationRepository $dwsCertificationRepository;

    /**
     * Constructor.
     *
     * @param \UseCase\ProvisionReport\FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase
     * @param \UseCase\User\FindUserUseCase $findUserUseCase
     * @param \Domain\DwsCertification\DwsCertificationRepository $dwsCertificationRepository
     */
    public function __construct(
        FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase,
        FindUserUseCase $findUserUseCase,
        DwsCertificationRepository $dwsCertificationRepository
    ) {
        $this->findDwsProvisionReportUseCase = $findDwsProvisionReportUseCase;
        $this->findUserUseCase = $findUserUseCase;
        $this->dwsCertificationRepository = $dwsCertificationRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult
    {
        $users = $this->users($context, $filterParams);
        $userIds = $users->map(fn (User $x): int => $x->id)->toArray();
        $providedIn = $filterParams['providedIn'];
        $dwsCertifications = $this->dwsCertifications($users, $providedIn);
        $dwsProvisionReports = $this->dwsProvisionReports($context, (int)$filterParams['officeId'], $userIds, $providedIn);
        $dwsProvisionReportDigests = $this->createDwsProvisionReportDigests(
            $users,
            $dwsCertifications,
            $dwsProvisionReports,
            Option::fromArray($filterParams, 'status')
        );

        return $this->buildFinderResult(
            $dwsProvisionReportDigests,
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
                Permission::listDwsProvisionReports(),
                [
                    'isContractingWith' => [
                        'officeId' => $filterParams['officeId'],
                        'date' => $filterParams['providedIn'],
                        'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                    ],
                ] + $filter,
                ['all' => true],
            )
            ->list
            ->sortBy(fn (User $user): string => $user->name->phoneticDisplayName);
    }

    /**
     * 障害福祉サービス受給者証を取得する.
     *
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\DwsCertification\DwsCertification[]&\ScalikePHP\Map
     */
    private function dwsCertifications(Seq $users, Carbon $providedIn): Map
    {
        $certificationMap = $this->dwsCertificationRepository
            ->lookupByUserId(...$users->map(fn (User $x): int => $x->id)->toArray());

        return $users->flatMap(function (User $x) use ($providedIn, $certificationMap): Option {
            /** @var \Domain\DwsCertification\DwsCertification[]|\ScalikePHP\Seq $certifications */
            $certifications = $certificationMap->get($x->id)
                ->getOrElseValue(Seq::empty());

            return $certifications->find(function (DwsCertification $certification) use ($providedIn): bool {
                return $certification->status === DwsCertificationStatus::approved()
                        && $certification->effectivatedOn <= $providedIn;
            });
        })
            ->toMap('userId');
    }

    /**
     * 障害福祉サービス：予実を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param array $userIds
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Map
     */
    private function dwsProvisionReports(Context $context, int $officeId, array $userIds, Carbon $providedIn): Map
    {
        return $this->findDwsProvisionReportUseCase
            ->handle(
                $context,
                Permission::listDwsProvisionReports(),
                ['officeId' => $officeId, 'userIds' => $userIds, 'providedIn' => $providedIn],
                ['all' => true]
            )
            ->list
            ->toMap('userId');
    }

    /**
     * 障害福祉サービス：予実：概要を生成する.
     *
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param \Domain\DwsCertification\DwsCertification[]&\ScalikePHP\Map $dwsCertifications
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Map $dwsProvisionReports
     * @param \Domain\ProvisionReport\DwsProvisionReportStatus[]&\ScalikePHP\Option $status
     * @return \Domain\ProvisionReport\DwsProvisionReportDigest[]&\ScalikePHP\Seq
     */
    private function createDwsProvisionReportDigests(
        Seq $users,
        Map $dwsCertifications,
        Map $dwsProvisionReports,
        Option $status
    ): Seq {
        $digests = $users->flatMap(
            function (User $user) use ($dwsCertifications, $dwsProvisionReports): Option {
                $dwsNumberOption = $dwsCertifications->get($user->id);
                if ($dwsNumberOption->isEmpty()) {
                    return Option::none();
                }

                $dwsNumber = $dwsNumberOption->map(fn (DwsCertification $x): string => $x->dwsNumber)
                    ->getOrElseValue('');
                $status = $dwsProvisionReports->get($user->id)
                    ->map(fn (DwsProvisionReport $x): DwsProvisionReportStatus => $x->status)
                    ->getOrElseValue(DwsProvisionReportStatus::notCreated()); // まだ入力していない場合
                return Option::from(DwsProvisionReportDigest::create([
                    'userId' => $user->id,
                    'name' => $user->name,
                    'dwsNumber' => $dwsNumber,
                    'isEnabled' => $user->isEnabled,
                    'status' => $status,
                ]));
            }
        );
        return $status
            ->map(function (DwsProvisionReportStatus $x) use ($digests): Seq {
                return $digests->filter(fn (DwsProvisionReportDigest $y): bool => $y->status === $x);
            })
            ->getOrElseValue($digests);
    }
}
