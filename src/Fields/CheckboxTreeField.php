<?php

namespace Atwx\SilverstripeDataManager\Fields;

use SilverStripe\Forms\FormField;

class CheckboxTreeField extends FormField
{
    public function __construct(
        $name,
        $title = null,
        protected $items = null,
        $value = null,
        $titleField = 'Title',
        $childrenField = 'Children',
    ) {
        parent::__construct($name, $title, $value);
        $this->titleField = $titleField;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function ItemsJSON()
    {
        $items = [];
        foreach ($this->items as $item) {
            $name = $item->hasMethod($this->titleField) ? $item->{$this->titleField}() : $item->$this->titleField;
            $items[] = [
                'value' => $item->ID,
                'name' => $name,
                'children' => $this->getChildren($item),
            ];
        }
        return json_encode($items);
    }

    public function FieldValue()
    {
        if (is_array($this->value)) {
            return json_encode($this->value);
        } else {
            return $this->value;
        }
    }

    public function getChildren($item)
    {
        $children = [];
        foreach ($item->Children() as $child) {
            $children[] = [
                'value' => $child->ID,
                'name' => $child->Title,
                'children' => $this->getChildren($child),
            ];
        }
        return $children;
    }
}
