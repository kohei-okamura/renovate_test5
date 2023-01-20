<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindHomeHelpServiceCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\FindHomeVisitLongTermCareCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\FindVisitingCareForPwsdCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Office\GetOfficeInfoInteractor;

/**
 * {@link \UseCase\Office\GetOfficeInfoInteractor} Test.
 */
class GetOfficeInfoInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FindHomeHelpServiceCalcSpecUseCaseMixin;
    use FindHomeVisitLongTermCareCalcSpecUseCaseMixin;
    use FindVisitingCareForPwsdCalcSpecUseCaseMixin;
    use LookupOfficeGroupUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private GetOfficeInfoInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetOfficeInfoInteractorTest $self): void {
            $self->context
                ->allows('isAuthorizedTo')
                ->andReturn(true)
                ->byDefault();
            $self->findHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->homeHelpServiceCalcSpecs[0]), Pagination::create()))
                ->byDefault();
            $self->findHomeVisitLongTermCareCalcSpecUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->homeVisitLongTermCareCalcSpecs[0]), Pagination::create()))
                ->byDefault();
            $self->findVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->visitingCareForPwsdCalcSpecs[0]), Pagination::create()))
                ->byDefault();
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->officeGroups[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->interactor = app(GetOfficeInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array have Entity elements', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->offices[0]->id);

            $this->assertSame(
                [
                    'office',
                    'officeGroup',
                    'homeHelpServiceCalcSpecs',
                    'homeVisitLongTermCareCalcSpecs',
                    'visitingCareForPwsdCalcSpecs',
                ],
                array_keys($actual)
            );
            $this->assertModelStrictEquals($this->examples->offices[0], $actual['office']);
            $this->assertModelStrictEquals($this->examples->officeGroups[0], $actual['officeGroup']);
            $this->assertArrayStrictEquals([$this->examples->homeHelpServiceCalcSpecs[0]], $actual['homeHelpServiceCalcSpecs']);
            $this->assertArrayStrictEquals([$this->examples->homeVisitLongTermCareCalcSpecs[0]], $actual['homeVisitLongTermCareCalcSpecs']);
            $this->assertArrayStrictEquals([$this->examples->visitingCareForPwsdCalcSpecs[0]], $actual['visitingCareForPwsdCalcSpecs']);
        });
        $this->should('include Office entity', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->offices[0]->id);

            $this->assertModelStrictEquals($this->examples->offices[0], $actual['office']);
        });
        $this->should('include OfficeGroup entity', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->offices[0]->id);

            $this->assertModelStrictEquals($this->examples->officeGroups[0], $actual['officeGroup']);
        });
        $this->should('use LookupOfficeUseCase', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::viewInternalOffices(), Permission::viewExternalOffices()],
                    $this->examples->offices[0]->id
                )
                ->andReturn(Seq::from($this->examples->offices[1]));

            $this->interactor->handle($this->context, $this->examples->offices[0]->id);
        });
        $this->should('use LookupOfficeGroupUseCase', function (): void {
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->offices[0]->officeGroupId)
                ->andReturn(Seq::from($this->examples->officeGroups[2]));

            $this->interactor->handle($this->context, $this->examples->offices[0]->id);
        });
        $this->should('throw Exception when LookupOfficeGroup return empty', function (): void {
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(
                RuntimeException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->offices[0]->id);
                }
            );
        });
        $this->should('use FindHomeHelpServiceCalcSpecUseCase', function (): void {
            $this->findHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewInternalOffices(),
                    equalTo(['officeId' => $this->examples->offices[0]->id]),
                    equalTo(['all' => true])
                )
                ->andReturn(
                    FinderResult::from(
                        Seq::from($this->examples->homeHelpServiceCalcSpecs[0]),
                        Pagination::create()
                    )
                );

            $this->interactor->handle($this->context, $this->examples->offices[0]->id);
        });
        $this->should('use FindHomeVisitLongTermCareCalcSpecUseCase', function (): void {
            $this->findHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewInternalOffices(),
                    equalTo(['officeId' => $this->examples->offices[0]->id]),
                    equalTo(['all' => true])
                )
                ->andReturn(
                    FinderResult::from(
                        Seq::from($this->examples->homeVisitLongTermCareCalcSpecs[0]),
                        Pagination::create()
                    )
                );

            $this->interactor->handle($this->context, $this->examples->offices[0]->id);
        });
        $this->should('use FindVisitingCareForPwsdCalcSpecUseCase', function (): void {
            $this->findVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewInternalOffices(),
                    equalTo(['officeId' => $this->examples->offices[0]->id]),
                    equalTo(['all' => true])
                )
                ->andReturn(
                    FinderResult::from(
                        Seq::from($this->examples->visitingCareForPwsdCalcSpecs[0]),
                        Pagination::create()
                    )
                );

            $this->interactor->handle($this->context, $this->examples->offices[0]->id);
        });
        $this->should('throw NotFoundException when UseCase return empty', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::viewInternalOffices(), Permission::viewExternalOffices()],
                    $this->examples->offices[0]->id
                )
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->offices[0]->id);
                }
            );
        });
        $this->should('return office info with empty calc specs if the staff does not have Permission::viewInternalOffices', function (): void {
            $this->context
                ->expects('isAuthorizedTo')
                ->with(Permission::viewInternalOffices())
                ->andReturn(false);
            $actual = $this->interactor->handle($this->context, $this->examples->offices[0]->id);

            $this->assertSame(
                [
                    'office',
                    'officeGroup',
                    'homeHelpServiceCalcSpecs',
                    'homeVisitLongTermCareCalcSpecs',
                    'visitingCareForPwsdCalcSpecs',
                ],
                array_keys($actual)
            );
            $this->assertModelStrictEquals($this->examples->offices[0], $actual['office']);
            $this->assertModelStrictEquals($this->examples->officeGroups[0], $actual['officeGroup']);
            $this->assertArrayStrictEquals([], $actual['homeHelpServiceCalcSpecs']);
            $this->assertArrayStrictEquals([], $actual['homeVisitLongTermCareCalcSpecs']);
            $this->assertArrayStrictEquals([], $actual['visitingCareForPwsdCalcSpecs']);
        });
    }
}
