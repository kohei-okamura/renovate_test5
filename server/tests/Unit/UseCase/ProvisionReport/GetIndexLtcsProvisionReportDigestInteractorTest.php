<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\FinderResult;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportDigest;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\User\User;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\FindUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsInsCardRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\GetIndexLtcsProvisionReportDigestInteractor;

/**
 * {@link \UseCase\ProvisionReport\GetIndexLtcsProvisionReportDigestInteractor} のテスト.
 */
class GetIndexLtcsProvisionReportDigestInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindLtcsProvisionReportUseCaseMixin;
    use FindUserUseCaseMixin;
    use LtcsInsCardRepositoryMixin;
    use MockeryMixin;
    use UnitSupport;

    private array $filterParams;
    private array $paginationParams;
    private FinderResult $userFinderResult;
    private array $userIds;
    private GetIndexLtcsProvisionReportDigestInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexLtcsProvisionReportDigestInteractorTest $self): void {
            $self->userFinderResult = FinderResult::from([$self->examples->users[0], $self->examples->users[4]], Pagination::create());
            $self->userIds = $self->userFinderResult
                ->list
                ->sortBy(fn (User $x): string => $x->name->phoneticDisplayName)
                ->map(fn (User $x): int => $x->id)
                ->toArray();
            $self->findUserUseCase
                ->allows('handle')
                ->andReturn($self->userFinderResult)
                ->byDefault();
            $self->findLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->ltcsProvisionReports, Pagination::create()))
                ->byDefault();
            $self->ltcsInsCardRepository
                ->allows('lookupByUserId')
                ->andReturn(Seq::fromArray($self->examples->ltcsInsCards)->groupBy(fn (LtcsInsCard $x): int => $x->userId))
                ->byDefault();

            $report = $self->examples->ltcsProvisionReports[0];
            $self->filterParams = [
                'officeId' => $report->officeId,
                'providedIn' => $report->providedIn,
                'q' => '',
            ];
            $self->paginationParams = [
                'itemsPerPage' => 10,
                'page' => 1,
            ];
            $self->interactor = app(GetIndexLtcsProvisionReportDigestInteractor::class);
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
                    Permission::listLtcsProvisionReports(),
                    [
                        'isContractingWith' => [
                            'officeId' => $this->filterParams['officeId'],
                            'date' => $this->filterParams['providedIn'],
                            'serviceSegment' => ServiceSegment::longTermCare(),
                        ],
                        'q' => $this->filterParams['q'],
                    ],
                    ['all' => true]
                )
                ->andReturn($this->userFinderResult);

            $this->interactor->handle($this->context, $this->filterParams, $this->paginationParams);
        });
        $this->should('use lookupByUserId on LtcsInsCardRepository', function (): void {
            $this->ltcsInsCardRepository
                ->expects('lookupByUserId')
                ->with(...$this->userIds)
                ->andReturn(Seq::fromArray($this->examples->ltcsInsCards)->groupBy(fn (LtcsInsCard $x): int => $x->userId));

            $this->interactor->handle($this->context, $this->filterParams, $this->paginationParams);
        });
        $this->should('use FindLtcsProvisionReportUseCase', function (): void {
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listLtcsProvisionReports(),
                    [
                        'officeId' => (int)$this->filterParams['officeId'],
                        'userIds' => $this->userIds,
                        'providedIn' => $this->filterParams['providedIn'],
                    ],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->ltcsProvisionReports, Pagination::create()));

            $this->interactor->handle($this->context, $this->filterParams, $this->paginationParams);
        });
        $this->should('return FinderResult of LtcsProvisionReportDigest', function (): void {
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
        $this->should('return not paginated FinderResult of LtcsProvisionReportDigest when all specified', function (): void {
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
        $this->should('return filtered FinderResult of LtcsProvisionReportDigest when the status is specified', function (): void {
            $status = LtcsProvisionReportStatus::fixed();
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
     * @param null|\Domain\ProvisionReport\LtcsProvisionReportStatus $status
     * @return array&\Domain\ProvisionReport\LtcsProvisionReportDigest[]
     */
    private function createReportDigests(?LtcsProvisionReportStatus $status = null): array
    {
        $users = Seq::from($this->examples->users[0], $this->examples->users[4])
            ->sortBy(fn (User $x): string => $x->name->phoneticDisplayName);
        $ltcsInsCards = Seq::fromArray($this->examples->ltcsInsCards)
            ->distinctBy(fn (LtcsInsCard $x) => $x->userId);
        $ltcsProvisionReports = Seq::fromArray($this->examples->ltcsProvisionReports);
        $digests = $users->map(fn (User $user) => LtcsProvisionReportDigest::create([
            'userId' => $user->id,
            'name' => $user->name,
            'insNumber' => $ltcsInsCards->find(fn (LtcsInsCard $x): bool => $x->userId === $user->id)
                ->map(fn (LtcsInsCard $x) => $x->insNumber)
                ->getOrElseValue(''),
            'isEnabled' => $user->isEnabled,
            'status' => $ltcsProvisionReports->find(fn (LtcsProvisionReport $x): bool => $x->userId === $user->id)
                ->map(fn (LtcsProvisionReport $x) => $x->status)
                ->getOrElseValue(LtcsProvisionReportStatus::notCreated()),
        ]));
        return (empty($status)
            ? $digests
            : $digests->filter(fn (LtcsProvisionReportDigest $x): bool => $x->status === $status)
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
