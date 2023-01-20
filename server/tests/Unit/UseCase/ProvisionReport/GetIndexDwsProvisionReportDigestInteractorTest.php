<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\DwsCertification\DwsCertification;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportDigest;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\User\User;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsCertificationRepositoryMixin;
use Tests\Unit\Mixins\FindDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\FindUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\GetIndexDwsProvisionReportDigestInteractor;

/**
 * {@link \UseCase\ProvisionReport\GetIndexDwsProvisionReportDigestInteractor} のテスト.
 */
class GetIndexDwsProvisionReportDigestInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsCertificationRepositoryMixin;
    use ExamplesConsumer;
    use FindDwsProvisionReportUseCaseMixin;
    use FindUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private array $filterParams;
    private array $paginationParams;
    private FinderResult $userFinderResult;
    private array $userIds;
    private GetIndexDwsProvisionReportDigestInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexDwsProvisionReportDigestInteractorTest $self): void {
            $self->userFinderResult = FinderResult::from([$self->examples->users[0], $self->examples->users[1]], Pagination::create());
            $self->userIds = $self->userFinderResult
                ->list
                ->sortBy(fn (User $x): string => $x->name->phoneticDisplayName)
                ->map(fn (User $x): int => $x->id)
                ->toArray();
            $self->findUserUseCase
                ->allows('handle')
                ->andReturn($self->userFinderResult)
                ->byDefault();
            $self->findDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->dwsProvisionReports, Pagination::create()))
                ->byDefault();
            $self->dwsCertificationRepository
                ->allows('lookupByUserId')
                ->andReturn(Seq::fromArray($self->examples->dwsCertifications)->groupBy(fn (DwsCertification $x): int => $x->userId))
                ->byDefault();

            $report = $self->examples->dwsProvisionReports[0];
            $self->filterParams = [
                'officeId' => $report->officeId,
                'providedIn' => $report->providedIn,
                'q' => '',
            ];
            $self->paginationParams = [
                'itemsPerPage' => 10,
                'page' => 1,
            ];
            $self->interactor = app(GetIndexDwsProvisionReportDigestInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use FindUserUseCase', function (): void {
            $this->findUserUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listDwsProvisionReports(),
                    [
                        'isContractingWith' => [
                            'officeId' => $this->filterParams['officeId'],
                            'date' => $this->filterParams['providedIn'],
                            'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                        ],
                        'q' => $this->filterParams['q'],
                    ],
                    ['all' => true]
                )
                ->andReturn($this->userFinderResult);

            $this->interactor->handle($this->context, $this->filterParams, $this->paginationParams);
        });
        $this->should('use lookupByUserId in DwsCertificationRepository class', function (): void {
            $this->dwsCertificationRepository
                ->expects('lookupByUserId')
                ->with(...$this->userIds)
                ->andReturn(Seq::fromArray($this->examples->dwsCertifications)->groupBy(fn (DwsCertification $x): int => $x->userId));

            $this->interactor->handle($this->context, $this->filterParams, $this->paginationParams);
        });
        $this->should('use FindDwsProvisionReportUseCase', function (): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listDwsProvisionReports(),
                    [
                        'officeId' => (int)$this->filterParams['officeId'],
                        'userIds' => $this->userIds,
                        'providedIn' => $this->filterParams['providedIn'],
                    ],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->dwsProvisionReports, Pagination::create()));

            $this->interactor->handle($this->context, $this->filterParams, $this->paginationParams);
        });
        $this->should('return FinderResult of DwsProvisionReportDigest', function (): void {
            $digests = $this->createReportDigests();
            $count = count($digests);
            $itemsPerPage = $this->paginationParams['itemsPerPage'];
            $page = $this->paginationParams['page'];
            $expectedList = array_slice($digests, $itemsPerPage * ($page - 1), $itemsPerPage);
            $expectedPagination = $this->createPagination([
                'count' => $count,
                'desc' => false,
                'itemsPerPage' => $itemsPerPage,
                'page' => $page,
                'pages' => $count === 0 ? 1 : (int)ceil($count / $itemsPerPage),
            ]);
            $this->assertModelStrictEquals(
                FinderResult::from($expectedList, $expectedPagination),
                $this->interactor->handle($this->context, $this->filterParams, $this->paginationParams)
            );
        });
        $this->should('return not paginated FinderResult of DwsProvisionReportDigest when all specified', function (): void {
            $expectedList = $this->createReportDigests();
            $count = count($expectedList);
            $expectedPagination = $this->createPagination([
                'count' => $count,
                'itemsPerPage' => $count,
            ]);
            $this->assertModelStrictEquals(
                FinderResult::from($expectedList, $expectedPagination),
                $this->interactor->handle($this->context, $this->filterParams, ['all' => true])
            );
        });
        $this->should('return filtered FinderResult of DwsProvisionReportDigest when the status is specified', function (): void {
            $status = DwsProvisionReportStatus::fixed();
            $expectedList = $this->createReportDigests($status);
            $count = count($expectedList);
            $expectedPagination = $this->createPagination([
                'count' => $count,
                'itemsPerPage' => $count,
            ]);
            $this->assertModelStrictEquals(
                FinderResult::from($expectedList, $expectedPagination),
                $this->interactor->handle($this->context, ['status' => $status] + $this->filterParams, ['all' => true])
            );
        });
    }

    /**
     * 検証用の介護保険サービス：予実：概要配列を返す.
     *
     * @param null|\Domain\ProvisionReport\DwsProvisionReportStatus $status
     * @return array&\Domain\ProvisionReport\DwsProvisionReportDigest[]
     */
    private function createReportDigests(?DwsProvisionReportStatus $status = null): array
    {
        $dwsCertifications = Seq::fromArray($this->examples->dwsCertifications)
            ->distinctBy(fn (DwsCertification $x) => $x->userId);
        $users = Seq::from($this->examples->users[0], $this->examples->users[1])
            ->filter(fn (User $x): bool => !$dwsCertifications->find(fn (DwsCertification $c): bool => $c->userId === $x->id)->isEmpty())
            ->sortBy(fn (User $x): string => $x->name->phoneticDisplayName);
        $dwsProvisionReports = Seq::fromArray($this->examples->dwsProvisionReports);
        $digests = $users->map(fn (User $user) => DwsProvisionReportDigest::create([
            'userId' => $user->id,
            'name' => $user->name,
            'dwsNumber' => $dwsCertifications->find(fn (DwsCertification $x): bool => $x->userId === $user->id)
                ->map(fn (DwsCertification $x) => $x->dwsNumber)
                ->getOrElseValue(''),
            'isEnabled' => $user->isEnabled,
            'status' => $dwsProvisionReports->find(fn (DwsProvisionReport $x): bool => $x->userId === $user->id)
                ->map(fn (DwsProvisionReport $x) => $x->status)
                ->getOrElseValue(DwsProvisionReportStatus::notCreated()),
        ]));
        return (empty($status)
            ? $digests
            : $digests->filter(fn (DwsProvisionReportDigest $x): bool => $x->status === $status)
        )->toArray();
    }

    /**
     * 検証用のページネーションを返す.
     *
     * @param array $overwrite
     * @return \Domain\Common\Pagination
     */
    private function createPagination(array $overwrite): Pagination
    {
        return Pagination::create($overwrite + [
            'desc' => $paginationParams['desc'] ?? false,
            'page' => 1,
            'pages' => 1,
            'sortBy' => 'name',
        ]);
    }
}
