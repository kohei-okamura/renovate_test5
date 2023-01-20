<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\DwsVisitingCareForPwsdDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\IdentifyDwsHomeHelpServiceDictionaryUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsVisitingCareForPwsdDictionaryUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\GetDwsBillingStatementInfoInteractor;

/**
 * {@link \UseCase\Billing\GetDwsBillingStatementInfoInteractor} Test.
 */
class GetDwsBillingStatementInfoInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use DwsHomeHelpServiceDictionaryEntryFinderMixin;
    use DwsVisitingCareForPwsdDictionaryEntryFinderMixin;
    use IdentifyDwsHomeHelpServiceDictionaryUseCaseMixin;
    use IdentifyDwsVisitingCareForPwsdDictionaryUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingStatementUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private DwsBillingBundle $billingBundle;
    private DwsBillingStatement $billingStatement;
    private DwsBillingStatementItem $defaultBillingStatementItem;

    private GetDwsBillingStatementInfoInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetDwsBillingStatementInfoInteractorTest $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->billingBundle = $self->examples->dwsBillingBundles[1];
            $self->defaultBillingStatementItem = $self->examples->dwsBillingStatements[2]->items[0];
            $self->billingStatement = $self->examples->dwsBillingStatements[2]->copy([
                'items' => [
                    $self->defaultBillingStatementItem->copy([
                        'serviceCode' => ServiceCode::fromString('110000'), // 居宅のサービスコード
                    ]),
                    $self->defaultBillingStatementItem->copy([
                        'serviceCode' => ServiceCode::fromString('120000'), // 重訪のサービスコード
                    ]),
                ],
            ]);

            $self->dwsHomeHelpServiceDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($self->examples->dwsHomeHelpServiceDictionaryEntries[0]),
                    Pagination::create()
                ))
                ->byDefault();
            $self->dwsVisitingCareForPwsdDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($self->examples->dwsVisitingCareForPwsdDictionaryEntries[0]),
                    Pagination::create()
                ))
                ->byDefault();
            $self->identifyDwsHomeHelpServiceDictionaryUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsHomeHelpServiceDictionaries[0]))
                ->byDefault();
            $self->identifyDwsVisitingCareForPwsdDictionaryUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsVisitingCareForPwsdDictionaries[0]))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingBundle))
                ->byDefault();
            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingStatement))
                ->byDefault();

            $self->interactor = app(GetDwsBillingStatementInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return assoc with parameters', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->billingStatement->id
            );

            $this->assertArrayHasKey('billing', $actual);
            $this->assertArrayHasKey('bundle', $actual);
            $this->assertArrayHasKey('statement', $actual);
            $this->assertArrayHasKey('serviceCodeDictionary', $actual);

            $this->assertModelStrictEquals($this->billing, $actual['billing']);
            $this->assertModelStrictEquals($this->billingBundle, $actual['bundle']);
            $this->assertModelStrictEquals($this->billingStatement, $actual['statement']);
            $this->assertEquals([
                $this->examples->dwsHomeHelpServiceDictionaryEntries[0]->serviceCode->toString() => $this->examples->dwsHomeHelpServiceDictionaryEntries[0]->name,
                $this->examples->dwsVisitingCareForPwsdDictionaryEntries[0]->serviceCode->toString() => $this->examples->dwsVisitingCareForPwsdDictionaryEntries[0]->name,
            ], $actual['serviceCodeDictionary']);
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->billing->id)
                ->andReturn(Seq::from($this->billing));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->billingStatement->id
            );
        });
        $this->should('use LookupDwsBillingBundleUseCase', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->billing->id, $this->billingBundle->id)
                ->andReturn(Seq::from($this->billingBundle));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->billingStatement->id
            );
        });
        $this->should('use LookupDwsBillingStatementUseCase', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id
                )
                ->andReturn(Seq::from($this->billingStatement));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->billingStatement->id
            );
        });
        $this->should('use DwsHomeHelpServiceDictionaryEntryFinder', function (): void {
            $this->dwsHomeHelpServiceDictionaryEntryFinder
                ->expects('find')
                ->with(
                    equalTo([
                        'providedIn' => $this->billingBundle->providedIn,
                        'serviceCodes' => ['110000'],
                    ]),
                    equalTo(['all' => true, 'sortBy' => 'id'])
                )
                ->andReturn(FinderResult::from(
                    Seq::from(
                        $this->examples->dwsHomeHelpServiceDictionaryEntries[0]
                    ),
                    Pagination::create()
                ));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->billingStatement->id
            );
        });
        $this->should(
            'not use identifyDwsHomeHelpServiceDictionaryUseCase when Statements not include ServiceCode of HomeHelpService',
            function (): void {
                $statement = $this->examples->dwsBillingStatements[2]->copy([
                    'items' => [
                        $this->defaultBillingStatementItem->copy([
                            'serviceCode' => ServiceCode::fromString('120000'), // 重訪のサービスコード
                        ]),
                    ],
                ]);
                $this->lookupDwsBillingStatementUseCase
                    ->allows('handle')
                    ->andReturn(Seq::from($statement));

                $this->identifyDwsHomeHelpServiceDictionaryUseCase
                    ->expects('handle')
                    ->times(0);

                $this->interactor->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id
                );
            }
        );
        $this->should('use DwsVisitingCareForPwsdDictionaryEntryFinder', function (): void {
            $this->dwsVisitingCareForPwsdDictionaryEntryFinder
                ->expects('find')
                ->with(
                    equalTo([
                        'providedIn' => $this->billingBundle->providedIn,
                        'serviceCodes' => ['120000'],
                    ]),
                    equalTo(['all' => true, 'sortBy' => 'id'])
                )
                ->andReturn(FinderResult::from(
                    Seq::from(
                        $this->examples->dwsVisitingCareForPwsdDictionaryEntries[0]
                    ),
                    Pagination::create()
                ));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->billingBundle->id,
                $this->billingStatement->id
            );
        });
        $this->should(
            'not use IdentifyDwsVisitingCareForPwsdDictionaryUseCase when Statements not include ServiceCode of DwsVisitingCareForPwsd',
            function (): void {
                $statement = $this->examples->dwsBillingStatements[2]->copy([
                    'items' => [
                        $this->defaultBillingStatementItem->copy([
                            'serviceCode' => ServiceCode::fromString('110000'), // 居宅のサービスコード
                        ]),
                    ],
                ]);
                $this->lookupDwsBillingStatementUseCase
                    ->allows('handle')
                    ->andReturn(Seq::from($statement));

                $this->identifyDwsVisitingCareForPwsdDictionaryUseCase
                    ->expects('handle')
                    ->times(0);

                $this->interactor->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id
                );
            }
        );
        $this->should('throw NotFoundException when LookupDwsBillingUseCase return Empty', function (): void {
            $this->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->billingStatement->id
                    );
                }
            );
        });
        $this->should('throw NotFoundException when LookupDwsBillingBundleUseCase return Empty', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->billingStatement->id
                    );
                }
            );
        });
        $this->should('throw NotFoundException when LookupDwsBillingStatementUseCase return Empty', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->billingBundle->id,
                        $this->billingStatement->id
                    );
                }
            );
        });
        $this->should(
            'throw RuntimeException when DwsHomeHelpServiceDictionaryEntryFinder return empty',
            function (): void {
                $this->dwsHomeHelpServiceDictionaryEntryFinder
                    ->allows('find')
                    ->andReturn(FinderResult::from(Seq::empty(), Pagination::create()));

                $this->assertThrows(
                    RuntimeException::class,
                    function (): void {
                        $this->interactor->handle(
                            $this->context,
                            $this->billing->id,
                            $this->billingBundle->id,
                            $this->billingStatement->id
                        );
                    }
                );
            }
        );
        $this->should(
            'throw RuntimeException when DwsVisitingCareForPwsdDictionaryEntryFinder return empty',
            function (): void {
                $this->dwsVisitingCareForPwsdDictionaryEntryFinder
                    ->allows('find')
                    ->andReturn(FinderResult::from(Seq::empty(), Pagination::create()));

                $this->assertThrows(
                    RuntimeException::class,
                    function (): void {
                        $this->interactor->handle(
                            $this->context,
                            $this->billing->id,
                            $this->billingBundle->id,
                            $this->billingStatement->id
                        );
                    }
                );
            }
        );
    }
}
