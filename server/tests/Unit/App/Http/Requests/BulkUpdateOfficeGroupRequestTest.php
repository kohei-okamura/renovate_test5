<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\BulkUpdateOfficeGroupRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * BulkUpdateOfficeGroupRequest のテスト.
 */
class BulkUpdateOfficeGroupRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected BulkUpdateOfficeGroupRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (BulkUpdateOfficeGroupRequestTest $self): void {
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->officeGroups[1]->id)
                ->andReturn(Seq::from($self->examples->officeGroups[1]));
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->officeGroups[0]->id)
                ->andReturn(Seq::from($self->examples->officeGroups[0]));
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->request = new BulkUpdateOfficeGroupRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return array', function (): void {
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode(['list' => $this->defaultInput()])
            );

            $this->assertEquals(
                $this->defaultInput(),
                $this->request->payload()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance(['list' => $this->defaultInput()]);
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when unknown parentOfficeGroupId given' => [
                ['list.0.parentOfficeGroupId' => ['正しい値を入力してください。']],
                ['parentOfficeGroupId' => self::NOT_EXISTING_ID],
                ['parentOfficeGroupId' => $this->examples->officeGroups[0]->id],
            ],
            'when sortOrder is empty' => [
                ['list.0.sortOrder' => ['入力してください。']],
                ['sortOrder' => ''],
            ],
            'when sortOrder is non-integer' => [
                ['list.0.sortOrder' => ['整数で入力してください。']],
                ['sortOrder' => 'あいうえおかきくけこ'],
                ['sortOrder' => 1111111111],
            ],
            'when id is empty' => [
                ['list.0.id' => ['入力してください。']],
                ['id' => ''],
                ['id' => $this->examples->officeGroups[1]->id],
            ],
            'when id is not exists' => [
                ['list.0.id' => ['正しい値を入力してください。']],
                ['id' => self::NOT_EXISTING_ID],
                ['id' => $this->examples->officeGroups[1]->id],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance(['list' => [$invalid + $this->defaultInput()[0]]]);
                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance(['list' => [$valid + $this->defaultInput()[0]]]);
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     * NOTE: 実際のリクエストパラメータの list の値を返す
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            [
                'id' => $this->examples->officeGroups[1]->id,
                'parentOfficeGroupId' => $this->examples->officeGroups[0]->id,
                'sortOrder' => Carbon::now()->unix(),
            ],
        ];
    }
}
