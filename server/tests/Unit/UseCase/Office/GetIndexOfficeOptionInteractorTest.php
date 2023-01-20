<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Office\OfficeOption;
use Domain\Office\OfficeQualification;
use Domain\Office\Purpose;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeFinderMixin;
use Tests\Unit\Test;
use UseCase\Office\GetIndexOfficeOptionInteractor;

/**
 * {@link \UseCase\Office\GetIndexOfficeOptionInteractor} のテスト.
 */
final class GetIndexOfficeOptionInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindOfficeUseCaseMixin;
    use OfficeFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private GetIndexOfficeOptionInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->findOfficeUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->offices, Pagination::create()))
                ->byDefault();
            $self->officeFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->offices, Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetIndexOfficeOptionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array of office option', function (): void {
            $expected = Seq::fromArray($this->examples->offices)
                ->map(fn (Office $office): OfficeOption => OfficeOption::from($office));

            $actual = $this->interactor->handle(
                $this->context,
                Option::from(Permission::listInternalOffices()),
                Option::from($this->examples->users[0]->id),
                Option::none(),
                Option::none(),
                Seq::empty()
            );

            $this->assertEach(
                function (OfficeOption $expectedOne, OfficeOption $actualOne): void {
                    $this->assertModelStrictEquals($expectedOne, $actualOne);
                },
                [...$expected],
                [...$actual]
            );
        });
        $this->should(
            'use FindOfficeUseCase to find offices when permission is specified',
            function (Permission $permission, array $filterParams): void {
                $this->findOfficeUseCase
                    ->expects('handle')
                    ->with($this->context, [$permission], $filterParams, ['all' => true])
                    ->andReturn(FinderResult::from($this->examples->offices, Pagination::create()));

                $this->interactor->handle(
                    $this->context,
                    Option::from($permission),
                    Option::fromArray($filterParams, 'userId'),
                    Option::fromArray($filterParams, 'purpose'),
                    Option::none(),
                    Seq::fromArray($filterParams['qualifications'] ?? null)
                );
            },
            [
                'examples' => [
                    'without all of userId, purpose, and qualifications' => [
                        Permission::listStaffs(),
                        [],
                    ],
                    'with userId' => [
                        Permission::listUsers(),
                        ['userId' => 1234],
                    ],
                    'with purpose' => [
                        Permission::createDwsContracts(),
                        ['purpose' => Purpose::internal()],
                    ],
                    'with qualifications' => [
                        Permission::createBillings(),
                        ['qualifications' => [OfficeQualification::dwsHomeHelpService(), OfficeQualification::dwsVisitingCareForPwsd()]],
                    ],
                    'with all of userId, purpose, and qualifications' => [
                        Permission::createAttendances(),
                        [
                            'userId' => 1192,
                            'purpose' => Purpose::external(),
                            'qualifications' => [OfficeQualification::dwsHomeHelpService(), OfficeQualification::dwsVisitingCareForPwsd()],
                        ],
                    ],
                ],
            ]
        );
        $this->should('use OfficeFinder to find offices when permission is not specified', function (): void {
            $filterParams = [
                'userId' => 1234,
                'purpose' => Purpose::internal(),
                'qualifications' => [OfficeQualification::dwsHomeHelpService()],
            ];
            $this->officeFinder
                ->expects('find')
                ->with($filterParams, ['all' => true, 'sortBy' => 'name'])
                ->andReturn(FinderResult::from($this->examples->offices, Pagination::create()));

            $this->interactor->handle(
                $this->context,
                Option::none(),
                Option::fromArray($filterParams, 'userId'),
                Option::fromArray($filterParams, 'purpose'),
                Option::none(),
                Seq::fromArray($filterParams['qualifications'])
            );
        });
    }
}
