<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Staff;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Staff\Invitation;
use Infrastructure\Staff\InvitationRepositoryEloquentImpl;
use ScalikePHP\None;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Staff\InvitationRepositoryEloquentImpl} のテスト.
 */
class InvitationRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private InvitationRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (InvitationRepositoryEloquentImplTest $self): void {
            $self->repository = app(InvitationRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_transactionManager(): void
    {
        $this->should('return a class name of DatabaseTransactionManager', function (): void {
            $this->assertSame(PermanentDatabaseTransactionManager::class, $this->repository->transactionManager());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $expected = $this->examples->invitations[0];
            $actual = $this->repository->lookup($this->examples->invitations[0]->id);
            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals(
                $expected,
                $actual->head()
            );
        });
        $this->should('return empty seq when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store(): void
    {
        $this->should('add the entity', function (): void {
            $entity = Invitation::create($this->attrs());
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newEmail = 'new@example.com';
            $this->assertNotEquals($newEmail, $this->examples->invitations[0]->email);
            $invitation = $this->examples->invitations[0]->copy(['email' => $newEmail]);
            $this->repository->store($invitation);
            $actual = $this->repository->lookup($this->examples->invitations[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $invitation,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = Invitation::create($this->attrs());
            $stored = $this->repository->store($entity);

            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->invitations[0]->id, $this->examples->invitations[1]->id);
            $invitation0 = $this->repository->lookup($this->examples->invitations[0]->id);
            $this->assertCount(0, $invitation0);
            $invitation1 = $this->repository->lookup($this->examples->invitations[1]->id);
            $this->assertCount(0, $invitation1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->invitations[0]->id);
            $invitation0 = $this->repository->lookup($this->examples->invitations[0]->id);
            $this->assertCount(0, $invitation0);
            $invitation1 = $this->repository->lookup($this->examples->invitations[1]->id);
            $invitation2 = $this->repository->lookup($this->examples->invitations[2]->id);
            $this->assertCount(1, $invitation1);
            $this->assertModelStrictEquals($this->examples->invitations[1], $invitation1->head());
            $this->assertCount(1, $invitation2);
            $this->assertModelStrictEquals($this->examples->invitations[2], $invitation2->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupOptionByToken(): void
    {
        $this->should('return some entity when the token exists in db', function (): void {
            $x = $this->repository->lookupOptionByToken($this->examples->invitations[0]->token);

            $this->assertInstanceOf(Some::class, $x);
            $this->assertEquals($this->examples->invitations[0]->id, $x->get()->id);
        });
        $this->should('return None when the token not exists in db', function (): void {
            $x = $this->repository->lookupOptionByToken('INVALID_TOKEN');

            $this->assertInstanceOf(None::class, $x);
        });
    }

    /**
     * attrs.
     *
     * @return array
     */
    public function attrs(): array
    {
        $invitation = $this->examples->invitations[0];
        return [
            'staffId' => $invitation->staffId,
            'email' => $invitation->email,
            'token' => 'token',
            'roleIds' => [$this->examples->roles[0]->id, $this->examples->roles[1]->id],
            'officeIds' => [$this->examples->offices[0]->id, $this->examples->offices[1]->id],
            'officeGroupIds' => [],
            'expiredAt' => Carbon::now(),
            'createdAt' => Carbon::now(),
        ];
    }
}
