<?php

namespace Atwx\SilverstripeDataManager;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;

class DataManagerController extends Controller implements PermissionProvider
{
    use DataManagerControllerTrait;

    public function init()
    {
        parent::init();
        $this->templates['index'] = [
            static::class,
            DataManagerController::class,
            'Page'
        ];
        $this->templates['view'] = [
            static::class . '_view',
            DataManagerController::class . '_view',
            'Page'
        ];
        $this->templates['edit'] = [
            static::class . '_edit',
            DataManagerController::class . '_edit',
            'Page'
        ];
        $this->templates['add'] = [
            static::class . '_add',
            static::class . '_edit',
            DataManagerController::class . '_add',
            DataManagerController::class . '_edit',
            'Page'
        ];
    }

    public function handleRequest(HTTPRequest $request): HTTPResponse
    {
        $response = parent::handleRequest($request);
        if(!$this->canView()) {
            $this->pushCurrent();
            return Security::permissionFailure($this, "Bitte loggen Sie sich ein.");
        }
        if ($response->getStatusCode() == 403) {
            // Redirect to login form if permission is denied
            $this->pushCurrent();
            $response = Security::permissionFailure($this, "Diese Aktion ist nicht erlaubt.");
            $response->addHeader('Content-Type', 'text/html');
            return $response;
        }
        return $response;
    }

    public function providePermissions()
    {
        $perms = [
//            "DataManager" => [
//                'name' => _t(__CLASS__ . '.ACCESSALLINTERFACES', 'Access to all data managers'),
//                'category' => _t(Permission::class . '.DATA_ACCESS_CATEGORY', 'Data Manager Access'),
//                'sort' => -100
//            ]
        ];

        // Add any custom DataManager subclasses.
        foreach (ClassInfo::subclassesFor(DataManagerController::class) as $i => $class) {
            if ($class === DataManagerController::class) {
                continue;
            }

            // Remove namespace, only show class name (last element)
            $parts = explode('\\', (string) $class);
            $simpleClass = array_pop($parts);

            $code = 'DATAMANAGER_ACCESS_' . $simpleClass;

            $title = $class;
            $perms[$code] = [
                // Item in permission selection identifying the admin section. Example: Access to 'Files & Images'
                'name' => _t(
                    CMSMain::class . '.ACCESS',
                    "Access to '{title}' ({code})",
                    ['title' => $title, 'code' => $code]
                ),
                'category' => _t(Permission::class . '.DATA_ACCESS_CATEGORY', 'Data Manager Access')
            ];
        }

        return $perms;
    }

    public function Link($action = null)
    {
        $url = $this->getBaseUrl();
        if ($url) {
            $link = Controller::join_links($url, $action);

            // Give extensions the chance to modify by reference
            $this->extend('updateLink', $link, $action);
            return $link;
        }
    }

    public function CurrentUrl()
    {
        return $this->Link(). "?" . $this->CurrentQuery();
    }
}
