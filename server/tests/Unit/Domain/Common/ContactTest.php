<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * Contact のテスト
 */
class ContactTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected Contact $contact;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ContactTest $self): void {
            $self->values = [
                'tel' => '01-2345-6789',
                'relationship' => ContactRelationship::family(),
                'name' => '田中花子',
            ];
            $self->contact = Contact::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'tel' => ['tel'],
            'relationship' => ['relationship'],
            'name' => ['name'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->contact->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->contact);
        });
    }
}
