<?php


namespace Okay\Modules\Sviat\Redirects;

use Okay\Core\EntityFactory;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Request;
use Okay\Modules\Sviat\Redirects\Extensions\RedirectsExtension;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsExportHelper;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsImportHelper;

return [
    RedirectsExtension::class => [
        'class' => RedirectsExtension::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
        ],
    ],
    RedirectsImportHelper::class => [
        'class' => RedirectsImportHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
    ],
    RedirectsExportHelper::class => [
        'class' => RedirectsExportHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
    ],
];
