<?php
/**
 * Piwik PRO - cloud hosting and enterprise analytics consultancy
 *
 *
 * @link http://piwik.pro
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\AdvancedCampaignReporting\Columns;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Plugins\AdvancedCampaignReporting\Campaign\CampaignDetector;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

/**
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin\Dimension\VisitDimension} for more information.
 */
class CampaignName extends VisitDimension
{
    const COLUMN_NAME = 'campaign_name';

    const COLUMN_TYPE = 'VARCHAR(255) NULL DEFAULT NULL AFTER `referer_keyword`';

    const DEFAULT_URI_PARAMETERS = ['pk_campaign', 'piwik_campaign', 'pk_cpn', 'utm_campaign'];

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
        return Piwik::translate('AdvancedCampaignReporting_Name');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('campaignName');
        $segment->setName('AdvancedCampaignReporting_Name');
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
            StaticContainer::get('advanced_campaign_reporting.uri_parameters.campaign_name')
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
}
