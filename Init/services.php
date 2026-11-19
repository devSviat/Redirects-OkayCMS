<?php

namespace Okay\Modules\Sviat\Redirects;

use Okay\Core\EntityFactory;
use Okay\Core\ManagerMenu;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Request;
use Okay\Modules\Sviat\Redirects\Extensions\RedirectsBackendExtender;
use Okay\Modules\Sviat\Redirects\Extensions\RedirectsExtension;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsExportHelper;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsImportHelper;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsReincarnationHelper;

return [
    RedirectsBackendExtender::class => [
        'class' => RedirectsBackendExtender::class,
        'arguments' => [
            new SR(ManagerMenu::class),
            new SR(RedirectsReincarnationHelper::class),
        ],
    ],
    RedirectsExtension::class => [
        'class' => RedirectsExtension::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
            new SR(RedirectsReincarnationHelper::class),
        ],
    ],
    RedirectsReincarnationHelper::class => [
        'class' => RedirectsReincarnationHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
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
