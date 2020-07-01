<?php

namespace Sminnee\ModelComments;

use Plastyk\Dashboard\Model\DashboardPanel;
use SilverStripe\Snapshots\Snapshot;
use SilverStripe\Security\Permission;

// Dashboard isn't a dependency of the module, allow the case where it's not included
if (!class_exists(DashboardPanel::class)) {
    return;
}

class RecentCommentsDashboardPanel extends DashboardPanel
{
    /**
     * @var int
     * @config
     */
    private static $limit = 20;

    public function canView($member = null)
    {
        return Permission::checkMember($member, ['MODELCOMMENT_VIEW', 'MODELCOMMENT', 'MODELCOMMENT_ADMIN']);
    }

    public function getData()
    {
        $data = parent::getData();
        $data['Results'] = $this->getResults();

        return $data;
    }

    private function getResults()
    {
        return ModelComment::get()
            ->sort('Created', 'DESC')
            ->limit(static::config()->get('limit'));
    }
}
