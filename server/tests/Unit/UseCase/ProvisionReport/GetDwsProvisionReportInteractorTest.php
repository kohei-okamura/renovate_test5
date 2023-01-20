<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\GetDwsProvisionReportInteractor;

/**
 * {@link \UseCase\ProvisionReport\GetDwsProvisionReportInteractor} のテスト.
 */
final class GetDwsProvisionReportInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FindDwsProvisionReportUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private GetDwsProvisionReportInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->findDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->dwsProvisionReports[0]), Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetDwsProvisionReportInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return option of ProvisionReport', function (): void {
            $option = $this->interactor
                ->handle(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    Carbon::create(2021, 3)
                );
            $this->assertInstanceOf(Option::class, $option);
            $this->assertNotEmpty($option);
            $this->assertModelStrictEquals(
                $this->examples->dwsProvisionReports[0],
                $option->head()
            );
        });
        $this->should('use IdentifyUseCase with specified parameters', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->offices[1]->id,
                    $this->examples->users[2]->id,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::create(2021, 3)->endOfMonth())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));

            $this->interactor
                ->handle(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->offices[1]->id,
                    $this->examples->users[2]->id,
                    Carbon::create(2021, 3)
                );
        });
        $this->should('use FindUseCase with specified parameters', function (): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    equalTo([
                        'officeId' => $this->examples->offices[2]->id,
                        'userId' => $this->examples->users[1]->id,
                        'providedIn' => Carbon::create('2021', 3),
                    ]),
                    equalTo(['all' => true])
                )
                ->andReturn(FinderResult::from(Seq::from($this->examples->dwsProvisionReports[0]), Pagination::create()));

            $this->interactor
                ->handle(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->offices[2]->id,
                    $this->examples->users[1]->id,
                    Carbon::create(2021, 3)
                );
        });
        $this->should('throw NotFoundException when Contract is not identified', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        Permission::updateDwsProvisionReports(),
                        $this->examples->offices[2]->id,
                        $this->examples->users[1]->id,
                        Carbon::create(2021, 3)
                    );
                }
            );
        });
    }
}
