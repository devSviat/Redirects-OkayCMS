<?php

namespace Okay\Modules\Sviat\Redirects\Helpers;

use Okay\Core\EntityFactory;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;

class RedirectsExportHelper
{
    private const BATCH_SIZE = 1000;

    private EntityFactory $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function buildCsv(): string
    {
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            return '';
        }

        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'from_url',
            'to_url',
            'name',
            'status',
            'enabled',
            'is_lang',
            'hits',
            'last_hit_at',
            'created_at',
            'updated_at',
        ], ';');

        $redirectsEntity = $this->entityFactory->get(RedirectsEntity::class);
        $page = 1;

        do {
            $rows = $redirectsEntity->find([
                'page' => $page,
                'limit' => self::BATCH_SIZE,
            ]);

            foreach ($rows as $row) {
                fputcsv($stream, [
                    (string) ($row->from_url ?? ''),
                    (string) ($row->to_url ?? ''),
                    (string) ($row->name ?? ''),
                    (string) ($row->status ?? RedirectsEntity::STATUS_301),
                    (int) (!empty($row->enabled)),
                    (int) (!empty($row->is_lang)),
                    (int) ($row->hits ?? 0),
                    (string) ($row->last_hit_at ?? ''),
                    (string) ($row->created_at ?? ''),
                    (string) ($row->updated_at ?? ''),
                ], ';');
            }

            $page++;
            $hasMore = count($rows) === self::BATCH_SIZE;
        } while ($hasMore);

        rewind($stream);
        $csv = (string) stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }
}
