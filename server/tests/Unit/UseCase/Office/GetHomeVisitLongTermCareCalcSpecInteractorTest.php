<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Carbon;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\IdentifyHomeVisitLongTermCareCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Office\GetHomeVisitLongTermCareCalcSpecInteractor;

/**
 * {@link \UseCase\Office\GetHomeVisitLongTermCareCalcSpecInteractor} のテスト.
 */
final class GetHomeVisitLongTermCareCalcSpecInteractorTest extends Test
{
    use DummyContextMixin;
    use ExamplesConsumer;
    use IdentifyHomeVisitLongTermCareCalcSpecUseCaseMixin;
    use MockeryMixin;
    use LookupOfficeUseCaseMixin;
    use UnitSupport;

    private HomeVisitLongTermCareCalcSpec $calcSpec;
    private Carbon $providedIn;
    private GetHomeVisitLongTermCareCalcSpecInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->calcSpec = $self->examples->homeVisitLongTermCareCalcSpecs[0];
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->identifyHomeVisitLongTermCareCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->calcSpec))
                ->byDefault();

            $self->providedIn = Carbon::parse('2020-10');
            $self->interactor = app(GetHomeVisitLongTermCareCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('return some of HomeVisitLongTermCareCalcSpec', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                [Permission::viewInternalOffices()],
                $this->calcSpec->officeId,
                $this->providedIn
            );
            $this->assertSome($actual, function (HomeVisitLongTermCareCalcSpec $calcSpec): void {
                $this->assertModelStrictEquals($this->calcSpec, $calcSpec);
            });
        });
        $this->should('return none when LookupOfficeUseCase return empty', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $actual = $this->interactor->handle(
                $this->context,
                [Permission::viewInternalOffices()],
                $this->calcSpec->officeId,
                $this->providedIn
            );
            $this->assertNone($actual);
        });
        $this->should('return none when IdentifyHomeVisitLongTermCareCalcSpecUseCase return none', function (): void {
            $this->identifyHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $actual = $this->interactor->handle(
                $this->context,
                [Permission::viewInternalOffices()],
                $this->calcSpec->officeId,
                $this->providedIn
            );
            $this->assertNone($actual);
        });
    }
}
