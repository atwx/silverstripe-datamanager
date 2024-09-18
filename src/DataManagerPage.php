<?php

namespace Atwx\SilverstripeDataManager;

use Page;

if (!class_exists(Page::class)) {
    return;
}

class DataManagerPage extends Page {
    private static $table_name = 'DataManagerPage';
}
