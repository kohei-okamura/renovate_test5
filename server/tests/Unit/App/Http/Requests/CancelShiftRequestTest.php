<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CancelShiftRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Faker\Generator;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * CancelShiftRequest Test.
 */
class CancelShiftRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupShiftUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private CancelShiftRequest $request;
    private Generator $faker;
    private Shift $shift;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CancelShiftRequestTest $self): void {
            $self->faker = app(Generator::class);
            $self->shift = $self->examples->shifts[0];
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->shift->id)
                ->andReturn(Seq::from($self->shift))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[3]->id)
                ->andReturn(Seq::from($self->examples->shifts[3]));
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[11]->id)
                ->andReturn(Seq::from($self->examples->shifts[11]));
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->request = new CancelShiftRequest();

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
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->routeParam() + $this->defaultInput());
            $this->assertTrue($validator->passes());
        });

        $examples = [
            'when ID is canceled' => [
                ['id' => ['存在しないIDまたはキャンセル済みのIDが含まれています。']],
                ['id' => $this->examples->shifts[3]->id],
                ['id' => $this->shift->id],
            ],
            'when schedule.start of entity is less than now' => [
                ['id' => ['過去の勤務シフトはキャンセルできません。']],
                ['id' => $this->examples->shifts[11]->id],
                ['id' => $this->examples->shifts[0]->id],
            ],
            'when reason is empty' => [
                ['reason' => ['入力してください。']],
                ['reason' => ''],
                ['reason' => 'キャンセル理由'],
            ],
            'when reason is not string' => [
                ['reason' => ['文字列で入力してください。']],
                ['reason' => 12345],
                ['reason' => 'キャンセル理由'],
            ],
            'when reason is over 255 letters' => [
                ['reason' => ['255文字以内で入力してください。']],
                ['reason' => $this->faker->numerify(str_repeat('#', 256))],
                ['reason' => $this->faker->numerify(str_repeat('#', 255))],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->routeParam() + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->routeParam() + $this->defaultInput());
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return ['reason' => 'キャンセル理由'];
    }

    /**
     * ルートパラメーター.
     *
     * @return array
     */
    private function routeParam(): array
    {
        return [
            'id' => $this->shift->id,
        ];
    }
}
