<?php

use Interop\Container\ContainerInterface;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignName;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignKeyword;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignSource;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignMedium;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignContent;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignId;

return [
    'advanced_campaign_reporting.campaign_detector' => DI\object(
        '\Piwik\Plugins\AdvancedCampaignReporting\Campaign\CampaignDetector'
    ),
    'advanced_campaign_reporting.uri_parameters.campaign_name' => DI\factory(function (ContainerInterface $c) {
        return [ CampaignName::COLUMN_NAME => $c->has('ini.AdvancedCampaignReporting.campaign_name') ?
            array_map('trim', explode(',', $c->get('ini.AdvancedCampaignReporting.campaign_name'))) :
            CampaignName::DEFAULT_URI_PARAMETERS
        ];
    }),
    'advanced_campaign_reporting.uri_parameters.campaign_keyword' => DI\factory(function (ContainerInterface $c) {
        return [CampaignKeyword::COLUMN_NAME => $c->has('ini.AdvancedCampaignReporting.campaign_keyword') ?
            array_map('trim', explode(',', $c->get('ini.AdvancedCampaignReporting.campaign_keyword'))) :
            CampaignKeyword::DEFAULT_URI_PARAMETERS
        ];
    }),
    'advanced_campaign_reporting.uri_parameters.campaign_source' => DI\factory(function (ContainerInterface $c) {
        return [ CampaignSource::COLUMN_NAME => $c->has('ini.AdvancedCampaignReporting.campaign_source') ?
            array_map('trim', explode(',', $c->get('ini.AdvancedCampaignReporting.campaign_source'))) :
            CampaignSource::DEFAULT_URI_PARAMETERS
        ];
    }),
    'advanced_campaign_reporting.uri_parameters.campaign_medium' => DI\factory(function (ContainerInterface $c) {
        return [ CampaignMedium::COLUMN_NAME => $c->has('ini.AdvancedCampaignReporting.campaign_medium') ?
            array_map('trim', explode(',', $c->get('ini.AdvancedCampaignReporting.campaign_medium'))) :
            CampaignMedium::DEFAULT_URI_PARAMETERS
        ];
    }),
    'advanced_campaign_reporting.uri_parameters.campaign_content' => DI\factory(function (ContainerInterface $c) {
        return [ CampaignContent::COLUMN_NAME => $c->has('ini.AdvancedCampaignReporting.campaign_content') ?
            array_map('trim', explode(',', $c->get('ini.AdvancedCampaignReporting.campaign_content'))) :
            CampaignContent::DEFAULT_URI_PARAMETERS
        ];
    }),
    'advanced_campaign_reporting.uri_parameters.campaign_id' => DI\factory(function (ContainerInterface $c) {
        return [ CampaignId::COLUMN_NAME => $c->has('ini.AdvancedCampaignReporting.campaign_id') ?
            array_map('trim', explode(',', $c->get('ini.AdvancedCampaignReporting.campaign_id'))) :
            CampaignId::DEFAULT_URI_PARAMETERS
        ];
    }),
];
