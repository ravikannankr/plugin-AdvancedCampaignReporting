<?php
/**
 * Piwik PRO -  Premium functionality and enterprise-level support for Piwik Analytics
 *
 * @link http://piwik.pro
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\AdvancedCampaignReporting;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\Referrers\API as ReferrersAPI;

/**
 * API for plugin AdvancedCampaignReporting
 *
 * @package AdvancedCampaignReporting
 * @method static \Piwik\Plugins\AdvancedCampaignReporting\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected function getDataTable($name, $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = Archive::createDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    public function getName($idSite, $period, $date, $segment = false, $expanded = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_NAME_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);
        $dataTable->filter('AddSegmentValue');

        if ($this->isTableEmpty($dataTable)) {
            $referrersDataTable = ReferrersAPI::getInstance()->getCampaigns($idSite, $period, $date, $segment, $expanded);
            $dataTable = $this->mergeDataTableMaps($dataTable, $referrersDataTable);
        }

        return $dataTable;
    }

    public function getKeywordContentFromNameId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_NAME_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable);

        if ($this->isTableEmpty($dataTable)) {
            $referrersDataTable = ReferrersAPI::getInstance()->getKeywordsFromCampaignId($idSite, $period, $date, $idSubtable, $segment);
            $dataTable = $this->mergeDataTableMaps($dataTable, $referrersDataTable);
        }

        return $dataTable;
    }

    public function getKeyword($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_KEYWORD_RECORD_NAME, $idSite, $period, $date, $segment);

        if ($this->isTableEmpty($dataTable)) {
            $referrersDataTable = ReferrersAPI::getInstance()->getCampaigns($idSite, $period, $date, $segment, $expanded = true);
            $referrersDataTable->applyQueuedFilters();
            $referrersDataTable = $referrersDataTable->mergeSubtables();

            $dataTable = $this->mergeDataTableMaps($dataTable, $referrersDataTable);
        }

        return $dataTable;
    }

    public function getSource($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_SOURCE_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->filter('AddSegmentValue');
        return $dataTable;
    }

    public function getMedium($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_MEDIUM_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->filter('AddSegmentValue');
        return $dataTable;
    }

    public function getContent($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_CONTENT_RECORD_NAME, $idSite, $period, $date, $segment);
        return $dataTable;
    }

    public function getSourceMedium($idSite, $period, $date, $segment = false, $expanded = false)
    {
        $dataTable = $this->getDataTable(Archiver::HIERARCHICAL_SOURCE_MEDIUM_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);
        return $dataTable;
    }

    public function getNameFromSourceMediumId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::HIERARCHICAL_SOURCE_MEDIUM_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable);
        return $dataTable;
    }

    private function isTableEmpty(DataTable\DataTableInterface $dataTable)
    {
        if ($dataTable instanceof DataTable) {
            return $dataTable->getRowsCount() == 0;
        } else if ($dataTable instanceof DataTable\Map) {
            foreach ($dataTable->getDataTables() as $label => $childTable) {
                if ($this->isTableEmpty($childTable)) {
                    return true;
                }
            }
            return false;
        } else {
            throw new \Exception("Sanity check: unknown datatable type '" . get_class($dataTable) . "'.");
        }
    }

    private function mergeDataTableMaps(DataTable\DataTableInterface $dataTable,
                                        DataTable\DataTableInterface $referrersDataTable)
    {
        if ($dataTable instanceof DataTable) {
            if ($this->isTableEmpty($dataTable)) {
                $referrersDataTable->setAllTableMetadata($dataTable->getAllTableMetadata());
                return $referrersDataTable;
            } else {
                return $dataTable;
            }
        } else if ($dataTable instanceof DataTable\Map) {
            foreach ($dataTable->getDataTables() as $label => $childTable) {
                $newTable = $this->mergeDataTableMaps($childTable, $referrersDataTable->getTable($label));
                $dataTable->addTable($newTable, $label);
            }
            return $dataTable;
        } else {
            throw new \Exception("Sanity check: unknown datatable type '" . get_class($dataTable) . "'.");
        }
    }
}
