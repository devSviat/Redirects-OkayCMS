<?php

namespace Modules\Sviat\Redirects;

use Aura\Sql\ExtendedPdo;
use Okay\Core\Config;
use Okay\Core\EntityFactory;
use Okay\Core\ServiceLocator;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;
use Okay\Modules\Sviat\Redirects\Extensions\FeatureValuesMergeRedirectsExtension;
use PHPUnit\Framework\TestCase;

/**
 * Обробник redirect-правил після злиття значень властивості: пише в таблицю модуля
 * точні 301 і перенаправляє наявне правило з тим самим джерелом, а не дублює його.
 * Працює в транзакції тесту, що відкочується.
 */
class FeatureValuesMergeRedirectsTest extends TestCase
{
    private ExtendedPdo $pdo;
    private string $px;
    private bool $inTransaction = false;

    protected function setUp(): void
    {
        try {
            $this->pdo = ServiceLocator::getInstance()->getService(ExtendedPdo::class);
            $this->pdo->query('SELECT 1');
        } catch (\PDOException $e) {
            $this->markTestSkipped('немає бази: ' . $e->getMessage());
        }
        $prefix = (string) ServiceLocator::getInstance()->getService(Config::class)->get('db_prefix');
        if (!preg_match('/^[a-z0-9_]*$/i', $prefix)) {
            $this->fail('підозрілий префікс таблиць');
        }
        $this->px = $prefix;
        if ($this->pdo->fetchValue("SHOW TABLES LIKE '{$this->px}" . RedirectsEntity::TABLE . "'") === false) {
            $this->markTestSkipped('модуль редиректів не встановлено');
        }

        $this->pdo->beginTransaction();
        $this->inTransaction = true;
    }

    protected function tearDown(): void
    {
        if ($this->inTransaction && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function testStoresExactPermanentRedirectsAndRepointsExistingSource(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->pdo->perform(
            "INSERT INTO {$this->px}" . RedirectsEntity::TABLE . " (name, from_url, to_url, type, is_lang, status, enabled, hits, created_at, updated_at)
             VALUES ('fx', 'catalog/fx-merge/fxtype-old', 'catalog/fx-merge/elsewhere', 'pattern', 1, '302', 0, 7, :now, :now)",
            ['now' => $now]
        );

        $handler = new FeatureValuesMergeRedirectsExtension(ServiceLocator::getInstance()->getService(EntityFactory::class));
        $stored = $handler->storeFeatureValuesMergeRedirects(1, [
            ['from' => 'catalog/fx-merge/fxtype-old', 'to' => 'catalog/fx-merge/fxtype-new'],
            ['from' => 'ru/catalog/fx-merge/fxtype-old', 'to' => 'ru/catalog/fx-merge/fxtype-new'],
        ]);

        self::assertSame(3, $stored, 'до кількості попередніх обробників додаються обидва правила');
        $rows = $this->pdo->fetchAll(
            "SELECT from_url, to_url, type, is_lang, status, enabled, hits FROM {$this->px}" . RedirectsEntity::TABLE . "
             WHERE from_url LIKE '%fx-merge/fxtype-old' ORDER BY from_url"
        );
        self::assertSame([
            ['from_url' => 'catalog/fx-merge/fxtype-old', 'to_url' => 'catalog/fx-merge/fxtype-new', 'type' => 'exact', 'is_lang' => 0, 'status' => '301', 'enabled' => 1, 'hits' => 7],
            ['from_url' => 'ru/catalog/fx-merge/fxtype-old', 'to_url' => 'ru/catalog/fx-merge/fxtype-new', 'type' => 'exact', 'is_lang' => 0, 'status' => '301', 'enabled' => 1, 'hits' => 0],
        ], array_map(static fn (array $r): array => [
            'from_url' => $r['from_url'], 'to_url' => $r['to_url'], 'type' => $r['type'], 'is_lang' => (int) $r['is_lang'],
            'status' => (string) $r['status'], 'enabled' => (int) $r['enabled'], 'hits' => (int) $r['hits'],
        ], $rows));
    }
}
