<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingFile;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingFile} のテスト.
 */
final class DwsBillingFileTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_instance(): void
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
     * @return \Domain\Billing\DwsBillingFile
     */
    private function createInstance(array $attrs = []): DwsBillingFile
    {
        $x = new DwsBillingFile(
            name: 'サービス提供実績記録票_藤沢_202012.csv',
            path: 'attachments/xyz.csv',
            token: str_repeat('x', 60),
            mimeType: MimeType::csv(),
            createdAt: Carbon::create(2009, 10, 10, 19, 11, 19),
            downloadedAt: Carbon::create(2006, 12, 13, 3, 55, 31),
        );
        return $x->copy($attrs);
    }
}
