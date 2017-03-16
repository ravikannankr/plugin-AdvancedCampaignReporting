<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AdvancedCampaignReporting\Columns;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\AdvancedCampaignReporting\Campaign\CampaignDetector;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

/**
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin\Dimension\VisitDimension} for more information.
 */
class CampaignSource extends VisitDimension
{
    const COLUMN_NAME = 'campaign_source';

    const COLUMN_TYPE = 'VARCHAR(255) NULL DEFAULT NULL AFTER `campaign_keyword`';

    const DEFAULT_URI_PARAMETERS = ['pk_source', 'utm_source'];

    /**
     * @var string
     */
    protected $columnName = self::COLUMN_NAME;

    /**
     * @var string
     */
    protected $columnType = self::COLUMN_TYPE;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Piwik::translate('AdvancedCampaignReporting_Source');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('campaignSource');
        $segment->setName('AdvancedCampaignReporting_Source');
        $segment->setCategory('AdvancedCampaignReporting_Title');
        $this->addSegment($segment);
    }

    /**
     * {@inheritdoc}
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        /** @var CampaignDetector $campaignDetector */
        $campaignDetector = StaticContainer::get('advanced_campaign_reporting.campaign_detector');

        $campaignDimensions = $campaignDetector->detectCampaignFromRequest(
            $request,
            StaticContainer::get('advanced_campaign_reporting.uri_parameters.campaign_source')
        );

        return isset($campaignDimensions[self::COLUMN_NAME]) ? $campaignDimensions[self::COLUMN_NAME] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn($this->columnName);
    }

    public function getRequiredVisitFields()
    {
        return [
            'campaign_name',
            'campaign_keyword'
        ];
    }
}
