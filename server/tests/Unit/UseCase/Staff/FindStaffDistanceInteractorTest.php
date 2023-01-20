<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Common\Distance;
use Domain\Common\Location;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Illuminate\Database\Eloquent\Collection;
use Infrastructure\Staff\Staff as InfrastructureStaff;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffDistanceFinderMixin;
use Tests\Unit\Mixins\UserRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Staff\FindStaffDistanceInteractor;

/**
 * \UseCase\Staff\FindStaffDistanceInteractor のテスト.
 */
final class FindStaffDistanceInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StaffDistanceFinderMixin;
    use UnitSupport;
    use UserRepositoryMixin;

    public const FILTER_PARAMS = [
        'location' => [
            'lat' => 35.684426,
            'lng' => 139.641997,
        ],
        'range' => 10000,
        'sex' => 1,
    ];

    public const PAGINATION_PARAMS = [
        'itemsPerPage' => 10,
        'page' => 2,
    ];

    /**
     * デフォルトのSRIDを定義.
     *
     * @link https://qiita.com/boiledorange73/items/b98d3d1ef3abf7299aba
     */
    protected const DEFAULT_SRID = 4326;

    private FindStaffDistanceInteractor $interactor;

    private Seq $staffDistances;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindStaffDistanceInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::from(Seq::from($self->examples->offices[0])))
                ->byDefault();
            $self->staffDistances = $self->makeStaffDistances($self->examples->users[15]->location);
            $self->interactor = app(FindStaffDistanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find staffs using StaffDistanceFinder', function (): void {
            $paginationParams = self::PAGINATION_PARAMS + ['sortBy' => 'distance'];
            $pageCount = $this->countStaffWithinRange(self::FILTER_PARAMS['range']);
            $pagination = Pagination::create($paginationParams + ['count' => $pageCount]);
            $expected = FinderResult::from(
                Seq::fromArray($this->staffDistances)->filter(fn (
                    Distance $distance
                ) => $distance->destination->organizationId === $this->context->organization->id),
                $pagination,
            );
            $filterParams = [
                'location' => [
                    'lat' => 35.684426,
                    'lng' => 139.641997,
                ],
                'range' => 10000,
                'sex' => 1,
            ];

            $filterParams['location'] = Location::create(self::FILTER_PARAMS['location']);
            $this->staffDistanceFinder
                ->expects('find')
                ->with(
                    ['officeIds' => [$this->examples->offices[0]->id]]
                    + $filterParams
                    + ['organizationId' => $this->context->organization->id],
                    $paginationParams
                )
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listStaffs(), self::FILTER_PARAMS, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [
                'location' => [
                    'lat' => 35.684426,
                    'lng' => 139.641997,
                ],
                'range' => 10000,
                'sex' => 1,
            ];
            $filterParams['location'] = Location::create(self::FILTER_PARAMS['location']);
            $this->staffDistanceFinder
                ->expects('find')
                ->with(
                    ['officeIds' => [$this->examples->offices[0]->id]]
                    + $filterParams
                    + ['organizationId' => $this->context->organization->id],
                    self::PAGINATION_PARAMS + ['sortBy' => 'distance']
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listStaffs(), self::FILTER_PARAMS, self::PAGINATION_PARAMS);
        });
        $this->should('throw InvalidArgumentException when location is not found', function (): void {
            $filterParams = [];
            $this->assertThrows(
                InvalidArgumentException::class,
                function () use ($filterParams): void {
                    $this->interactor->handle($this->context, Permission::listStaffs(), $filterParams, self::PAGINATION_PARAMS);
                }
            );
        });
    }

    /**
     * スタッフの距離情報一覧を生成する.
     *
     * @param \Domain\Common\Location $location
     * @return \ScalikePHP\Seq
     */
    private function makeStaffDistances(Location $location): Seq
    {
        $distances = $this->fetchDistances($location);
        return Seq::fromArray($distances->map(fn (InfrastructureStaff $x): Distance => Distance::create([
            'distance' => $x['distance'],
            'destination' => $x->toDomain(),
        ])));
    }

    /**
     * 距離を取得する.
     *
     * @param \Domain\Common\Location $location
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function fetchDistances(Location $location): Collection
    {
        $query = InfrastructureStaff::query()
            ->with('attr')
            ->join('staff_to_attr', 'staff_to_attr.staff_id', '=', 'staff.id')
            ->join('staff_attr', 'staff_attr.id', '=', 'staff_to_attr.staff_attr_id');
        $bindValues = [sprintf('POINT(%f %f)', $location->lat, $location->lng), self::DEFAULT_SRID];
        $select = 'ST_Distance_Sphere(ST_GeomFromText(?, ?), staff_attr.location) AS distance';
        return $query
            ->select('staff.*')
            ->selectRaw($select)
            ->addBinding($bindValues, 'select')
            ->get();
    }

    /**
     * 指定範囲に存在するスタッフをカウントする.
     *
     * @param int $range
     * @return int
     */
    private function countStaffWithinRange($range): int
    {
        $targets = Seq::fromArray($this->staffDistances)->filter(fn (
            Distance $distance
        ) => $distance->distance <= $range);
        return count($targets);
    }
}
