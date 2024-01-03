<?php

namespace Atwx\SilverstripeDataManager;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
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

class ManageController extends BaseController
{
    private static $allowed_actions = array(
        "EditForm",
        "edit",
        "add",
        "view",
        "delete",
        "export",
    );

    private static $managed_model = null;

    private static $title = 'ManageController';

    public function getBaseUrl() {
        return self::config()->get('url_segment');
    }

    public function Link($action = null) {
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
        return [
        ];
    }

    public function Actions()
    {
        $actions = ArrayList::create();
        $actions->push(ArrayData::create([
            "Title" => "Neu",
            "Link" => $this->Link("add"),
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
        $query = singleton($Model)->filter($query, $this->getRequest());
        return $query;
    }

    public function getItems()
    {
        $list = PaginatedList::create($this->getQuery(), $this->getRequest())->setPageLength(30);
        return $list;
    }

    public function getManagementFields()
    {
        $fieldsList = singleton($this->getManagedModel())->getManagementFields();
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
        if (!$model->hasMethod('getFilterFields')) {
            return null;
        }
        $fields = $model->getFilterFields();
        $actions = FieldList::create(
            FormAction::create("search", "Filtern")
                ->addExtraClass("button--secondary")
        );
        $form = Form::create($this, "FilterForm", $fields, $actions)
            ->addExtraClass("form--filter")
            ->setFormAction($this->Link())
            ->setFormMethod("get");
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

        if ($id) {
            $item = $class::get()->byId($id);
            $form = $item->EditForm($this, "EditForm", [
                new FormAction("save", "Speichern"),
                new LiteralField('Cancel', '<a href="javascript:history.back();" class="uk-button">Abbrechen</a>'),
            ]);
        } else {
            $form = singleton($class)->EditForm($this, "EditForm", [
                new FormAction("save", "Speichern"),
                new LiteralField('Cancel', '<a href="javascript:history.back();" class="uk-button">Abbrechen</a>'),
            ]);
        }

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
            return $this->renderWith([ManageController::class . '_view', BaseController::class], array(
//                "Content" => DBField::create_field("HTMLText", "<h1>Anzeigen</h1>"),
                "Content" => $item,
                "Title" => $item->Title(),
            ));
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
        return $this->renderWith([ManageController::class . '_edit', BaseController::class], array(
//            "Content" => DBField::create_field("HTMLText", "<h1>Bearbeiten</h1>"),
            "Form" => $form,
            "Title" => singleton($class)->singular_name() . " bearbeiten",
        ));
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
        return $this->renderWith([ManageController::class . '_edit', BaseController::class], array(
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

}
