<?php


namespace Okay\Modules\Sviat\Redirects\Backend\Controllers;


use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;

class RedirectAdmin extends IndexAdmin
{
    private const TEMPLATE = 'redirect_admin.tpl';

    public function fetch(RedirectsEntity $redirectsEntity)
    {
        $redirect = new \stdClass();

        if ($this->request->method('post')) {
            $redirect = $this->hydrateRedirectFromRequest();

            $error = $this->validateRedirect($redirect);
            if ($error === '' && $this->isFromUrlUsed($redirectsEntity, $redirect)) {
                $error = 'used_url';
            }
            if ($error !== '') {
                $this->design->assign('message_error', $error);
            } else {
                $this->persistRedirect($redirectsEntity, $redirect);
                $this->postRedirectGet->redirect();
            }
        } else {
            $redirect = $this->loadRedirectById($redirectsEntity);
        }

        $this->design->assign('redirect', $redirect);
        $this->response->setContent($this->design->fetch(self::TEMPLATE));
    }

    private function loadRedirectById(RedirectsEntity $redirectsEntity)
    {
        $id = (int) $this->request->get('id', 'integer');
        if ($id <= 0) {
            return new \stdClass();
        }

        return (object) ($redirectsEntity->get($id) ?? []);
    }

    private function hydrateRedirectFromRequest()
    {
        $redirect = new \stdClass();
        $redirect->id = (int) $this->request->post('id', 'integer');
        $redirect->name = trim((string) $this->request->post('name'));
        $redirect->from_url = $this->normalizePath($this->request->post('from_url'));
        $redirect->to_url = $this->normalizePath($this->request->post('to_url'));
        $redirect->type = $this->normalizeType($this->request->post('type'));
        $redirect->is_lang = (int) !empty($this->request->post('is_lang'));
        $redirect->enabled = (int) !empty($this->request->post('enabled'));
        $redirect->status = $this->normalizeStatus($this->request->post('status'));

        return $redirect;
    }

    private function validateRedirect(\stdClass $redirect): string
    {
        if ($redirect->from_url === '') {
            return 'empty_url_from';
        }
        if ($redirect->to_url === '') {
            return 'empty_url_to';
        }

        return '';
    }

    private function isFromUrlUsed(RedirectsEntity $redirectsEntity, \stdClass $redirect): bool
    {
        $existing = $redirectsEntity->find([
            'from_url' => $redirect->from_url,
            'limit' => 10,
        ]);

        foreach ($existing as $item) {
            if ((int) ($item->id ?? 0) !== (int) ($redirect->id ?? 0)) {
                return true;
            }
        }

        return false;
    }

    private function persistRedirect(RedirectsEntity $redirectsEntity, \stdClass $redirect): void
    {
        $now = date('Y-m-d H:i:s');
        if ($redirect->name === '') {
            $redirect->name = $redirect->from_url;
        }

        if ($redirect->id > 0) {
            $redirect->updated_at = $now;
            $redirectsEntity->update($redirect->id, $redirect);
            $this->postRedirectGet->storeMessageSuccess('updated');
            return;
        }

        $redirect->created_at = $now;
        $redirect->updated_at = $now;
        $newId = (int) $redirectsEntity->add($redirect);
        $this->postRedirectGet->storeMessageSuccess('added');
        $this->postRedirectGet->storeNewEntityId($newId);
    }

    private function normalizePath($value): string
    {
        return ltrim(trim((string) $value), '/');
    }

    private function normalizeStatus($status): string
    {
        $status = (string) $status;
        if (in_array($status, RedirectsEntity::STATUSES, true)) {
            return $status;
        }

        return RedirectsEntity::STATUS_301;
    }

    private function normalizeType($type): string
    {
        $type = (string) $type;
        if (in_array($type, RedirectsEntity::TYPES, true)) {
            return $type;
        }

        return RedirectsEntity::TYPE_EXACT;
    }
}
