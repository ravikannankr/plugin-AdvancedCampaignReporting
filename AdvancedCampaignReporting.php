<?php
/**
 * Piwik PRO - cloud hosting and enterprise analytics consultancy
 *
 *
 * @link http://piwik.pro
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\AdvancedCampaignReporting;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignContent;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignId;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignKeyword;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignMedium;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignName;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignSource;
use Piwik\Plugins\Referrers\Reports\GetCampaigns;
use Piwik\Url;
use Piwik\View\ReportsByDimension;
use Piwik\WidgetsList;

/**
 * @package AdvancedCampaignReporting
 */
class AdvancedCampaignReporting extends \Piwik\Plugin
{
    public function getListHooksRegistered()
    {
        return array(
            'Tracker.newConversionInformation'              => 'enrichConversionWithAdvancedCampaign',
            'Tracker.PageUrl.getQueryParametersToExclude'   => 'getQueryParametersToExclude',
            'Request.dispatch'                              => 'dispatchAdvancedCampaigns',
            'Live.getAllVisitorDetails'                     => 'extendVisitorDetails',
            'Report.filterReports'                          => 'removeOriginalCampaignReport'
        );
    }

    /**
     * New DB fields to track Campaign attributes
     */
    public function install()
    {
        foreach($this->getTables() as $table) {
            try {
                $query = "ALTER TABLE `" . $table . "`
                    ADD `campaign_name` " . CampaignName::COLUMN_TYPE . " ,
                    ADD `campaign_keyword` " . CampaignKeyword::COLUMN_TYPE . " ,
                    ADD `campaign_source` " . CampaignSource::COLUMN_TYPE . " ,
                    ADD `campaign_medium` " . CampaignMedium::COLUMN_TYPE . ",
                    ADD `campaign_content` " . CampaignContent::COLUMN_TYPE . ",
                    ADD `campaign_id` " . CampaignId::COLUMN_TYPE;
                Db::exec($query);
            } catch (\Exception $e) {
                if (!Db::get()->isErrNo($e, '1060')) {
                    throw $e;
                }
            }
        }
    }

    public function uninstall()
    {
        $fields = array(
            'campaign_name',
            'campaign_keyword',
            'campaign_source',
            'campaign_medium',
            'campaign_content',
            'campaign_id',
        );
        foreach($this->getTables() as $table) {
            foreach($fields as $field) {
                Db::exec("ALTER TABLE `" . $table . "` DROP COLUMN `". $field ."` ");
            }
        }
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $fields = array(
            'campaignId'      => CampaignId::COLUMN_NAME,
            'campaignContent' => CampaignContent::COLUMN_NAME,
            'campaignKeyword' => CampaignKeyword::COLUMN_NAME,
            'campaignMedium'  => CampaignMedium::COLUMN_NAME,
            'campaignName'    => CampaignName::COLUMN_NAME,
            'campaignSource'  => CampaignSource::COLUMN_NAME,
        );
        foreach ($fields as $name => $field) {
            $visitor[$name] = $details[$field];
        }
    }


    public function enrichConversionWithAdvancedCampaign(&$goal, $visitorInfo, \Piwik\Tracker\Request $request)
    {
        $campaignTracker = new Tracker($request);
        $campaignTracker->updateNewConversionWithCampaign($goal, $visitorInfo);
    }

    public function getQueryParametersToExclude(&$excludedParameters)
    {
        $advancedCampaignParameters = Tracker::getCampaignParameters();

        foreach($advancedCampaignParameters as $advancedCampaignParameter) {
            $excludedParameters = array_merge($excludedParameters, $advancedCampaignParameter);
        }
    }

    /**
     * @param Report[] $reports
     */
    public function removeOriginalCampaignReport(&$reports)
    {
        foreach ($reports as $index => $report) {
            if ($report instanceof GetCampaigns) {
                unset($reports[$index]);
            }
        }
    }

    public static function getAdvancedCampaignFields()
    {
        return array(
            CampaignName::COLUMN_NAME,
            CampaignKeyword::COLUMN_NAME,
            CampaignSource::COLUMN_NAME,
            CampaignMedium::COLUMN_NAME,
            CampaignContent::COLUMN_NAME,
            CampaignId::COLUMN_NAME
        );
    }

    /**
     * Instead of dispatching the standard Referrers>Campaigns report,
     * dispatch our better campaign report.
     *
     * @param $module
     * @param $action
     * @param $parameters
     */
    public function dispatchAdvancedCampaigns(&$module, &$action, &$parameters)
    {
        if($module == 'Referrers'
            && $action == 'getCampaigns') {
            $module = 'AdvancedCampaignReporting';
            $action = 'indexCampaigns';
        }
    }

    public function getLabelFromMethod($method)
    {
        $labels = array(
            'getName' => 'AdvancedCampaignReporting_Name',
            'getKeyword' => 'AdvancedCampaignReporting_Keyword',
            'getSource' => 'AdvancedCampaignReporting_Source',
            'getMedium' => 'AdvancedCampaignReporting_Medium',
            'getContent' => 'AdvancedCampaignReporting_Content',
            'getSourceMedium' => 'AdvancedCampaignReporting_CombinedSourceMedium',
            'getKeywordContentFromNameId' => 'AdvancedCampaignReporting_CombinedKeywordContent',
            'getNameFromSourceMediumId' => 'AdvancedCampaignReporting_Name',
        );
        if(!isset($labels[$method])) {
            throw new \Exception("Invalid requested label for $method");
        }
        return Piwik::translate($labels[$method]);
    }

    private function getTables()
    {
        $tables = array(
            Common::prefixTable("log_visit"),
            Common::prefixTable("log_conversion"),
        );
        return $tables;
    }

    public function isTrackerPlugin()
    {
        return true;
    }
}
