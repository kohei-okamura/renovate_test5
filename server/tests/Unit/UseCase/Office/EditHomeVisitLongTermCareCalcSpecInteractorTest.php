<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\HomeVisitLongTermCareCalcSpecRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupHomeVisitLongTermCareCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\EditHomeVisitLongTermCareCalcSpecInteractor;

/**
 * EditHomeVisitLongTermCareCalcSpecInteractor のテスト.
 */
class EditHomeVisitLongTermCareCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindLtcsProvisionReportUseCaseMixin;
    use LoggerMixin;
    use LookupHomeVisitLongTermCareCalcSpecUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use HomeVisitLongTermCareCalcSpecRepositoryMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditHomeVisitLongTermCareCalcSpecInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditHomeVisitLongTermCareCalcSpecInteractorTest $self): void {
            $self->lookupHomeVisitLongTermCareCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->homeVisitLongTermCareCalcSpecs[0]))
                ->byDefault();
            $self->homeVisitLongTermCareCalcSpecRepository
                ->allows('store')
                ->andReturn($self->examples->homeVisitLongTermCareCalcSpecs[0])
                ->byDefault();
            $self->homeVisitLongTermCareCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->byDefault();
            $self->findLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->ltcsProvisionReports[0]), Pagination::create()))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditHomeVisitLongTermCareCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the EditHomeVisitLongTermCareCalcSpec after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $entity = Seq::from($this->examples->homeVisitLongTermCareCalcSpecs[0])
                        ->headOption()->getOrElse(function (): void {
                            throw new NotFoundException("HomeVisitLongTermCareCalcSpec({$this->examples->homeVisitLongTermCareCalcSpecs[0]}) not found");
                        });
                    $this->homeVisitLongTermCareCalcSpecRepository
                        ->expects('store')
                        ->with(
                            equalTo($entity->copy(
                                $this->payload() +
                                [
                                    'version' => $entity->version + 1,
                                    'updatedAt' => Carbon::now(),
                                ]
                            ))
                        )
                        ->andReturn($this->examples->homeVisitLongTermCareCalcSpecs[0]);
                    return $callback();
                });
            $this->interactor->handle(
                $this->context,
                $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId,
                $this->examples->homeVisitLongTermCareCalcSpecs[0]->id,
                $this->payload()
            );
        });
        $this->should('return the HomeVisitLongTermCareCalcSpec', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId,
                $this->examples->homeVisitLongTermCareCalcSpecs[0]->id,
                $this->payload()
            );
            $this->assertEquals($this->examples->homeVisitLongTermCareCalcSpecs[0], $actual['homeVisitLongTermCareCalcSpec']);
            $this->assertEquals(1, $actual['provisionReportCount']);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所算定情報（介保・訪問介護）が更新されました', ['id' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->id] + $context)
                ->andReturnNull();
            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->examples->homeHelpServiceCalcSpecs[0]->id,
                $this->payload()
            );
        });
        $this->should('throw a NotFoundException when the HomeVisitLongTermCareCalcSpecs not exists in db', function (): void {
            $this->lookupHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateInternalOffices()], $this->examples->offices[0]->id, $this->examples->homeVisitLongTermCareCalcSpecs[0]->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->offices[0]->id,
                        $this->examples->homeVisitLongTermCareCalcSpecs[0]->id,
                        $this->payload()
                    );
                }
            );
        });
        $this->should('use findLtcsProvisionReportUseCase', function (): void {
            $filterParams = [
                'officeId' => $this->examples->offices[0]->id,
                'provideInForBetween' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->period,
            ];
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateInternalOffices(),
                    equalTo($filterParams),
                    ['all' => true],
                )
                ->andReturn(FinderResult::from(Seq::from($this->examples->ltcsProvisionReports[0]), Pagination::create()));
            $this->interactor->handle(
                $this->context,
                $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId,
                $this->examples->homeVisitLongTermCareCalcSpecs[0]->id,
                $this->payload()
            );
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function payload(): array
    {
        return Json::decode(Json::encode($this->examples->homeHelpServiceCalcSpecs[0]), true);
    }
}
