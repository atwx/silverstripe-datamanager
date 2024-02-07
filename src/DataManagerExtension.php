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
        if($this->owner->dbObject("Title")) {
            return $this->owner->dbObject("Title")->getValue();
        }
        return "[$this->owner->ClassName: $this->owner->ID]";
    }

    public function getDataManagerFields()
    {
        if($this->owner->hasMethod('dataManagerFields')) {
            return $this->owner->dataManagerFields();
        }
        return $this->owner->summaryFields();
    }

    public function getDataManagerData()
    {
        $data = new ArrayList();
        foreach ($this->owner->getDataManagerFields() as $name => $title) {
            $data->push(ArrayData::create([
                "Value" => $this->getColumnContent($name), // TODO: Casting
            ]));
        }
        return $data;
    }

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

    public function getExportFields()
    {
        return $this->owner->exportFields(); // TODO: Add export fields
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
     * Stub
     * @return DBHTMLText
     */
    public function forTemplate()
    {
        return $this->owner->renderWith([$this->owner->ClassName, self::class]);
    }
}
