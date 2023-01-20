<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindStaffUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Staff\GetIndexStaffOptionInteractor;

/**
 * {@link \UseCase\Staff\GetIndexStaffOptionInteractor} のテスト.
 */
class GetIndexStaffOptionInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindStaffUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private GetIndexStaffOptionInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexStaffOptionInteractorTest $self): void {
            $self->findStaffUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->staffs, Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetIndexStaffOptionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array of staff option', function (): void {
            $expected = Seq::fromArray($this->examples->staffs)
                ->map(fn (Staff $staff): array => [
                    'text' => $staff->name->displayName,
                    'value' => $staff->id,
                ]);
            $actual = $this->interactor->handle($this->context, Permission::listStaffs(), [$this->examples->offices[0]->id]);

            $this->assertSame(
                $expected->toArray(),
                $actual->toArray()
            );
        });
        $this->should('use FindStaffUseCase', function (): void {
            $this->findStaffUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listStaffs(),
                    ['officeIds' => [$this->examples->offices[0]->id]],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->staffs, Pagination::create()));

            $this->interactor->handle($this->context, Permission::listStaffs(), [$this->examples->offices[0]->id]);
        });
    }
}
