<?php

namespace Okay\Modules\Sviat\Redirects\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsImportHelper;

class RedirectsImportAdmin extends IndexAdmin
{
    private const TEMPLATE = 'redirects_import_admin.tpl';

    public function fetch(RedirectsImportHelper $redirectsImportHelper)
    {
        if ($this->request->method('post')) {
            $file = $this->request->files('csv_file');
            $updateExisting = !empty($this->request->post('update_existing', 'boolean'));
            $this->design->assign('update_existing', (int) $updateExisting);
            if (empty($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $this->design->assign('message_error', 'import_upload_error');
            } else {
                $report = $redirectsImportHelper->importFromCsv((string) ($file['tmp_name'] ?? ''), $updateExisting);
                $this->design->assign('import_report', $report);
                $this->design->assign('message_success', 'import_done');
            }
        }

        $this->response->setContent($this->design->fetch(self::TEMPLATE));
    }
}
