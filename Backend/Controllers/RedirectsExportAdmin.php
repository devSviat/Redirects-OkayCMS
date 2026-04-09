<?php

namespace Okay\Modules\Sviat\Redirects\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsExportHelper;

class RedirectsExportAdmin extends IndexAdmin
{
    public function fetch(RedirectsExportHelper $redirectsExportHelper)
    {
        $filename = 'redirects_export_' . date('Y-m-d_His') . '.csv';
        $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->response->setContent($redirectsExportHelper->buildCsv(), RESPONSE_TEXT);
    }
}
