<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildCopayListPdfParamInteractor;

/**
 * {@link \UseCase\Billing\BuildCopayListPdfParamInteractor} のテスト.
 */
final class BuildCopayListPdfParamInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use IdentifyDwsCertificationUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private Seq $bundles;
    private Seq $statements;
    private BuildCopayListPdfParamInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->bundles = Seq::from($self->examples->dwsBillingBundles[0]);
            $user1 = $self->examples->dwsBillingStatements[0]->user->copy([
                'dwsNumber' => '1234567890',
            ]);
            $statement = $self->examples->dwsBillingStatements[0]->copy([
                'user' => $user1,
                'totalFee' => 100000,
                'totalCappedCopay' => 9300,
                'totalAdjustedCopay' => 9300,
            ]);
            $self->statements = Seq::from($statement);
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->dwsCertifications[0]))
                ->byDefault();

            $self->interactor = app(BuildCopayListPdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupOfficeUseCase', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::viewBillings()],
                    $this->examples->dwsCertifications[0]->copayCoordination->officeId
                )
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle(
                $this->context,
                $this->billing,
                $this->bundles,
                $this->statements
            );
        });
        $this->should('use IdentifyDwsCertificationUseCase', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->statements->head()->user->userId,
                    $this->bundles->head()->providedIn
                )
                ->andReturn(Option::some($this->examples->dwsCertifications[0]));

            $this->interactor->handle(
                $this->context,
                $this->billing,
                $this->bundles,
                $this->statements
            );
        });
        $this->should('throw NotFoundException if DwsCertification not found', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing,
                        $this->bundles,
                        $this->statements
                    );
                }
            );
        });
        $this->should('return CopayListPdf param', function (): void {
            $billing = $this->billing->copy([
                'office' => $this->billing->office->copy([
                    'code' => '8127193362',
                    'addr' => new Addr(
                        postcode: '984-0056',
                        prefecture: Prefecture::miyagi(),
                        city: '仙台市',
                        street: '若林区成田町16番地の2',
                        apartment: 'ロイヤルヒルズ成田町403号',
                    ),
                ]),
            ]);

            $actual = $this->interactor->handle(
                $this->context,
                $billing,
                $this->bundles,
                $this->statements
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
