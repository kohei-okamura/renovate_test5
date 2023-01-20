<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use App\Validations\CustomValidator;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Password;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Context\Context;
use Domain\File\ReadonlyFileStorage;
use Domain\Staff\Certification;
use Domain\Staff\Staff;
use Domain\Staff\StaffStatus;
use Illuminate\Console\Command;
use Lib\Exceptions\InvalidConsoleOptionException;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use SplFileInfo;
use Symfony\Component\Console\Input\InputOption;
use UseCase\Staff\CreateStaffUseCase;

/**
 * スタッフ登録コマンド.
 *
 * @codeCoverageIgnore 一時的に除外（自社以外の事業者を追加するまでにはテストする）
 */
final class CreateStaffCommand extends Command
{
    use CommandSupport;

    private const OPTION_JSON = 'input';
    private const OPTION_ORGANIZATION = 'organization';
    private const OPTION_PASSWORD = 'password';

    /**
     * コンソールコマンドの名前.
     *
     * @var string
     */
    protected $name = 'staff:create';

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = 'スタッフ登録コマンド';

    /**
     * コマンドを実行する.
     *
     * @param \UseCase\Staff\CreateStaffUseCase $useCase
     * @param \Domain\File\ReadonlyFileStorage $storage
     * @return int
     */
    public function handle(CreateStaffUseCase $useCase, ReadonlyFileStorage $storage): int
    {
        return $this->withLogging(
            'スタッフ登録コマンド',
            function () use ($useCase, $storage): int {
                $context = $this->createContext(self::OPTION_ORGANIZATION)->getOrElse(function (): void {
                    throw new InvalidConsoleOptionException('The "--organization" option is required.');
                });

                $jsonPath = $this->getStringOption(self::OPTION_JSON)->getOrElse(function (): void {
                    throw new InvalidConsoleOptionException('The "--json" option is required.');
                });
                $jsonFile = $storage->fetch($jsonPath)->getOrElse(function () use ($jsonPath): void {
                    throw new NotFoundException("File({$jsonPath}) not found");
                });

                $password = $this->getStringOption(self::OPTION_PASSWORD)->getOrElse(function (): void {
                    throw new InvalidConsoleOptionException('The "--password" option is required.');
                });

                $staff = $this->parseJson($context, $jsonFile, $password);
                $useCase->handle($context, $staff, Option::none());

                return self::SUCCESS;
            },
            [self::OPTION_PASSWORD => '***']
        );
    }

    /** {@inheritdoc} */
    protected function getOptions(): array
    {
        return [
            [self::OPTION_JSON, 'j', InputOption::VALUE_REQUIRED, '入力 JSON ファイル'],
            [self::OPTION_ORGANIZATION, 'o', InputOption::VALUE_REQUIRED, '事業者コード'],
            [self::OPTION_PASSWORD, 'p', InputOption::VALUE_REQUIRED, '登録するスタッフのログインパスワード'],
        ];
    }

    /**
     * JSON ファイルを読み込んでスタッフのエンティティを生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \SplFileInfo $jsonFile
     * @param string $password
     * @throws \JsonException
     * @return \Domain\Staff\Staff
     */
    private function parseJson(Context $context, SplFileInfo $jsonFile, string $password): Staff
    {
        $content = file_get_contents($jsonFile->getPathname());
        $input = Json::decode($content, true);
        $this->validate($context, $input);
        return Staff::create([
            'employeeNumber' => $input['employeeNumber'],
            'name' => new StructuredName(
                familyName: $input['name']['familyName'],
                givenName: $input['name']['givenName'],
                phoneticFamilyName: $input['name']['phoneticFamilyName'],
                phoneticGivenName: $input['name']['phoneticGivenName'],
            ),
            'sex' => Sex::from(+$input['sex']),
            'birthday' => Carbon::parse($input['birthday']),
            'addr' => new Addr(
                postcode: $input['addr']['postcode'],
                prefecture: Prefecture::from(+$input['addr']['prefecture']),
                city: $input['addr']['city'],
                street: $input['addr']['street'],
                apartment: $input['addr']['apartment'],
            ),
            'location' => Location::create([]),
            'tel' => $input['tel'],
            'fax' => $input['fax'],
            'email' => $input['email'],
            'password' => Password::fromString($password),
            'certifications' => Seq::fromArray($input['certifications'] ?? [])
                ->map(fn ($x): Certification => Certification::from(+$x))
                ->toArray(),
            'roleIds' => [...Seq::fromArray($input['roleIds'] ?? [])->map(fn ($x): int => +$x)],
            'officeIds' => [...Seq::fromArray($input['officeIds'] ?? [])->map(fn ($x): int => +$x)],
            'officeGroupIds' => [...Seq::fromArray($input['officeGroupIds'] ?? [])->map(fn ($x): int => +$x)],
            'isVerified' => true,
            'status' => StaffStatus::active(),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * バリデーションを行う.
     *
     * @param \Domain\Context\Context $context
     * @param array $input
     * @return void
     */
    private function validate(Context $context, array $input): void
    {
        $validator = CustomValidator::make($context, $input, [
            'employeeNumber' => ['nullable', 'max:20'],
            'name.familyName' => ['required', 'max:100'],
            'name.givenName' => ['required', 'max:100'],
            'name.phoneticFamilyName' => ['required', 'max:100', 'katakana'],
            'name.phoneticGivenName' => ['required', 'max:100', 'katakana'],
            'sex' => ['required', 'sex'],
            'birthday' => ['required', 'date'],
            'addr.postcode' => ['required', 'postcode'],
            'addr.prefecture' => ['required', 'prefecture'],
            'addr.city' => ['required', 'max:200'],
            'addr.street' => ['required', 'max:200'],
            'addr.apartment' => ['max:200'],
            'tel' => ['required', 'phone_number'],
            'fax' => ['phone_number'],
            'email' => ['required', 'email', 'max:255'],
            'certifications.*' => ['nullable', 'certification'],
            'roleIds' => ['required', 'role_exists'],
            'officeIds' => ['array', 'office_exists_ignore_permissions'],
            'officeGroupIds' => ['array', 'office_group_exists'],
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $this->logger()->warning('Validation failed', $errors);
            throw new RuntimeException('Validation failed');
        }
    }
}
