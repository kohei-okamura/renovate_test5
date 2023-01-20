<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Staff\StaffStatus;
use ScalikePHP\None;
use ScalikePHP\Seq;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffFinderMixin;
use Tests\Unit\Test;
use UseCase\Staff\IdentifyStaffByEmailInteractor;

/**
 * IdentifyStaffByEmailInteractor のテスト.
 */
final class IdentifyStaffByEmailInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StaffFinderMixin;
    use UnitSupport;

    private IdentifyStaffByEmailInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (IdentifyStaffByEmailInteractorTest $self): void {
            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0])
                ->byDefault();
            $self->staffFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->staffs[0]), Pagination::create([])))
                ->byDefault();

            $self->interactor = app(IdentifyStaffByEmailInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a some of Staff', function (): void {
            $email = $this->examples->staffs[0]->email;
            $this->staffFinder
                ->expects('find')
                ->with(
                    [
                        'email' => $email,
                        'organizationId' => $this->context->organization->id,
                        'statuses' => [StaffStatus::provisional(), StaffStatus::active()],
                        'isEnable' => true,
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'id',
                    ]
                )
                ->andReturn(FinderResult::from(Seq::from($this->examples->staffs[0]), Pagination::create([])));
            $option = $this->interactor->handle($this->context, $email);

            $this->assertInstanceOf(Some::class, $option);
            $this->assertModelStrictEquals($this->examples->staffs[0], $option->get());
        });
        $this->should('return None when staff not exists', function (): void {
            $this->staffFinder->allows('find')->andReturn(FinderResult::from([], Pagination::create([])));

            $this->assertInstanceOf(
                None::class,
                $this->interactor->handle($this->context, $this->examples->staffs[0]->email)
            );
        });
    }
}
