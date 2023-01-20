<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\Common\Schedule;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\Shift\ServiceOption;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\DwsProvisionReportItem} のテスト
 */
final class DwsProvisionReportItemTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $x = self::createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $x = self::createInstance();
            $this->assertMatchesJsonSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isHomeHelpService(): void
    {
        $examples = [
            'with physicalCare' => [DwsProjectServiceCategory::physicalCare(), true],
            'with housework' => [DwsProjectServiceCategory::housework(), true],
            'with accompanyWithPhysicalCare' => [DwsProjectServiceCategory::accompanyWithPhysicalCare(), true],
            'with accompany' => [DwsProjectServiceCategory::accompany(), true],
            'with visitingCareForPwsd' => [DwsProjectServiceCategory::visitingCareForPwsd(), false],
            'with ownExpense' => [DwsProjectServiceCategory::ownExpense(), false],
        ];
        $this->should(
            'return value as boolean',
            function (DwsProjectServiceCategory $category, bool $expect): void {
                $entity = self::createInstance(compact('category'));
                $this->assertTrue($entity->isHomeHelpService() === $expect);
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_isVisitingCareForPwsd(): void
    {
        $examples = [
            'with physicalCare' => [DwsProjectServiceCategory::physicalCare(), false],
            'with housework' => [DwsProjectServiceCategory::housework(), false],
            'with accompanyWithPhysicalCare' => [DwsProjectServiceCategory::accompanyWithPhysicalCare(), false],
            'with accompany' => [DwsProjectServiceCategory::accompany(), false],
            'with visitingCareForPwsd' => [DwsProjectServiceCategory::visitingCareForPwsd(), true],
            'with ownExpense' => [DwsProjectServiceCategory::ownExpense(), false],
        ];
        $this->should(
            'return value as boolean',
            function (DwsProjectServiceCategory $category, bool $expect): void {
                $entity = self::createInstance(compact('category'));
                $this->assertTrue($entity->isVisitingCareForPwsd() === $expect);
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_isOwnExpense(): void
    {
        $examples = [
            'with physicalCare' => [DwsProjectServiceCategory::physicalCare(), false],
            'with housework' => [DwsProjectServiceCategory::housework(), false],
            'with accompanyWithPhysicalCare' => [DwsProjectServiceCategory::accompanyWithPhysicalCare(), false],
            'with accompany' => [DwsProjectServiceCategory::accompany(), false],
            'with visitingCareForPwsd' => [DwsProjectServiceCategory::visitingCareForPwsd(), false],
            'with ownExpense' => [DwsProjectServiceCategory::ownExpense(), true],
        ];
        $this->should(
            'return value as boolean',
            function (DwsProjectServiceCategory $category, bool $expect): void {
                $entity = self::createInstance(compact('category'));
                $this->assertTrue($entity->isOwnExpense() === $expect);
            },
            compact('examples')
        );
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\ProvisionReport\DwsProvisionReportItem
     */
    private static function createInstance(array $attrs = []): DwsProvisionReportItem
    {
        $values = [
            'schedule' => Schedule::create(),
            'category' => DwsProjectServiceCategory::physicalCare(),
            'headcount' => 1,
            'options' => ServiceOption::firstTime(),
            'movingDurationMinutes' => 0,
            'ownExpenseProgramId' => null,
            'note' => 'aaa',
        ];
        return DwsProvisionReportItem::create($attrs + $values);
    }
}
