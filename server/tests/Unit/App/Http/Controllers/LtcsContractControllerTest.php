<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsContractController;
use App\Http\Requests\CreateLtcsContractRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsContractRequest;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Domain\Permission\Permission;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateContractUseCaseMixin;
use Tests\Unit\Mixins\EditContractUseCaseMixin;
use Tests\Unit\Mixins\GetOverlapContractUseCaseMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\LtcsContractController} のテスト.
 */
final class LtcsContractControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateContractUseCaseMixin;
    use EditContractUseCaseMixin;
    use ExamplesConsumer;
    use GetOverlapContractUseCaseMixin;
    use LookupContractUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private Contract $contract;
    private LtcsContractController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->contract = $self->examples->contracts[0]->copy([
                'organizationId' => $self->examples->organizations[0]->id,
            ]);

            $self->createContractUseCase
                ->allows('handle')
                ->andReturn($self->contract)
                ->byDefault();

            $self->editContractUseCase
                ->allows('handle')
                ->andReturn($self->contract)
                ->byDefault();

            $self->getOverlapContractUseCase
                ->allows('handle')
                ->andReturn(Seq::empty())
                ->byDefault();

            $self->lookupContractUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->contract))
                ->byDefault();

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->organizationResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->organizations[0]));

            $self->staffResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->staffs[0]));

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(LtcsContractController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{userId}/contracts',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputForCreate())
        ));
        app()->bind(CreateLtcsContractRequest::class, function () {
            $request = Mockery::mock(CreateLtcsContractRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $actual = app()->call(
                [$this->controller, 'create'],
                ['userId' => $this->examples->contracts[0]->userId]
            );

            $this->assertSame(Response::HTTP_CREATED, $actual->getStatusCode());
        });
        $this->should('return an empty response', function (): void {
            $actual = app()->call(
                [$this->controller, 'create'],
                ['userId' => $this->examples->contracts[0]->userId]
            );

            $this->assertSame('', $actual->getContent());
        });
        $this->should('create Contract using use case', function (): void {
            $this->createContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->contracts[0]->userId,
                    equalTo($this->createContractModelInstance())
                )
                ->andReturn($this->contract);

            app()->call(
                [$this->controller, 'create'],
                ['userId' => $this->examples->contracts[0]->userId]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{user_id}/contracts/{id}',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });

        $this->should('return a 200 response', function (): void {
            $actual = app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->contract->userId, 'id' => $this->contract->id]
            );

            $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
        });
        $this->should('return a JSON of contract', function (): void {
            $expected = Json::encode(['contract' => $this->contract]);

            $actual = app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->contract->userId, 'id' => $this->contract->id]
            );

            $this->assertSame($expected, $actual->getContent());
        });
        $this->should('get contract using use case', function (): void {
            $this->lookupContractUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewLtcsContracts(), $this->contract->userId, $this->contract->id)
                ->andReturn(Seq::from($this->contract));

            app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->contract->userId, 'id' => $this->contract->id]
            );
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupContractUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewLtcsContracts(), $this->contract->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(NotFoundException::class, function (): void {
                app()->call(
                    [$this->controller, 'get'],
                    ['userId' => $this->contract->userId, 'id' => self::NOT_EXISTING_ID]
                );
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{user_id}/contracts/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputForUpdate())
        ));
        app()->bind(UpdateLtcsContractRequest::class, function () {
            $request = Mockery::mock(UpdateLtcsContractRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('payload')->andReturn($this->inputForUpdate())->byDefault();
            return $request;
        });

        $this->should('return a 200 response', function (): void {
            $actual = app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->contract->userId, 'id' => $this->contract->id]
            );

            $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $expected = Json::encode(['contract' => $this->contract]);

            $actual = app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->contract->userId, 'id' => $this->contract->id]
            );

            $this->assertSame($expected, $actual->getContent());
        });
        $this->should('update Contract using use case', function (): void {
            $this->editContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsContracts(),
                    $this->contract->userId,
                    $this->contract->id,
                    equalTo($this->inputForUpdate())
                )
                ->andReturn($this->contract);

            app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->contract->userId, 'id' => $this->contract->id]
            );
        });
    }

    /**
     * リクエストから生成されるはずの契約モデルインスタンス.
     *
     * @return \Domain\Contract\Contract
     */
    private function createContractModelInstance(): Contract
    {
        $input = $this->inputForCreate();
        $value = [
            'officeId' => $input['officeId'],
            'serviceSegment' => ServiceSegment::longTermCare(),
            'status' => ContractStatus::provisional(),
            'contractedOn' => null,
            'terminatedOn' => null,
            'dwsPeriods' => [],
            'ltcsPeriod' => ContractPeriod::create([]),
            'expiredReason' => LtcsExpiredReason::unspecified(),
            'note' => $input['note'],
        ];
        return Contract::create($value);
    }

    /**
     * 登録用Input.
     *
     * @return array
     */
    private function inputForCreate(): array
    {
        return [
            'officeId' => $this->contract->officeId,
            'note' => $this->contract->note,

            // URLパラメータがMockで取れないのでここに追加
            'userId' => $this->examples->users[0]->id,
        ];
    }

    /**
     * 更新用Input.
     *
     * @return array
     */
    private function inputForUpdate(): array
    {
        return [
            'officeId' => $this->contract->officeId,
            'status' => $this->contract->status,
            'contractedOn' => '2020-01-01',
            'terminatedOn' => '2020-12-31',
            'dwsPeriods' => [],
            'ltcsPeriod' => [
                'start' => '2020-05-17',
                'end' => '2020-12-15',
            ],
            'expiredReason' => LtcsExpiredReason::hospitalized()->value(),
            'note' => 'だるまさんが転んだ',

            // URLパラメータがMockで取れないのでここに追加
            'userId' => $this->examples->users[0]->id,
        ];
    }
}
