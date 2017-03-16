<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AdvancedCampaignReporting\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetSourceMedium extends Base
{
    protected function init()
    {
        parent::init();

        $this->name            = Piwik::translate('AdvancedCampaignReporting_CombinedSourcesMediums');
        $this->documentation   = Piwik::translate('');
        $this->hasGoalMetrics  = true;

        $this->action = 'getSourceMedium';

        $this->order = 6;

        $this->actionToLoadSubTables = 'getNameFromSourceMediumId';
        $this->subcategoryId = 'Referrers_Campaigns';
    }

    /**
     * {@inheritdoc}
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslations(array('label' => Piwik::translate('AdvancedCampaignReporting_CombinedSourceMedium')));

        $view->config->columns_to_display = array_merge(array('label'), $this->metrics);
        $view->config->subtable_controller_action = 'getNameFromSourceMediumId';
    }
}
