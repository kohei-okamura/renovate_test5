<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\IdentifyDwsProvisionReportInteractor;

/**
 * {@link \UseCase\ProvisionReport\IdentifyDwsProvisionReportInteractor} のテスト.
 */
final class IdentifyDwsProvisionReportInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FindDwsProvisionReportUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private IdentifyDwsProvisionReportInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->findDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->dwsProvisionReports[0]), Pagination::create()))
                ->byDefault();

            $self->interactor = app(IdentifyDwsProvisionReportInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use FindDwsProvisionReportUseCase', function (): void {
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
        $this->should('return option of ProvisionReport', function (): void {
            $option = $this->interactor
                ->handle(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    Carbon::create(2021, 3)
                );
            $this->assertSome($option, function (DwsProvisionReport $actual): void {
                $this->assertModelStrictEquals(
                    $this->examples->dwsProvisionReports[0],
                    $actual
                );
            });
        });
    }
}
