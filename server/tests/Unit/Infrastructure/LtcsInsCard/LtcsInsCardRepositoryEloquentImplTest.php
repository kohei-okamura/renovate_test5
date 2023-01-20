<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\LtcsInsCard;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCard as DomainLtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Infrastructure\LtcsInsCard\LtcsInsCardRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * LtcsInsCardRepositoryEloquentImpl のテスト.
 */
class LtcsInsCardRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsInsCardRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (LtcsInsCardRepositoryEloquentImplTest $self): void {
            $self->repository = app(LtcsInsCardRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->ltcsInsCards[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->ltcsInsCards[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
        $this->should('return an entity when entity has multiple LtcsInsCardMaxBenefitQuota', function (): void {
            $ltcsInsCard = $this->examples->ltcsInsCards[0]->copy([
                'maxBenefitQuotas' => [
                    LtcsInsCardMaxBenefitQuota::create([
                        'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType1(),
                        'maxBenefitQuota' => 100,
                    ]),
                    LtcsInsCardMaxBenefitQuota::create([
                        'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType2(),
                        'maxBenefitQuota' => 100,
                    ]),
                ],
                'version' => $this->examples->ltcsInsCards[0]->version + 1,
            ]);
            $this->repository->store($ltcsInsCard);
            $actual = $this->repository->lookup($this->examples->ltcsInsCards[0]->id);
            $this->assertModelStrictEquals(
                $ltcsInsCard,
                $actual->head()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store(): void
    {
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $x = $this->examples->ltcsInsCards[0];
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'userId' => $this->examples->users[0]->id,
                'status' => $x->status,
                'insNumber' => $x->insNumber,
                'insurerNumber' => $x->insurerNumber,
                'insurerName' => $x->insurerName,
                'ltcsLevel' => $x->ltcsLevel,
                'maxBenefitQuotas' => $x->maxBenefitQuotas,
                'copayRate' => $x->copayRate,
                'effectivatedOn' => $x->effectivatedOn,
                'issuedOn' => $x->issuedOn,
                'certificatedOn' => $x->certificatedOn,
                'activatedOn' => $x->activatedOn,
                'deactivatedOn' => $x->deactivatedOn,
                'copayActivatedOn' => $x->copayActivatedOn,
                'copayDeactivatedOn' => $x->copayDeactivatedOn,
                'carePlanAuthorType' => $x->carePlanAuthorType,
                'careManagerName' => $x->careManagerName,
                'carePlanAuthorOfficeId' => $x->carePlanAuthorOfficeId,
                'communityGeneralSupportCenterId' => $x->communityGeneralSupportCenterId,
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = DomainLtcsInsCard::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $insCard = $this->examples->ltcsInsCards[0];
            $copiedInsCard = $insCard->copy([
                'isEnabled' => !$insCard->isEnabled,
                'version' => $insCard->version + 1,
            ]);
            $this->repository->store($copiedInsCard);
            $actual = $this->repository->lookup($insCard->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $copiedInsCard,
                $actual->head()
            );
        });
        $this->should('update LtcsInsCardMaxBenefitQuotas when update the entity', function (): void {
            $insCard = $this->examples->ltcsInsCards[13];
            $this->assertCount(4, $insCard->maxBenefitQuotas);
            $copiedInsCard = $insCard->copy([
                'maxBenefitQuotas' => $this->examples->ltcsInsCards[0]->maxBenefitQuotas,
                'version' => $insCard->version + 1,
            ]);
            $this->repository->store($copiedInsCard);

            /** @var \Domain\LtcsInsCard\LtcsInsCard $actual */
            $actual = $this->repository->lookup($copiedInsCard->id)->head();
            $this->assertCount(2, $actual->maxBenefitQuotas);
            $this->assertEach(
                function ($a, $b): void {
                    $this->assertModelStrictEquals($a, $b);
                },
                $copiedInsCard->maxBenefitQuotas,
                $actual->maxBenefitQuotas,
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $this->repository->remove($this->examples->ltcsInsCards[0]);
            $actual = $this->repository->lookup($this->examples->ltcsInsCards[0]->id);
            $this->assertCount(0, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById(
                $this->examples->ltcsInsCards[0]->id,
                $this->examples->ltcsInsCards[1]->id
            );
            $ltcsInsCard0 = $this->repository->lookup($this->examples->ltcsInsCards[0]->id);
            $ltcsInsCard1 = $this->repository->lookup($this->examples->ltcsInsCards[1]->id);
            $this->assertCount(0, $ltcsInsCard0);
            $this->assertCount(0, $ltcsInsCard1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->ltcsInsCards[0]->id);
            $actual = $this->repository->lookup($this->examples->ltcsInsCards[0]->id);
            $this->assertCount(0, $actual);
            $this->assertTrue($this->repository->lookup($this->examples->ltcsInsCards[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->ltcsInsCards[2]->id)->nonEmpty());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupByUserId(): void
    {
        $this->should('return Map of Seq with User ID of key', function () {
            $ids = [
                $this->examples->users[0]->id,
                $this->examples->users[1]->id,
            ];
            $actual = $this->repository->lookupByUserId(...$ids);

            $this->assertInstanceOf(Map::class, $actual);
            $actual->each(function (Seq $x, int $key) use ($ids): void {
                $this->assertTrue(in_array($key, $ids, true));
                $this->assertForAll($x, fn (LtcsInsCard $insCard): bool => $insCard->userId === $key);
            });
        });
    }
}
