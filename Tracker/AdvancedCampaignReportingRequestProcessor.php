<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\plugins\AdvancedCampaignReporting\Tracker;

use Piwik\Plugins\AdvancedCampaignReporting\Tracker;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Request;

class AdvancedCampaignReportingRequestProcessor extends RequestProcessor
{
    /**
     * @param VisitProperties $visitProperties
     * @param Request $request
     */
    public function onNewVisit(VisitProperties $visitProperties, Request $request)
    {
        $campaignTracker = new Tracker($request);
        $campaignTracker->updateNewVisitWithCampaign($visitProperties);
    }
}
