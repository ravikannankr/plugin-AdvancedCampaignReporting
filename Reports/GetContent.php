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
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignContent;
use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetContent extends Base
{
    protected function init()
    {
        parent::init();

        $this->name            = Piwik::translate('AdvancedCampaignReporting_Contents');
        $this->dimension       = new CampaignContent();
        $this->documentation   = Piwik::translate('');
        $this->hasGoalMetrics  = true;

        $this->action = 'getContent';

        $this->order = 5;

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
    }

}
