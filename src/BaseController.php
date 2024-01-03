<?php

namespace Atwx\SilverstripeDataManager;

use App\Projects\Project;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class BaseController extends Controller
{
    public function MainNavigation()
    {
        $projects = Project::get();
        $projectNav = ArrayList::create();
        $projectNav->push(ArrayData::create(array(
            'Title' => 'Alle Projekte',
            'Link' => '',
            'Active' => $this->request->getURL() == '',
        )));

        foreach ($projects as $project) {
            $projectNav->push(ArrayData::create(array(
                'Title' => $project->Title,
                'Link' => 'projects/'.$project->ID.'/shortlinks',
                'Active' => $this->request->getURL() == 'projects/'.$project->ID.'/shortlinks',
            )));
        }
        $navigation = ArrayList::create([
            ArrayData::create(array(
                'Title' => 'Projekte',
                'Link' => '/',
                'Children' => $projectNav,
            )),
        ]);
        return $navigation;
    }
}
