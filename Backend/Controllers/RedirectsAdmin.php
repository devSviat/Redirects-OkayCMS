<?php

namespace Okay\Modules\Sviat\Redirects\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsReincarnationHelper;

class RedirectsAdmin extends IndexAdmin
{
    private const TEMPLATE = 'redirects_admin.tpl';
    private const SESSION_LIMIT_KEY = 'sviat_redirects_limit';
    private const DEFAULT_LIMIT = 25;
    private const MIN_LIMIT = 5;
    private const MAX_LIMIT = 100;

    public function fetch(
        RedirectsEntity $redirectsEntity,
        RedirectsReincarnationHelper $reincarnationHelper
    ) {
        if ($this->handleReincarnationAction($reincarnationHelper)) {
            return;
        }

        $page = max(1, (int) $this->request->get('page', 'integer'));
        $limit = $this->resolveLimit();
        $filter = ['page' => $page, 'limit' => $limit];

        $this->handleMassAction($redirectsEntity, $reincarnationHelper);
        $this->applySearchFilter($filter);
        $this->applyStateFilter($filter);
        $this->applySortFilter($filter);

        $total = (int) $redirectsEntity->count($filter);
        $showAll = (string) $this->request->get('page') === 'all';
        if ($showAll) {
            $filter['limit'] = max(1, $total);
        }

        $pagesCount = (int) ceil($total / max(1, (int) $filter['limit']));
        $filter['page'] = min(max(1, $page), max(1, $pagesCount));

        $matches = $reincarnationHelper->getMatchDetails();
        $redirects = $this->loadRedirectsWithReincarnatedFirst(
            $redirectsEntity,
            $filter,
            $total,
            $matches,
            $showAll
        );

        $selectedFound = 0;
        foreach ($redirects as $redirect) {
            if (!empty($redirect->reincarnation_found)) {
                $selectedFound++;
            }
        }

        $this->design->assign('redirects', $redirects);
        $this->design->assign('redirects_count', $total);
        $this->design->assign('pages_count', $pagesCount);
        $this->design->assign('current_page', $filter['page']);
        $this->design->assign('current_limit', $filter['limit']);
        $this->design->assign('sort', $filter['sort'] ?? '');
        $this->design->assign('reincarnation_status', $reincarnationHelper->getStatus());
        $this->design->assign('reincarnation_selected_count', $selectedFound);
        $this->response->setContent($this->design->fetch(self::TEMPLATE));
    }

    private function handleReincarnationAction(
        RedirectsReincarnationHelper $reincarnationHelper
    ): bool {
        if (!$this->request->method('post')) {
            return false;
        }

        if ((string) $this->request->post('reincarnation_action') !== 'scan') {
            return false;
        }

        $report = $reincarnationHelper->scan(true);
        $this->response->setContent(
            json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            RESPONSE_TEXT
        );

        return true;
    }

    private function loadRedirectsWithReincarnatedFirst(
        RedirectsEntity $redirectsEntity,
        array $filter,
        int $total,
        array $matches,
        bool $showAll
    ): array {
        if ($total <= 0) {
            return [];
        }

        if (empty($matches)) {
            return $redirectsEntity->find($filter);
        }

        // A stable PHP partition keeps the selected database sorting inside
        // both groups while always lifting reincarnated rows to the top.
        $allFilter = $filter;
        $allFilter['page'] = 1;
        $allFilter['limit'] = max(1, $total);
        $allRedirects = $redirectsEntity->find($allFilter);

        $found = [];
        $regular = [];
        foreach ($allRedirects as $redirect) {
            if (!is_object($redirect)) {
                continue;
            }

            $redirectId = (int) ($redirect->id ?? 0);
            if ($redirectId > 0 && isset($matches[$redirectId])) {
                $match = $matches[$redirectId];
                $redirect->reincarnation_found = 1;
                $redirect->reincarnation_product_id = (int) ($match['product_id'] ?? 0);
                $redirect->reincarnation_product_sku = (string) ($match['sku'] ?? '');
                $found[] = $redirect;
            } else {
                $redirect->reincarnation_found = 0;
                $redirect->reincarnation_product_id = 0;
                $redirect->reincarnation_product_sku = '';
                $regular[] = $redirect;
            }
        }

        $ordered = array_merge($found, $regular);
        if ($showAll) {
            return $ordered;
        }

        $offset = (max(1, (int) $filter['page']) - 1) * max(1, (int) $filter['limit']);

        return array_slice($ordered, $offset, max(1, (int) $filter['limit']));
    }

    private function handleMassAction(
        RedirectsEntity $redirectsEntity,
        RedirectsReincarnationHelper $reincarnationHelper
    ): void {
        if (!$this->request->method('post')) {
            return;
        }

        $ids = array_map('intval', (array) $this->request->post('check'));
        $ids = array_values(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $action = (string) $this->request->post('action');
        if ($action === 'enable') {
            $redirectsEntity->update($ids, ['enabled' => 1]);
            return;
        }
        if ($action === 'disable') {
            $redirectsEntity->update($ids, ['enabled' => 0]);
            return;
        }
        if ($action === 'delete') {
            $redirectsEntity->delete($ids);
            $reincarnationHelper->forgetRedirects($ids);
            return;
        }
        if ($action === 'status_301') {
            $redirectsEntity->update($ids, ['status' => RedirectsEntity::STATUS_301]);
            return;
        }
        if ($action === 'status_302') {
            $redirectsEntity->update($ids, ['status' => RedirectsEntity::STATUS_302]);
        }
    }

    private function applySearchFilter(array &$filter): void
    {
        $keyword = trim((string) $this->request->get('keyword'));
        if ($keyword === '') {
            return;
        }

        $filter['keyword'] = $keyword;
        $this->design->assign('keyword', $keyword);
    }

    private function applyStateFilter(array &$filter): void
    {
        $state = (string) $this->request->get('filter');
        if ($state === '') {
            return;
        }

        $this->design->assign('filter', $state);
        if (in_array($state, RedirectsEntity::STATUSES, true)) {
            $filter['status'] = $state;
            return;
        }

        if ($state === 'enabled') {
            $filter['enabled'] = 1;
            return;
        }

        if ($state === 'disabled') {
            $filter['disabled'] = 1;
        }
    }

    private function applySortFilter(array &$filter): void
    {
        $sort = (string) $this->request->get('sort', 'string');
        if ($sort === '') {
            return;
        }

        $allowed = [
            'name',
            'name_desc',
            'from_url',
            'from_url_desc',
            'to_url',
            'to_url_desc',
            'hits',
            'hits_desc',
            'status',
            'status_desc',
            'enabled',
            'enabled_desc',
            'created_at',
            'created_at_desc',
            'updated_at',
            'updated_at_desc',
        ];

        if (in_array($sort, $allowed, true)) {
            $filter['sort'] = $sort;
        }
    }

    private function resolveLimit(): int
    {
        $requested = (int) $this->request->get('limit', 'integer');
        if ($requested > 0) {
            $normalized = max(self::MIN_LIMIT, min(self::MAX_LIMIT, $requested));
            $_SESSION[self::SESSION_LIMIT_KEY] = $normalized;
            return $normalized;
        }

        if (!empty($_SESSION[self::SESSION_LIMIT_KEY])) {
            return max(self::MIN_LIMIT, min(self::MAX_LIMIT, (int) $_SESSION[self::SESSION_LIMIT_KEY]));
        }

        return self::DEFAULT_LIMIT;
    }
}
