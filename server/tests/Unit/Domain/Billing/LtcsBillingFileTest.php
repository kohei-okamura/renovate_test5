<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingFile;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingFile} のテスト.
 */
final class LtcsBillingFileTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $x = $this->createInstance();
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
            $x = $this->createInstance();
            $this->assertMatchesJsonSnapshot($x->toJson());
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingFile
     */
    private function createInstance(array $attrs = []): LtcsBillingFile
    {
        $x = new LtcsBillingFile(
            name: '介護給付費請求書・明細書_新宿_202012.csv',
            path: 'attachments/xyz.csv',
            token: str_repeat('x', 60),
            mimeType: MimeType::csv(),
            createdAt: Carbon::create(2009, 10, 10, 19, 11, 19),
            downloadedAt: Carbon::create(2006, 12, 13, 3, 55, 31),
        );
        return $x->copy($attrs);
    }
}
