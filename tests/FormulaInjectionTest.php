<?php

namespace Modules\Sviat\Redirects;

use Okay\Core\EntityFactory;
use Okay\Modules\Sviat\Redirects\Backend\Controllers\RedirectsImportTemplateAdmin;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;
use Okay\Modules\Sviat\Redirects\Export\FormulaSafeCsv;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsExportHelper;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsImportHelper;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Адреси й назви редиректів вводить адмін, але CSV відкривають в Excel:
 * комірка, що починається з =, +, - або @, виконується як формула.
 */
#[AllowMockObjectsWithoutExpectations]
class FormulaInjectionTest extends TestCase
{
    private const HYPERLINK = '=HYPERLINK("http://x","y")';

    public function testExportNeutralisesFormulaCells(): void
    {
        $rows = $this->parse($this->exportCsv([
            $this->row(['name' => self::HYPERLINK, 'from_url' => '-old', 'to_url' => '@new']),
        ]));

        $this->assertSame("'" . self::HYPERLINK, $rows[1][2]);
        $this->assertSame("'-old", $rows[1][0]);
        $this->assertSame("'@new", $rows[1][1]);
    }

    public function testControlOldWriterEmitsFormulaRaw(): void
    {
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, [self::HYPERLINK, '-old'], ';', '"', '');
        rewind($handle);

        $this->assertSame([self::HYPERLINK, '-old'], fgetcsv($handle, 0, ';', '"', ''));
    }

    public function testCountersStayNumbers(): void
    {
        $csv = $this->exportCsv([$this->row(['hits' => 12, 'enabled' => 1])]);

        $this->assertStringContainsString(';1;0;12;', $csv);
        $this->assertSame(-7, FormulaSafeCsv::escapeValue(-7));
        $this->assertSame("'-7", FormulaSafeCsv::escapeValue('-7'));
    }

    public function testExportImportRoundTripKeepsValues(): void
    {
        $csv = $this->exportCsv([
            $this->row(['name' => self::HYPERLINK, 'from_url' => 'old-page', 'to_url' => '-new-page']),
            $this->row(['name' => '@sum', 'from_url' => '+plus', 'to_url' => 'plain']),
        ]);

        $added = $this->import($csv);

        $this->assertCount(2, $added);
        $this->assertSame(self::HYPERLINK, $added[0]->name);
        $this->assertSame('-new-page', $added[0]->to_url);
        $this->assertSame('+plus', $added[1]->from_url);
        $this->assertSame('@sum', $added[1]->name);
    }

    public function testImportKeepsOrdinaryLeadingApostrophe(): void
    {
        $added = $this->import("from_url;to_url;name\nold;new;'quoted'\n");

        $this->assertSame("'quoted'", $added[0]->name);
    }

    public function testHeaderlessImportStripsEscapingToo(): void
    {
        $added = $this->import("\"'-old\";new;\"'=x\"\n");

        $this->assertSame('-old', $added[0]->from_url);
        $this->assertSame('=x', $added[0]->name);
    }

    public function testTemplateHasNoFormulaCells(): void
    {
        $controller = (new \ReflectionClass(RedirectsImportTemplateAdmin::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($controller, 'buildTemplateCsv');
        $rows = $this->parse($method->invoke($controller));

        $this->assertCount(4, $rows);
        $this->assertSame('category/$slug', $rows[2][0]);
    }

    private function row(array $fields): \stdClass
    {
        return (object) ($fields + [
            'from_url' => 'a', 'to_url' => 'b', 'name' => 'n', 'status' => '301',
            'enabled' => 1, 'is_lang' => 0, 'hits' => 0,
            'last_hit_at' => '', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
    }

    /** @param list<\stdClass> $rows */
    private function exportCsv(array $rows): string
    {
        $entity = $this->createStub(RedirectsEntity::class);
        $entity->method('find')->willReturn($rows);

        return (new RedirectsExportHelper($this->factory($entity)))->buildCsv();
    }

    /** @return list<\stdClass> */
    private function import(string $csv): array
    {
        $added = [];
        $entity = $this->createStub(RedirectsEntity::class);
        $entity->method('find')->willReturn([]);
        $entity->method('add')->willReturnCallback(function ($row) use (&$added) {
            $added[] = $row;
            return count($added);
        });

        $file = tempnam(sys_get_temp_dir(), 'redirects-csv');
        file_put_contents($file, $csv);
        try {
            (new RedirectsImportHelper($this->factory($entity)))->importFromCsv($file);
        } finally {
            unlink($file);
        }

        return $added;
    }

    private function factory(RedirectsEntity $entity): EntityFactory
    {
        $factory = $this->createStub(EntityFactory::class);
        $factory->method('get')->willReturn($entity);

        return $factory;
    }

    /** @return list<list<string>> */
    private function parse(string $csv): array
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, $csv);
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
            $rows[] = $row;
        }

        return $rows;
    }
}
