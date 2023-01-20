<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\File;

use function app;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\File\GenerateFileNameInteractor;

/**
 * {@link \UseCase\File\GenerateFileNameInteractor} のテスト.
 */
final class GenerateFileNameInteractorTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private const FILENAME = 'サービス提供票_#{user}_#{providedIn}.pdf';

    private int $userId;
    private GenerateFileNameInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->config
                ->allows('get')
                ->andReturn(self::FILENAME)
                ->byDefault();
            $self->interactor = app(GenerateFileNameInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return file name', function (): void {
            $userName = $this->examples->users[0]->name->displayName;
            $providedIn = $this->examples->ltcsProvisionReports[0]->providedIn;

            $expected = str_replace(
                ['#{user}', '#{providedIn}'],
                [
                    'user' => $userName,
                    'providedIn' => $providedIn->format('Ym'),
                ],
                self::FILENAME
            );
            $this->assertSame(
                $expected,
                $this->interactor->handle(
                    'ltcs_provision_report_sheet_pdf',
                    ['user' => $userName, 'providedIn' => $providedIn]
                ),
            );
        });
    }
}
