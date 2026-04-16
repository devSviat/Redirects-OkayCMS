<?php


namespace Okay\Modules\Sviat\Redirects\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;

class RedirectsAdmin extends IndexAdmin
{
    private const TEMPLATE = 'redirects_admin.tpl';
    private const SESSION_LIMIT_KEY = 'sviat_redirects_limit';
    private const DEFAULT_LIMIT = 25;
    private const MIN_LIMIT = 5;
    private const MAX_LIMIT = 100;

    public function fetch(RedirectsEntity $redirectsEntity)
    {
        $page = max(1, (int) $this->request->get('page', 'integer'));
        $limit = $this->resolveLimit();
        $filter = ['page' => $page, 'limit' => $limit];

        $this->handleMassAction($redirectsEntity);
        $this->applySearchFilter($filter);
        $this->applyStateFilter($filter);
        $this->applySortFilter($filter);

        $total = (int) $redirectsEntity->count($filter);
        if ((string) $this->request->get('page') === 'all') {
            $filter['limit'] = max(1, $total);
        }

        $pagesCount = (int) ceil($total / max(1, (int) $filter['limit']));
        $filter['page'] = min(max(1, $page), max(1, $pagesCount));
        $redirects = $total > 0 ? $redirectsEntity->find($filter) : [];

        $this->design->assign('redirects', $redirects);
        $this->design->assign('redirects_count', $total);
        $this->design->assign('pages_count', $pagesCount);
        $this->design->assign('current_page', $filter['page']);
        $this->design->assign('current_limit', $filter['limit']);
        $this->design->assign('sort', $filter['sort'] ?? '');
        $this->response->setContent($this->design->fetch(self::TEMPLATE));
    }

    private function handleMassAction(RedirectsEntity $redirectsEntity): void
    {
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
