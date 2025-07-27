<?php
namespace Atwx\SilverstripeDataManager;

use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\ArrayData;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\FieldType\DBHTMLText;

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

    public function getDataManagerExportFields()
    {
        if($this->owner->hasMethod('dataManagerExportFields')) {
            return $this->owner->dataManagerExportFields();
        }
        return $this->getDataManagerFields();
    }

    public function getDataManagerExportData()
    {
        $data = [];
        foreach ($this->owner->getDataManagerExportFields() as $name => $title) {
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
