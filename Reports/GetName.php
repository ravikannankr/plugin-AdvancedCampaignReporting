<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AdvancedCampaignReporting\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignName;
use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetName extends Base
{
    protected function init()
    {
        parent::init();

        $this->name            = Piwik::translate('AdvancedCampaignReporting_Names');
        $this->dimension       = new CampaignName();
        $this->documentation   = Piwik::translate('');
        $this->hasGoalMetrics  = true;

        $this->action = 'getName';

        $this->order = 1;

        $this->actionToLoadSubTables = 'getKeywordContentFromNameId';
        $this->subcategoryId = 'Referrers_Campaigns';
    }

    /**
     * {@inheritdoc}
     */
    public function configureView(ViewDataTable $view)
    {
        if (!empty($this->dimension)) {
            $view->config->addTranslations(array('label' => $this->dimension->getName()));
        }

        $view->config->columns_to_display = array_merge(array('label'), $this->metrics);
        $view->config->subtable_controller_action = 'getKeywordContentFromNameId';
    }
}
