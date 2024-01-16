<?php

namespace Atwx\SilverstripeDataManager;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\View\ArrayData;

class DataManagerController extends Controller implements PermissionProvider
{
    private static $allowed_actions = array(
        "index",
        "EditForm",
        "edit",
        "add",
        "view",
        "delete",
        "export",
    );

    private static $managed_model = null;

    private static $title = 'DataManagerController';

    private static $logo = null;

    public function init()
    {
        parent::init();
        $this->templates['view'] = [
            get_class($this) . '_view',
            DataManagerController::class . '_view',
            'Page'
        ];
        $this->templates['edit'] = [
            get_class($this) . '_edit',
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
            return Security::permissionFailure($this, "Bitte loggen Sie sich ein.");
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
            $parts = explode('\\', $class);
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

    public function getBaseUrl()
    {
        return self::config()->get('url_segment');
    }

    public function canView()
    {
        // Default: Any logged-in user can view
        return Security::getCurrentUser();
    }

    public function getLogo()
    {
        $logo = self::config()->get('logo');
        if($logo) {
            $logoUrl = ModuleResourceLoader::resourceURL($logo);
            return $logoUrl;
        }
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

    public function index(HTTPRequest $request)
    {
        return $this->renderWith([self::class, 'Page']);
    }

    public function Actions()
    {
        $actions = ArrayList::create();
        $actions->push(ArrayData::create([
            "Title" => "Neu",
            "Link" => $this->Link("add"),
            "AccessKey" => "n",
        ]));
        return $actions;
    }

    public function Title()
    {
        return self::config()->get('title');
    }

    public function Description()
    {
        return self::config()->get('description');
    }

    public function getManagedModel()
    {
        return self::config()->get('managed_model');
    }

    public function CurrentQuery()
    {
        $vars = $this->getRequest()->getVars();
        if (isset($vars['SecurityID'])) {
            unset($vars['SecurityID']);
        }
        if (isset($vars['action_search'])) {
            unset($vars['action_search']);
        }
        return http_build_query($vars);
    }

    public function getQuery()
    {
        $Model = self::config()->get('managed_model');
        $query = DataObject::get($Model);
        if (singleton($Model)->hasMethod("buildDataManagerFilter")) {
            $query = singleton($Model)->buildDataManagerFilter($query, $this->getRequest());
        }
        return $query;
    }

    public function getItems()
    {
        $list = PaginatedList::create($this->getQuery(), $this->getRequest())->setPageLength(30);
        return $list;
    }

    public function getDataManagerFields()
    {
        $fieldsList = singleton($this->getManagedModel())->getDataManagerFields();
        $fields = ArrayList::create();
        foreach ($fieldsList as $name => $title) {
            $fields->push(ArrayData::create([
                "Field" => $name,
                "Title" => $title,
            ]));
        }
        return $fields;
    }

    public function FilterForm()
    {
        $model = singleton($this->ManagedModel);
        $fields = $model->getDataManagerFilterFields();
        if (!$fields) {
            return null;
        }
        $actions = FieldList::create(
            FormAction::create("search", "Filtern")
                ->addExtraClass("button--secondary")
        );
        $form = Form::create($this, "FilterForm", $fields, $actions)
            ->addExtraClass("form--filter")
            ->setFormAction($this->Link())
            ->setFormMethod("get");
        $form->setTemplate('Atwx/SilverstripeDataManager/Includes/FilterForm');
        $form->loadDataFrom($this->getRequest()->getVars());
        return $form;
    }

    public function EditForm()
    {
        $request = $this->getRequest();
        $object_class = "";
        if ($request->isGET()) {
            $object_class = $this->getManagedModel();
            $id = $this->getRequest()->param("ID");
        } else {
            if ($this->getRequest()->postVar("EditClass")) {
                $object_class = $this->getRequest()->postVar("EditClass");
                $id = $this->getRequest()->postVar("ID");
            }
        }

        if ($object_class) {
            $class = $object_class;
        } else {
            $class = $this->getManagedModel();
        }

        if($id) {
            $item = $class::get()->byId($id);
        } else {
            $item = singleton($class);
        }

        if ($item->hasMethod("dataManagerFormFields")) {
            $fields = $item->dataManagerFormFields();
            $fields->push(new HiddenField("ID", "ID"));
        } else {
            $fields = $item->scaffoldFormFields();
        }

        $form = Form::create($this, "EditForm", $fields, new FieldList([
            new FormAction("save", "Speichern"),
            new LiteralField('Cancel', '<a href="javascript:history.back();" class="uk-button">Abbrechen</a>'),
        ]));

        // TODO: Validator

        if ($object_class) {
            $form->Fields()->push(new HiddenField("EditClass", "EditClass", $object_class));
        }

        return $form;
    }

    public function view(HTTPRequest $request)
    {
        $id = $request->param("ID");
        $class = $this->getManagedModel();
        if ($id) {
            $item = $class::get()->byId($id);
//            $templates = [get_class($this) . '_view', DataManagerController::class . '_view', 'Page'];
            return [
                "Item" => $item,
                "Content" => $item,
                "Title" => $item->Title(),
            ];
        }
    }

    public function edit(HTTPRequest $request)
    {
        $form = $this->EditForm();
        $id = $request->param("ID");
        $class = $this->getManagedModel();
        if ($id) {
            $item = $class::get()->byId($id);
            $form->loadDataFrom($item);
        }
        return [
            "Form" => $form,
            "Title" => singleton($class)->singular_name() . " bearbeiten",
        ];
    }

    public function delete(HTTPRequest $request)
    {
        $id = $request->param("ID");
        $class = $this->getManagedModel();
        if ($id) {
            $item = $class::get()->byId($id);
            $item->delete();
        }
        return $this->redirectBack();
    }

    public function add()
    {
        $form = $this->EditForm();
        if ($id = $this->getRequest()->param("ID")) { //Sub-Object
//            print_r($this->getRequest()->getVars());
            $form->loadDataFrom($this->getRequest()->getVars()); //Maybe: From GET
        }
        $class = $this->getManagedModel();
        $title = "Neu: " . singleton($class)->singular_name();
        if ($this->getRequest()->getVar("Title")) {
            $title = $this->getRequest()->getVar("Title");
        }
        return $this->renderWith([DataManagerController::class . '_edit', 'Page'], array(
            "Title" => $title,
            "Form" => $form
        ));
    }

    public function save($data, $form)
    {
        $class = $this->getManagedModel();

        if (isset($data["ID"]) && $data["ID"]) {
            //Save
            $item = $class::get()->byID($data["ID"]);
            if ($form instanceof ModelForm) {
                $form->save($item);
            } else {
                $form->saveInto($item);
                $item->write();
            }

            return $this->redirect($this->Link());
        } else {
            $item = $class::create();
            if ($form instanceof ModelForm) {
                $form->save($item);
            } else {
                $form->saveInto($item);
                $item->write();
            }
            return $this->redirect($this->Link());
        }
    }

    public function export(HTTPRequest $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 2;

        $items = $this->getItems();

        $fields = singleton($this->getManagedModel())->getExportFields();

        $col = "A";
        foreach ($fields as $name => $title) {
            $sheet->setCellValue($col . 1, $title);
            $col++;
        }

        foreach ($items as $item) {
            $col = "A";
            foreach ($item->getExportData() as $data) {
                $sheet->setCellValue($col . $row, $data);
                $col++;
            }
            $row++;
        }

        $fileName = "export-" . date("Y-m-d") . ".xlsx";

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function getFilterIsSet()
    {
        return isset($_GET['Query']);
    }

}
