<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeGroupRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Office\LookupOfficeGroupInteractor;

/**
 * LookupOfficeGroupInteractor のテスト.
 */
final class LookupOfficeGroupInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OfficeGroupRepositoryMixin;
    use UnitSupport;

    private LookupOfficeGroupInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupOfficeGroupInteractorTest $self): void {
            $self->context->allows('organization')->andReturn($self->examples->organizations[0]);
            $self->interactor = app(LookupOfficeGroupInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of officeGroup', function (): void {
            $this->officeGroupRepository
                ->expects('lookup')
                ->with($this->examples->officeGroups[0]->id)
                ->andReturn(Seq::from($this->examples->officeGroups[0]));

            $actual = $this->interactor->handle($this->context, $this->examples->officeGroups[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->officeGroups[0], $actual->head());
        });

        $this->should('return empty seq when different organizationId given', function (): void {
            $officeGroup = $this->examples->officeGroups[0]->copy(['organizationId' => self::NOT_EXISTING_ID]);
            $this->officeGroupRepository
                ->allows('lookup')
                ->andReturn(Seq::from($officeGroup));

            $actual = $this->interactor->handle($this->context, $this->examples->officeGroups[0]->id);
            $this->assertCount(0, $actual);
        });
    }
}
