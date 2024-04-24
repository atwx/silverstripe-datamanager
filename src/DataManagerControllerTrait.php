<?php

namespace Atwx\SilverstripeDataManager;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\View\ArrayData;

trait DataManagerControllerTrait {
    private static $allowed_actions = array(
        "index",
        "EditForm",
        "edit",
        "add",
        "view",
        "delete",
        "export",
        "duplicate",
    );

    private static $managed_model = null;

    private static $title = 'DataManagerController';

    private static $logo = null;

    public function getBaseUrl()
    {
        return self::config()->get('url_segment');
    }

    public function canView()
    {
        // Default: Any logged-in user can view
        return Security::getCurrentUser();
    }

    public function IsModal()
    {
        // TODO
        return $this->getRequest()->getVar("modal") ? true : false;
    }

    public function getLogo()
    {
        $logo = self::config()->get('logo');
        if($logo) {
            $logoUrl = ModuleResourceLoader::resourceURL($logo);
            return $logoUrl;
        }
    }

    public function index(HTTPRequest $request)
    {
        return [

        ];
    }

    public function Actions()
    {
        $actions = ArrayList::create();
        $actions->push(ArrayData::create([
            "Title" => "Neu",
            "Primary" => true,
            "Link" => $this->Link("add") . "?BackUrl=" . $this->Link(),
            "AccessKey" => "n",
        ]));
        $actions->push(ArrayData::create([
            "Title" => "Export",
            "Target" => "_blank",
            "Link" => $this->Link("export") . "?" . $this->CurrentQuery(),
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
        if (!$model->hasMethod("getDataManagerFilterFields")) {
            return null;
        }
        $fields = $model->getDataManagerFilterFields();
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
        } else {
            $fields = $item->scaffoldFormFields();
        }

        $fields->push(new HiddenField("ID", "ID"));
        $fields->push(new HiddenField("BackURL", "BackURL"));

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
        $form->loadDataFrom([
            "BackURL" => $request->getVar("BackURL"),
        ]);
        if($this->IsModal()) {
            $this->templates['edit'] = [
                get_class($this) . '_edit',
                DataManagerController::class . '_edit',
                DataManagerController::class . '_modal',
            ];
        }
        return [
            "Form" => $form,
            "Title" => singleton($class)->singular_name() . " bearbeiten",
        ];
    }

    public function duplicate(HTTPRequest $request)
    {
        $id = $request->param("ID");
        $class = $this->getManagedModel();
        if ($id) {
            $item = $class::get()->byId($id);
            // Redirect to add with data as params
            $data = $item->toMap();
            unset($data["ID"]);
            unset($data["Created"]);
            unset($data["LastEdited"]);
            unset($data["Segment"]);
            $data["duplicate"] = 1;
            $data["BackURL"] = $request->getVar("BackURL");
            return $this->redirect($this->Link("add") . "?" . http_build_query($data));
        }
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
            $form->loadDataFrom($this->getRequest()->getVars()); //Maybe: From GET
        }
        $form->loadDataFrom($this->getRequest()->getVars());
        $class = $this->getManagedModel();
        if($this->getRequest()->getVar("duplicate")) {
            $title = "Duplizieren: " . singleton($class)->singular_name();
        } else {
            $title = "Neu: " . singleton($class)->singular_name();
        }

        if ($this->getRequest()->getVar("Title")) {
            $title = $this->getRequest()->getVar("Title");
        }
        return [
            "Title" => $title,
            "Form" => $form,
        ];
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
        } else {
            $item = $class::create();
            if ($form instanceof ModelForm) {
                $form->save($item);
            } else {
                $form->saveInto($item);
                $item->write();
            }
        }

        return $this->redirectBack();
    }

    public function getExportItems()
    {
        return $this->getQuery();
    }

    public function getExportFields()
    {
        return singleton($this->getManagedModel())->getDataManagerExportFields();
    }

    public function export(HTTPRequest $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 2;

        $items = $this->getExportItems();
        $fields = $this->getExportFields();

        $col = "A";
        foreach ($fields as $name => $title) {
            $sheet->setCellValue($col . 1, $title);
            $col++;
        }

        foreach ($items as $item) {
            $col = "A";
            if ($item->hasMethod("getDataManagerExportData")) {
                foreach ($item->getDataManagerExportData() as $data) {
                    $sheet->setCellValue($col . $row, $data);
                    $col++;
                }
            } else {
                //ArrayData
                foreach ($fields as $name => $value) {
                    $value = $item->$name;
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
            }
            $row++;
        }

        $class = $this->getManagedModel();
        $className = strtolower(singleton($class)->plural_name());

        $fileName = "export-$className-" . date("Y-m-d") . ".xlsx";

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function getFilterIsSet()
    {
        return $this->CurrentQuery();
    }

    public function hasSessionMessage()
    {
        $session = $this->getRequest()->getSession();
        return $session->get("DataManagerMessage") ? true : false;
    }

    public function setSessionMessage($message, $type)
    {
        $session = $this->getRequest()->getSession();
        $session->set("DataManagerMessage", $message);
        $session->set("DataManagerMessageType", $type);
    }

    public function getSessionMessage()
    {
        $session = $this->getRequest()->getSession();
        $message = $session->get("DataManagerMessage");
        $session->clear("DataManagerMessage");
        return $message;
    }

    public function getSessionMessageType()
    {
        $session = $this->getRequest()->getSession();
        $type = $session->get("DataManagerMessageType");
        $session->clear("DataManagerMessageType");
        return $type;
    }

}
