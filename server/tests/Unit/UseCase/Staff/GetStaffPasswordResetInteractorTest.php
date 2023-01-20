<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Staff\StaffPasswordReset;
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
use Tests\Unit\Mixins\StaffPasswordResetRepositoryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\GetStaffPasswordResetInteractor;

/**
 * GetStaffPasswordResetInteractor のテスト.
 */
final class GetStaffPasswordResetInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StaffRepositoryMixin;
    use StaffPasswordResetRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    public const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';
    public const NOT_EXIST_ORGANIZATION_ID = 99;

    private GetStaffPasswordResetInteractor $interactor;
    private StaffPasswordReset $staffPasswordReset;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetStaffPasswordResetInteractorTest $self): void {
            $self->staffPasswordReset = $self->examples->staffPasswordResets[0]->copy([
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

            $self->staffPasswordResetRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::from($self->staffPasswordReset))
                ->byDefault();

            $self->staffPasswordResetRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->byDefault();

            $self->interactor = app(GetStaffPasswordResetInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_invoke(): void
    {
        $this->should('return a StaffPasswordReset', function (): void {
            $this->staffPasswordResetRepository
                ->expects('lookupOptionByToken')
                ->with(self::TOKEN)
                ->andReturn(Option::from($this->staffPasswordReset));

            $this->assertModelStrictEquals(
                $this->staffPasswordReset,
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
        $this->should('throw NotFoundException when StaffPasswordReset not found', function (): void {
            $this->staffPasswordResetRepository->allows('lookupOptionByToken')->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, 'WRONG_TOKEN');
                }
            );
        });
        $this->should('throw ForbiddenException when the StaffPasswordReset is expired', function (): void {
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
