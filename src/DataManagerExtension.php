<?php
namespace Atwx\SilverstripeDataManager;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Security\Permission;
use SilverStripe\View\ArrayData;

class DataManagerExtension extends Extension
{
//    public function canView($member = null)
//    {
//        return true;
//    }
//
//    public function canEdit($member = null)
//    {
//        return Permission::check("ADMIN", "any", $member);
//    }
//
//    public function canDelete($member = null)
//    {
//        return Permission::check("ADMIN", "any", $member);
//    }
//
//    public function canCreate($member = null, $context = [])
//    {
//        return Permission::check("ADMIN", "any", $member);
//    }

    public function Title()
    {
        if($this->owner->dbObject("Title")->exists()) {
            return $this->owner->dbObject("Title")->getValue();
        }
        return "[$this->owner->ClassName: $this->ID]";
    }

    public function getManagementFields()
    {
        return $this->owner->summaryFields();
    }

    public function getExportFields()
    {
        return $this->owner->exportFields(); // TODO: Add export fields
    }

    public function getFilterFields()
    {
        return null;
    }

    public function filter($query, $request)
    {
        return $query;
    }

    public function getManagementData()
    {
        $data = new ArrayList();
        foreach ($this->owner->getManagementFields() as $name => $title) {
            $data->push(ArrayData::create([
                "Value" => $this->getColumnContent($name), // TODO: Casting
            ]));
        }
        return $data;
    }

    public function getExportData()
    {
        $data = [];
        foreach ($this->owner->getExportFields() as $name => $title) {
            $data[] = $this->owner->getColumnContent($name);
        }
        return $data;
    }

    /**
     * HTML for the column, content of the <td> element.
     *
     * @param GridField $gridField
     * @param DataObject $record Record displayed in this row
     * @param string $columnName
     * @return string HTML for the column. Return NULL to skip.
     */
    public function getColumnContent($fieldName)
    {
        $record = $this->owner;

        if ($record->hasMethod('relField')) {
            return $record->relField($fieldName);
        }

        if ($record->hasMethod($fieldName)) {
            return $record->$fieldName();
        }

        return $record->$fieldName;
    }

    function EditForm($controller, $name, $action)
    {
        if ($this->owner->hasMethod('getCustomEditForm')) {
            return $this->owner->getCustomEditForm($controller, $name, $action);
        }

        $fields = $this->owner->EditFormFields();
        $actions = new FieldList(
            $action
        );
        $required = $this->getValidator();
        $form = new Form(
            $controller, // the Controller to render this form on
            $name, // name of the method that returns this form on the controller
            $fields, // list of FormField instances
            $actions, // list of FormAction instances
            $required // optional use of RequiredFields object
        );
        return $form;
    }

    public function EditFormFields()
    {
        $fields = $this->owner->scaffoldFormFields();
        $fields->push(new HiddenField("ID", "ID"));

        return $fields;
    }

    public function getValidator()
    {
        $required = new RequiredFields(array(
        ));
        return $required;
    }

    /**
     * Stub
     * @return DBHTMLText
     */
    public function forTemplate()
    {
        return $this->owner->renderWith([$this->owner->ClassName, self::class]);
    }
}
