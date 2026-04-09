<?php


namespace Okay\Modules\Sviat\Redirects\Init;


use Okay\Admin\Helpers\BackendProductsHelper;
use Okay\Helpers\MainHelper;
use Okay\Core\Modules\EntityField;
use Okay\Core\Modules\AbstractInit;
use Okay\Modules\Sviat\Redirects\Entities\RedirectsEntity;
use Okay\Modules\Sviat\Redirects\Extensions\RedirectsExtension;

class Init extends AbstractInit
{
    private const PERMISSION = 'sviat__redirects';

    public function install()
    {
        $this->setBackendMainController('RedirectsAdmin');
        $this->migrateEntityTable(RedirectsEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('name'))->setTypeText()->setNullable(),
            (new EntityField('from_url'))->setTypeVarchar(1024, false)->setIndexUnique(),
            (new EntityField('to_url'))->setTypeVarchar(1024, false),
            (new EntityField('type'))->setTypeEnum(['exact', 'pattern'])->setDefault('exact')->setIndex(),
            (new EntityField('is_lang'))->setTypeTinyInt(1, true)->setDefault(0)->setIndex(),
            (new EntityField('status'))->setTypeEnum(['301','302'])->setDefault('301')->setIndex(),
            (new EntityField('enabled'))->setTypeTinyInt(1, true)->setDefault(1)->setIndex(),
            (new EntityField('hits'))->setTypeInt(11, false)->setDefault(0),
            (new EntityField('created_at'))->setTypeDatetime(true),
            (new EntityField('updated_at'))->setTypeDatetime(true)->setIndex(),
            (new EntityField('last_hit_at'))->setTypeDatetime(true),
        ]);
    }

    public function init()
    {
        $this->registerBackendController('RedirectsAdmin');
        $this->addBackendControllerPermission('RedirectsAdmin', self::PERMISSION);

        $this->registerBackendController('RedirectAdmin');
        $this->addBackendControllerPermission('RedirectAdmin', self::PERMISSION);

        $this->registerBackendController('RedirectsImportAdmin');
        $this->addBackendControllerPermission('RedirectsImportAdmin', self::PERMISSION);

        $this->registerBackendController('RedirectsImportTemplateAdmin');
        $this->addBackendControllerPermission('RedirectsImportTemplateAdmin', self::PERMISSION);

        $this->registerBackendController('RedirectsExportAdmin');
        $this->addBackendControllerPermission('RedirectsExportAdmin', self::PERMISSION);

        $this->extendBackendMenu('left_seo', [
            'sviat_redirects__menu_title' => [
                'RedirectsAdmin',
                'RedirectAdmin',
                'RedirectsImportAdmin',
                'RedirectsImportTemplateAdmin',
                'RedirectsExportAdmin',
            ],
        ]);

        $this->registerQueueExtension(
            [MainHelper::class, 'setDesignDataProcedure'],
            [RedirectsExtension::class, 'redirect']
        );

        $this->registerQueueExtension(
            ['class' => BackendProductsHelper::class, 'method' => 'delete'],
            ['class' => RedirectsExtension::class, 'method' => 'createProductDeleteRedirects']
        );

        $this->extendUpdateObject(
            'Sviat.Redirects.RedirectsEntity',
            self::PERMISSION,
            RedirectsEntity::class
        );
    }
}
