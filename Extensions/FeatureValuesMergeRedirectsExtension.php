<?php

namespace Okay\Modules\Sviat\Redirects\Extensions;

use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;

/**
 * Зберігає 301 для адрес фільтрів, що зникли після злиття значень властивості
 * (BackendFeaturesValuesMergeHelper::storeRedirects). Виконується в транзакції злиття,
 * тож невдача тут відкочує й саме злиття.
 */
class FeatureValuesMergeRedirectsExtension implements ExtensionInterface
{
    public function __construct(private EntityFactory $entityFactory)
    {
    }

    /**
     * @param int $stored скільки правил уже зберегли попередні обробники
     * @param list<array{from: string, to: string}> $redirects
     * @return int $stored плюс збережені тут; наявне правило з тим самим джерелом переспрямовується
     */
    public function storeFeatureValuesMergeRedirects($stored, array $redirects): int
    {
        /** @var RedirectsEntity $redirectsEntity */
        $redirectsEntity = $this->entityFactory->get(RedirectsEntity::class);
        $now = date('Y-m-d H:i:s');

        foreach ($redirects as $redirect) {
            $from = ltrim(strtolower(trim($redirect['from'])), '/');
            $to = ltrim(strtolower(trim($redirect['to'])), '/');
            $rule = [
                'to_url' => $to,
                'type' => RedirectsEntity::TYPE_EXACT,
                'is_lang' => 0,
                'status' => RedirectsEntity::STATUS_301,
                'enabled' => 1,
                'updated_at' => $now,
            ];

            $existing = $redirectsEntity->findOne(['from_url' => $from]);
            if (!empty($existing->id)) {
                $saved = $redirectsEntity->update((int)$existing->id, $rule);
            } else {
                $saved = $redirectsEntity->add($rule + [
                    'name' => 'Feature values merge: ' . $from,
                    'from_url' => $from,
                    'hits' => 0,
                    'created_at' => $now,
                ]);
            }

            if ($saved) {
                $stored++;
            }
        }

        return (int)$stored;
    }
}
