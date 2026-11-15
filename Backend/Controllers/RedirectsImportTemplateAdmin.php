<?php

namespace Okay\Modules\Sviat\Redirects\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\Redirects\Export\FormulaSafeCsv;

class RedirectsImportTemplateAdmin extends IndexAdmin
{
    public function fetch()
    {
        $filename = 'redirects_import_template.csv';
        $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->response->setContent($this->buildTemplateCsv(), RESPONSE_TEXT);
    }

    private function buildTemplateCsv(): string
    {
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            return '';
        }

        fwrite($stream, "\xEF\xBB\xBF");
        FormulaSafeCsv::putRow($stream, ['from_url', 'to_url', 'name', 'status', 'enabled', 'is_lang', 'type'], ';');
        FormulaSafeCsv::putRow($stream, ['old-page', 'new-page', 'Example redirect', '301', '1', '0', 'exact'], ';');
        FormulaSafeCsv::putRow($stream, ['category/$slug', 'products/$slug', 'Pattern example', '301', '1', '0', 'pattern'], ';');
        FormulaSafeCsv::putRow($stream, ['old-category/product-1', 'catalog/new-category', 'Multilingual example', '301', '1', '1', 'exact'], ';');

        rewind($stream);
        $csv = (string) stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }
}
