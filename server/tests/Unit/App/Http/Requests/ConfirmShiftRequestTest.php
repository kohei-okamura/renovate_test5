<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\ConfirmShiftRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * ConfirmShiftRequest のテスト
 */
class ConfirmShiftRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindShiftUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use SessionMixin;
    use UnitSupport;
    use StaffResolverMixin;
    use LookupShiftUseCaseMixin;

    protected ConfirmShiftRequest $request;
    private Shift $shift;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ConfirmShiftRequestTest $self): void {
            $self->request = new ConfirmShiftRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            $self->shift = $self->examples->shifts[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->findShiftUseCase
                ->allows('handle')
                ->andReturn(
                    FinderResult::from(
                        [
                            $self->examples->shifts[0],
                            $self->examples->shifts[2],
                            $self->examples->shifts[3],
                            $self->examples->shifts[8],
                        ],
                        Pagination::create(['all' => true])
                    )
                );
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[0]->id)
                ->andReturn(Seq::from($self->examples->shifts[0]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[2]->id)
                ->andReturn(Seq::from($self->examples->shifts[2]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[8]->id)
                ->andReturn(Seq::from($self->examples->shifts[8]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateShifts(),
                    $self->examples->shifts[0]->id,
                    $self->examples->shifts[2]->id
                )
                ->andReturn(Seq::fromArray([$self->examples->shifts[0], $self->examples->shifts[2]]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateShifts(),
                    $self->examples->shifts[0]->id,
                    $self->examples->shifts[2]->id,
                    $self::NOT_EXISTING_ID
                )
                ->andReturn(Seq::emptySeq())
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });

        $examples = [
            'when ids is empty' => [
                ['ids' => ['入力してください。']],
                ['ids' => ''],
                $this->defaultInput(),
            ],
            'when ids is not array' => [
                ['ids' => ['配列にしてください。', '正しい値を入力してください。']],
                ['ids' => 'error'],
                $this->defaultInput(),
            ],
            'when ids contain unknown shiftId' => [
                ['ids' => ['正しい値を入力してください。']],
                ['ids' => [...($this->defaultInput()['ids']), self::NOT_EXISTING_ID]],
                $this->defaultInput(),
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
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
        return [
            'ids' => [
                $this->examples->shifts[0]->id,
                $this->examples->shifts[2]->id,
            ],
        ];
    }
}
