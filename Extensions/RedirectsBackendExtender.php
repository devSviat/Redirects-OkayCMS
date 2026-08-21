<?php

namespace Okay\Modules\Sviat\Redirects\Extensions;

use Okay\Core\ManagerMenu;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Modules\Sviat\Redirects\Helpers\RedirectsReincarnationHelper;

class RedirectsBackendExtender implements ExtensionInterface
{
    private const MENU_ITEM_KEY = 'sviat_redirects__menu_title';

    /** @var ManagerMenu */
    private $managerMenu;

    /** @var RedirectsReincarnationHelper */
    private $reincarnationHelper;

    public function __construct(
        ManagerMenu $managerMenu,
        RedirectsReincarnationHelper $reincarnationHelper
    ) {
        $this->managerMenu = $managerMenu;
        $this->reincarnationHelper = $reincarnationHelper;
    }

    /**
     * Adds the native OkayCMS counter to the Redirects item in the left menu.
     *
     * The method is connected to BackendMainHelper::evensCounters().
     *
     * @param mixed $output
     */
    public function setReincarnationMenuCounter($output = null): void
    {
        try {
            $count = $this->reincarnationHelper->getFoundCount();
        } catch (\Throwable $e) {
            return;
        }

        if ($count <= 0) {
            return;
        }

        $this->managerMenu->addCounter(self::MENU_ITEM_KEY, $count);
    }
}
