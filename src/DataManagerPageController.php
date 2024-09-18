<?php

namespace Atwx\SilverstripeDataManager;

use PageController;
use SilverStripe\Control\HTTPRequest;

if (!class_exists(PageController::class)) {
    return;
}

class DataManagerPageController extends PageController
{
    use DataManagerControllerTrait;

    public function index(HTTPRequest $request)
    {
        return [
            'Title' => $this->Title,
        ];
    }

}
