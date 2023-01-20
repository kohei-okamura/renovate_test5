<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\EditUserLocationJob;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Context\Context;
use Domain\User\User;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EditUserUseCaseMixin;
use Tests\Unit\Mixins\LocationResolverMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * EditUserLocationJob のテスト.
 */
final class EditUserLocationJobTest extends Test
{
    use ContextMixin;
    use EditUserUseCaseMixin;
    use ExamplesConsumer;
    use LocationResolverMixin;
    use MockeryMixin;
    use UnitSupport;

    private EditUserLocationJob $job;
    private User $editEntity;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->editEntity = $self->editEntity();
            $self->resolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->users[0]->location))
                ->byDefault();
            $self->editUserUseCase
                ->allows('handle')
                ->andReturn($self->examples->users[0])
                ->byDefault();

            $self->job = new EditUserLocationJob($self->context, $self->editEntity);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('location resolver is called and return a Option of Location', function (): void {
            $this->resolver
                ->expects('resolve')
                ->with($this->context, $this->editEntity->addr)
                ->andReturn(Option::from($this->examples->users[0]->location));

            $this->job->handle($this->resolver, $this->editUserUseCase);
        });
        $this->should('edit the User using UseCase', function (): void {
            $this->editUserUseCase
                ->expects('handle')
                ->withArgs(function (Context $context, int $id, array $values): bool {
                    $this->assertEquals($this->context, $context);
                    $this->assertEquals($this->editEntity->id, $id);
                    $this->assertEquals(['location' => $this->examples->users[0]->location], $values);
                    return true;
                })
                ->andReturn($this->examples->users[0]);

            $this->job->handle($this->resolver, $this->editUserUseCase);
        });
    }

    /**
     * 編集エンティティ.
     *
     * @return \Domain\User\User
     */
    private function editEntity(): User
    {
        $addr = new Addr(
            postcode: '123-4567',
            prefecture: Prefecture::saitama(),
            city: '和光市',
            street: '広沢 XX-XX-XX',
            apartment: 'XXX XXX号室',
        );
        return $this->examples->users[0]->copy(['addr' => $addr, 'updatedAt' => Carbon::now()]);
    }
}
