<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\OfficeGroup;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Office\GetIndexOfficeGroupOptionInteractor;

/**
 * {@link \UseCase\Office\GetIndexOfficeGroupOptionInteractor} のテスト.
 */
class GetIndexOfficeGroupOptionInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private GetIndexOfficeGroupOptionInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexOfficeGroupOptionInteractorTest $self): void {
            $self->findOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->officeGroups, Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetIndexOfficeGroupOptionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array of user option', function (): void {
            $expected = Seq::fromArray($this->examples->officeGroups)
                ->map(fn (OfficeGroup $officeGroup): array => [
                    'text' => $officeGroup->name,
                    'value' => $officeGroup->id,
                ]);
            $actual = $this->interactor->handle($this->context, Permission::listOfficeGroups());

            $this->assertSame(
                $expected->toArray(),
                $actual->toArray()
            );
        });
        $this->should('use FindOfficeGroupUseCase', function (): void {
            $this->findOfficeGroupUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->officeGroups, Pagination::create()));

            $this->interactor->handle($this->context, Permission::listOfficeGroups());
        });
    }
}
