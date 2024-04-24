<?php

namespace Atwx\SilverstripeDataManager;

use PageController;
use SilverStripe\Control\HTTPRequest;

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
