<?php
/**
 * Piwik PRO -  Premium functionality and enterprise-level support for Piwik Analytics
 *
 * @link http://piwik.pro
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\AdvancedCampaignReporting;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\AdvancedCampaignReporting\Campaign\CampaignDetectorInterface;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignContent;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignId;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignKeyword;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignMedium;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignName;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignSource;
use Piwik\Tracker\Visit\VisitProperties;

class Tracker
{
    /**
     * @var \Piwik\Tracker\Request
     */
    private $request;

    /**
     * @var CampaignDetectorInterface
     */
    private $campaignDetector;

    public static $campaignFieldLengths = array(
        CampaignName::COLUMN_NAME => 255,
        CampaignKeyword::COLUMN_NAME => 255,
        CampaignSource::COLUMN_NAME=> 255,
        CampaignMedium::COLUMN_NAME => 255,
        CampaignContent::COLUMN_NAME=> 255,
        CampaignId::COLUMN_NAME => 100
    );

    public function __construct(\Piwik\Tracker\Request $request)
    {
        $this->request = $request;
        $this->campaignDetector = StaticContainer::get('advanced_campaign_reporting.campaign_detector');
    }

    /**
     * @return array
     */
    public static function getCampaignParameters()
    {
        return array(
            CampaignName::COLUMN_NAME => self::getCampaignParametersConfig(CampaignName::COLUMN_NAME),
            CampaignKeyword::COLUMN_NAME => self::getCampaignParametersConfig(CampaignKeyword::COLUMN_NAME),
            CampaignSource::COLUMN_NAME => self::getCampaignParametersConfig(CampaignSource::COLUMN_NAME),
            CampaignMedium::COLUMN_NAME => self::getCampaignParametersConfig(CampaignMedium::COLUMN_NAME),
            CampaignContent::COLUMN_NAME => self::getCampaignParametersConfig(CampaignContent::COLUMN_NAME),
            CampaignId::COLUMN_NAME => self::getCampaignParametersConfig(CampaignId::COLUMN_NAME),
        );
    }

    private static function getCampaignParametersConfig($columnName)
    {
        return StaticContainer::get(sprintf(
            'advanced_campaign_reporting.uri_parameters.%s',
            $columnName
        ))[$columnName];
    }

    public function updateNewConversionWithCampaign(&$conversionToInsert, $visitorInfo)
    {
        $campaignParameters = self::getCampaignParameters();

        $campaignDimensions = $this->campaignDetector->detectCampaignFromVisit(
            $visitorInfo,
            $campaignParameters
        );
        if(empty($campaignDimensions)) {
            $campaignDimensions = $this->campaignDetector->detectCampaignFromRequest(
                $this->request,
                $campaignParameters
            );
        }

        $visitProperties = new VisitProperties();
        $this->addDimensionsToRow($visitProperties, $campaignDimensions);

        $conversionToInsert = array_merge($conversionToInsert, $visitProperties->getProperties());
    }

    public function updateNewVisitWithCampaign(VisitProperties $visitProperties)
    {
        $campaignParameters = self::getCampaignParameters();

        $campaignDimensions = $this->campaignDetector->detectCampaignFromRequest(
            $this->request,
            $campaignParameters
        );

        if(empty($campaignDimensions)) {

            // If for some reason a campaign was detected in Core Tracker
            // but not here, copy that campaign to the Advanced Campaign
            if($visitProperties->getProperty('referer_type') != Common::REFERRER_TYPE_CAMPAIGN) {
                return ;
            }
            $campaignDimensions = array(
                CampaignName::COLUMN_NAME=> $visitProperties->getProperty('referer_name')
            );
            if(!empty($visitProperties->getProperty('referer_keyword'))) {
                $campaignDimensions[CampaignKeyword::COLUMN_NAME] = $visitProperties->getProperty('referer_keyword');
            }
        }

        $this->addDimensionsToRow($visitProperties, $campaignDimensions);
    }

    /**
     * @param $visitProperties
     * @param $campaignDimensions
     * @return array
     */
    protected function addDimensionsToRow(VisitProperties $visitProperties, $campaignDimensions)
    {
        if(empty($campaignDimensions)) {
            return;
        }

        $this->truncateDimensions($campaignDimensions);

        Common::printDebug("Found Advanced Campaign (after truncation): ");
        Common::printDebug($campaignDimensions);

        // Set the new campaign fields on the visitor
        foreach($campaignDimensions as $field => $value) {
            $visitProperties->setProperty($field, $value);
        }

        // Overwrite core referer_ fields when an advanced campaign was detected
        $visitProperties->setProperty('referer_type', Common::REFERRER_TYPE_CAMPAIGN);

        if ($visitProperties->getProperty(CampaignName::COLUMN_NAME)) {
            $visitProperties->setProperty(
                'referer_name',
                substr($visitProperties->getProperty(CampaignName::COLUMN_NAME), 0, 70)
            );
        }
        if ($visitProperties->getProperty(CampaignKeyword::COLUMN_NAME)) {
            $visitProperties->setProperty(
                'referer_keyword',
                substr($visitProperties->getProperty(CampaignKeyword::COLUMN_NAME), 0, 255)
            );
        }
    }

    private function truncateDimensions(&$campaignDimensions)
    {
        foreach (self::$campaignFieldLengths as $name => $length) {
            if (!empty($campaignDimensions[$name])) {
                $campaignDimensions[$name] = substr($campaignDimensions[$name], 0, $length);
            }
        }
    }
}
