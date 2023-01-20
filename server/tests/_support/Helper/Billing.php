<?php

declare(strict_types=1);

namespace Helper;

use Codeception\Module;
use Domain\ModelCompat;
use Illuminate\Contracts\Console\Kernel;
use Lib\Json;
use Lib\Logging;
use PHPUnit\Framework\Assert;
use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Constraints\ArrayStrictEquals;
use Tests\Unit\Helpers\AssertMatchesModelSnapshot;
use Tests\Unit\Helpers\AssertModelStrictEquals;

/**
 * Billing TestSutite ヘルパクラス.
 *
 * このクラスに実装したメソッドはカスタムアクションとして、
 * テストメソッドの$Iで利用することができます.
 */
class Billing extends Module
{
    use AssertModelStrictEquals {
        AssertModelStrictEquals::assertModelStrictEquals as assertModelStrictEqualsUnit;
    }
    use AssertMatchesModelSnapshot {
        AssertMatchesModelSnapshot::assertMatchesModelSnapshot as assertMatchesModelSnapshotUnit;
    }
    use MatchesSnapshots;
    use Logging;

    private $targetClass;

    /**
     * レスポンスが指定のJSONであることを検証する.
     *
     * @param array $expectedAssoc
     * @throws \Codeception\Exception\ModuleException
     */
    public function seeResponseJson(array $expectedAssoc): void
    {
        $rest = $this->getModule('REST');
        $this->assertSame($expectedAssoc, json_decode($rest->grabResponse(), true));
    }

    /**
     * POSTを送信する（JSON版）.
     *
     * @param $url
     * @param array $params
     * @param array $files
     * @throws \Codeception\Exception\ModuleException
     */
    public function sendPost($url, $params = [], $files = []): void
    {
        $lumen = $this->getModule('Lumen');
        $lumen->haveHttpHeader('Content-Type', 'application/json');
        $rest = $this->getModule('REST');
        $rest->send('POST', $url, json_encode($params), $files);
    }

    /**
     * 前のレスポンスからCookieをセットする.
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function setCookieFromResponse()
    {
        $rest = $this->getModule('REST');
        $setCookies = $rest->grabHttpHeader('Set-Cookie', false);

        $lumen = $this->getModule('Lumen');
        foreach ($setCookies as $set) {
            [$key, $val] = $this->parseCookie($set);
            $lumen->setCookie($key, urldecode($val));
        }
    }

    /**
     * 指定した名前のCookieを前のレスポンスからセットする.
     *
     * @param string $cookieName クッキー名
     */
    public function setCookieByNameFromResponse(string $cookieName)
    {
        $rest = $this->getModule('REST');
        $setCookies = $rest->grabHttpHeader('Set-Cookie', false);

        $lumen = $this->getModule('Lumen');
        foreach ($setCookies as $set) {
            [$key, $val] = $this->parseCookie($set);
            if ($key === $cookieName) {
                $lumen->setCookie($key, urldecode($val));
                return;
            }
        }
        $this->assertTrue(false, "Cookie not found. Key=[{$cookieName}]");
    }

    /**
     * テストをスキップする.
     *
     * @param string $message
     */
    public function skipTestExec(string $message = ''): void
    {
        Assert::markTestSkipped($message);
    }

    /**
     * レスポンスデータを取得する.
     *
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     * @return array
     */
    public function grabResponseArray(): array
    {
        $rest = $this->getModule('REST');
        $resp = $rest->grabResponse();
        return Json::decode($resp, true);
    }

    /**
     * domain 層のModelが、厳密に同一であることを検証する.
     *
     * @param \Domain\ModelCompat $expected
     * @param \Domain\ModelCompat $actual
     * @param string $message
     */
    public function assertModelStrictEquals(ModelCompat $expected, ModelCompat $actual, string $message = ''): void
    {
        $this->assertModelStrictEqualsUnit($expected, $actual, $message);
    }

    /**
     * Model の array が、厳密に同一であることを検証する.
     *
     * @param array|\Domain\Model[] $expected
     * @param array|\Domain\Model[] $actual
     * @param string $message エラーメッセージ
     */
    public function assertArrayStrictEquals(array $expected, array $actual, string $message = ''): void
    {
        \PHPUnit\Framework\assertThat($actual, new ArrayStrictEquals($expected), $message);
    }

    /**
     * domain 層のModelが、Snapshot と一致していることを検証する.
     *
     * @param \Domain\Model|iterable $model
     */
    public function assertMatchesModelSnapshot($model): void
    {
        $this->assertMatchesModelSnapshotUnit($model);
    }

    /**
     * 名前を返す.
     *
     * Snapshot で使っている phpunit の Testcase クラスに存在しているメソッド
     *
     * @return string
     */
    public function getName(): string
    {
        return \get_class($this->targetClass);
    }

    /**
     * テストターゲットのクラス名を設定する.
     *
     * NOTE: 本メソッドで登録しないと、スナップショットが正しく動かない
     *
     * @param $class
     */
    public function setTargetClass($class): void
    {
        $this->targetClass = $class;
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param callable|string $callback
     * @param array<string, mixed> $parameters
     * @param null|string $defaultMethod
     * @return int
     */
    public function callArtisanCommand($callback, array $parameters = [], ?string $defaultMethod = null): int
    {
        return $this->getModule('Lumen')->getApplication()->make(Kernel::class)
            ->call($callback, $parameters, $defaultMethod);
    }

    /** {@inheritdoc} */
    protected function getSnapshotDirectory(): string
    {
        return dirname((new ReflectionClass($this->targetClass))->getFileName()) .
            \DIRECTORY_SEPARATOR .
            '__snapshots__';
    }

    /**
     * Cookie文字列をばらす.
     *
     * @param string $cookie
     * @return array
     */
    private function parseCookie(string $cookie): array
    {
        $cookieInfo = explode(';', $cookie);
        return explode('=', $cookieInfo[0], 2);
    }
}
