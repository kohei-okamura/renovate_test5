<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Office\LookupOfficeInteractor;

/**
 * LookupOfficeInteractor のテスト.
 */
final class LookupOfficeInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use UnitSupport;

    private LookupOfficeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupOfficeInteractorTest $self): void {
            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0])
                ->byDefault();
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->interactor = app(LookupOfficeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of office', function (): void {
            $this->officeRepository
                ->expects('lookup')
                ->with($this->examples->offices[0]->id)
                ->andReturn(Seq::from($this->examples->offices[0]));

            $actual = $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->examples->offices[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->offices[0], $actual->head());
        });
        $this->should('use isAccessibleTo Method in Context', function (): void {
            $this->context
                ->expects('isAccessibleTo')
                ->with([Permission::viewInternalOffices()], $this->examples->organizations[0]->id, [$this->examples->offices[0]->id])
                ->andReturn(true);

            $this->interactor
                ->handle($this->context, [Permission::viewInternalOffices()], $this->examples->offices[0]->id);
        });
        $this->should('return empty seq when isAccessibleTo return false', function (): void {
            $this->context
                ->expects('isAccessibleTo')
                ->andReturn(false);
            $actual = $this->interactor->handle($this->context, [Permission::viewInternalOffices()], $this->examples->offices[0]->id);
            $this->assertCount(0, $actual);
        });
    }
}
