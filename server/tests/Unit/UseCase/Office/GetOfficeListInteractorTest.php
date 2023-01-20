<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\Office;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\OfficeFinderMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Office\GetOfficeListInteractor;

/**
 * {@link \UseCase\Office\GetOfficeListInteractor} Test.
 */
class GetOfficeListInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use OfficeFinderMixin;
    use OfficeRepositoryMixin;
    use UnitSupport;

    private GetOfficeListInteractor $interactor;
    /** @var \Domain\Office\Office[]|\ScalikePHP\Seq */
    private $offices;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetOfficeListInteractorTest $self): void {
            $self->offices = Seq::fromArray($self->examples->offices)
                ->filter(fn (Office $x): bool => $x->organizationId === $self->examples->organizations[0]->id);

            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0])
                ->byDefault();
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->officeFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    $self->offices,
                    Pagination::create(),
                ));
            $self->officeRepository
                ->allows('lookup')
                ->andReturnUsing(function (int ...$ids) use ($self): Seq {
                    return Seq::fromArray($self->examples->offices)
                        ->filter(fn (Office $x): bool => in_array($x->id, $ids, true));
                });

            $self->interactor = app(GetOfficeListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a FinderResult of office', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->offices[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->offices[0], $actual->head());
        });
        $this->should('return a seq of all offices when no id passed', function (): void {
            $expected = $this->offices;

            $actual = $this->interactor->handle($this->context);

            $this->assertArrayStrictEquals($expected->toArray(), $actual->toArray());
        });
    }
}
