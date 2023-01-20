<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\DwsCertification;

use Domain\Common\Carbon;
use Domain\Common\StructuredName;
use Domain\DwsCertification\Child;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\DwsCertification\DwsCertification} のテスト.
 */
final class DwsCertificationTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    private array $values = [];
    private DwsCertification $dwsCertification;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'id' => 1,
                'userId' => $self->examples->users[0]->id,
                'effectivatedOn' => '2016-05-02 00:00:00',
                'status' => DwsCertificationStatus::approved(),
                'dwsNumber' => '0123456789',
                'dwsTypes' => [DwsType::physical()],
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::from(1),
                    'officeId' => $self->examples->dwsCertifications[0]->copayCoordination->officeId,
                ]),
                'child' => Child::create(
                    [
                        'name' => new StructuredName(
                            familyName: '内藤',
                            givenName: '勇介',
                            phoneticFamilyName: 'ナイトウ',
                            phoneticGivenName: 'ユウスケ',
                        ),
                        'birthday' => Carbon::parse('2000-01-01T00:00:00'),
                    ]
                ),
                'issuedOn' => '2016-05-02 00:00:00',
                'cityName' => '中野区',
                'cityCode' => '123456',
                'dwsLevel' => DwsLevel::level1(),
                'isSubjectOfComprehensiveSupport' => true,
                'activatedOn' => '2016-05-02 00:00:00',
                'deactivatedOn' => '2020-05-02 00:00:00',
                'grants' => [2, '支給量テスト', '2007-05-02T00:00:00+0900', '1972-04-17T00:00:00+0900'],
                'copayRate' => 3000,
                'copayActivatedOn' => '2016-05-02 00:00:00',
                'copayDeactivatedOn' => '2020-05-02 00:00:00',
                'agreements' => [10, 3, 13, 1000, '1980-01-27T00:00:00+0900', '1997-04-06T00:00:00+0900'],
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->dwsCertification = DwsCertification::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have userId attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('userId'), Arr::get($this->values, 'userId'));
        });
        $this->should('have effectivatedOn attribute', function (): void {
            $this->assertSame(
                $this->dwsCertification->get('effectivatedOn'),
                Arr::get($this->values, 'effectivatedOn')
            );
        });
        $this->should('have status attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('status'), Arr::get($this->values, 'status'));
        });
        $this->should('have dwsNumber attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('dwsNumber'), Arr::get($this->values, 'dwsNumber'));
        });
        $this->should('have dwsTypes attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('dwsTypes'), Arr::get($this->values, 'dwsTypes'));
        });
        $this->should('have copayCoordination attribute', function (): void {
            $this->assertSame(
                $this->dwsCertification->get('copayCoordination'),
                Arr::get($this->values, 'copayCoordination')
            );
        });
        $this->should('have child attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('child'), Arr::get($this->values, 'child'));
        });
        $this->should('have issuedOn attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('issuedOn'), Arr::get($this->values, 'issuedOn'));
        });
        $this->should('have cityName attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('cityName'), Arr::get($this->values, 'cityName'));
        });
        $this->should('have cityCode attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('cityCode'), Arr::get($this->values, 'cityCode'));
        });
        $this->should('have dwsLevel attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('dwsLevel'), Arr::get($this->values, 'dwsLevel'));
        });
        $this->should('have isSubjectOfComprehensiveSupport attribute', function (): void {
            $this->assertSame(
                $this->dwsCertification->get('isSubjectOfComprehensiveSupport'),
                Arr::get($this->values, 'isSubjectOfComprehensiveSupport')
            );
        });
        $this->should('have activatedOn attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('activatedOn'), Arr::get($this->values, 'activatedOn'));
        });
        $this->should('have deactivatedOn attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('deactivatedOn'), Arr::get($this->values, 'deactivatedOn'));
        });
        $this->should('have grants attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('grants'), Arr::get($this->values, 'grants'));
        });
        $this->should('have copayRate attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('copayRate'), Arr::get($this->values, 'copayRate'));
        });
        $this->should('have copayLimit attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('copayLimit'), Arr::get($this->values, 'copayLimit'));
        });
        $this->should('have copayActivatedOn attribute', function (): void {
            $this->assertSame(
                $this->dwsCertification->get('copayActivatedOn'),
                Arr::get($this->values, 'copayActivatedOn')
            );
        });
        $this->should('have copayDeactivatedOn attribute', function (): void {
            $this->assertSame(
                $this->dwsCertification->get('copayDeactivatedOn'),
                Arr::get($this->values, 'copayDeactivatedOn')
            );
        });
        $this->should('have agreements attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('agreements'), Arr::get($this->values, 'agreements'));
        });
        $this->should('have isEnabled attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('isEnabled'), Arr::get($this->values, 'isEnabled'));
        });
        $this->should('have version attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('version'), Arr::get($this->values, 'version'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->dwsCertification->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->dwsCertification);
        });
    }
}
