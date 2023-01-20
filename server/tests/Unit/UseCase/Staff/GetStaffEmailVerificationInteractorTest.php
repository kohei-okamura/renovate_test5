<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Staff\StaffEmailVerification;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\TokenExpiredException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\App\Http\Concretes\TestingContext;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffEmailVerificationRepositoryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Staff\GetStaffEmailVerificationInteractor;

/**
 * GetStaffEmailVerificationInteractor のテスト.
 */
final class GetStaffEmailVerificationInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StaffRepositoryMixin;
    use StaffEmailVerificationRepositoryMixin;
    use UnitSupport;

    public const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';
    public const NOT_EXIST_ORGANIZATION_ID = 99;

    private GetStaffEmailVerificationInteractor $interactor;
    private StaffEmailVerification $staffEmailVerification;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetStaffEmailVerificationInteractorTest $self): void {
            $self->staffEmailVerification = $self->examples->staffEmailVerifications[0]->copy([
                'staffId' => $self->examples->staffs[0]->id,
                'token' => self::TOKEN,
                'expiredAt' => Carbon::tomorrow(),
            ]);

            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0])
                ->byDefault();

            $self->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();

            $self->staffEmailVerificationRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::from($self->staffEmailVerification))
                ->byDefault();

            $self->interactor = app(GetStaffEmailVerificationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a StaffEmailVerification', function (): void {
            $this->staffEmailVerificationRepository
                ->expects('lookupOptionByToken')
                ->with(self::TOKEN)
                ->andReturn(Option::from($this->staffEmailVerification));

            $this->assertModelStrictEquals(
                $this->staffEmailVerification,
                $this->interactor->handle($this->context, self::TOKEN)
            );
        });
        $this->should('throw NotFoundException when organization id is different', function (): void {
            TestingContext::prepare(
                $this->context,
                $this->examples->organizations[1],
                Option::from($this->examples->staffs[0])
            );

            $this->assertNotSame($this->examples->staffs[0]->organizationId, $this->examples->organizations[1]->id);
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::TOKEN);
                }
            );
        });
        $this->should('throw NotFoundException when StaffEmailVerification not found', function (): void {
            $this->staffEmailVerificationRepository->allows('lookupOptionByToken')->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, 'WRONG_TOKEN');
                }
            );
        });
        $this->should('throw ForbiddenException when the StaffEmailVerification is expired', function (): void {
            Carbon::setTestNow(Carbon::tomorrow()->addSecond());

            $this->assertThrows(
                TokenExpiredException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::TOKEN);
                }
            );
        });
    }
}
