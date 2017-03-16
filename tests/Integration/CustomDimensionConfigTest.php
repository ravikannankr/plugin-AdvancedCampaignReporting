<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AdvancedCampaignReporting\tests\Integration;

use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignContent;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignId;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignKeyword;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignMedium;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignName;
use Piwik\Plugins\AdvancedCampaignReporting\Columns\CampaignSource;
use Piwik\Plugins\SitesManager\API as SitesManager;
use Piwik\Plugins\AdvancedCampaignReporting\API as AdvancedCampaignReportingAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CustomDimensionConfigTest extends IntegrationTestCase
{

    /** @var string $testUrl */
    private $testUrl;

    /** @var int $idSite */
    private $idSite;

    /** @var \Piwik_LocalTracker $tracker */
    private $tracker;

    /** @var \DateTime $testDate */
    private $testDate;

    public function setUp()
    {
        $testVars = new \Piwik\Tests\Framework\TestingEnvironmentVariables();
        $configOverride = $testVars->configOverride;
        $configOverride['AdvancedCampaignReporting'] = [
            CampaignName::COLUMN_NAME => 'pk_campaign,custom_name_parameter',
            CampaignKeyword::COLUMN_NAME => 'pk_campaign,custom_keyword_parameter',
            CampaignSource::COLUMN_NAME => 'pk_campaign,custom_source_parameter',
            CampaignMedium::COLUMN_NAME => 'pk_campaign,custom_medium_parameter',
            CampaignContent::COLUMN_NAME => 'pk_campaign,custom_content_parameter',
            CampaignId::COLUMN_NAME => 'pk_campaign,custom_id_parameter'
        ];
        $testVars->configOverride = $configOverride;
        $testVars->save();

        parent::setUp();
    }

    public function testTrackingWithCustomParameters()
    {
        $this->givenWebsite();

        $this->givenTracker();

        $this->givenUrl();

        $this->whenWebsiteTracksUrlWithCustomCampaignParameters();

        $this->thenNameDimensionShouldBeTracked();

        $this->thenKeywordDimensionShouldBeTracked();

        $this->thenSourceDimensionShouldBeTracked();

        $this->thenMediumDimensionShouldBeTracked();

        $this->thenContentDimensionShouldBeTracked();
    }

    private function thenNameDimensionShouldBeTracked()
    {
        $api = AdvancedCampaignReportingAPI::getInstance();

        $nameReport = $api->getName(
            $this->idSite,
            'day',
            $this->testDate->format('Y-m-d')
        );

        $this->assertEquals(
            'custom_name_value',
            $nameReport->getColumn('label')[0]
        );
    }

    private function thenKeywordDimensionShouldBeTracked()
    {
        $api = AdvancedCampaignReportingAPI::getInstance();

        $keywordReport = $api->getKeyword(
            $this->idSite,
            'day',
            $this->testDate->format('Y-m-d')
        );

        $this->assertEquals(
            'custom_keyword_value',
            $keywordReport->getColumn('label')[0]
        );
    }

    private function thenSourceDimensionShouldBeTracked()
    {
        $api = AdvancedCampaignReportingAPI::getInstance();

        $keywordReport = $api->getSource(
            $this->idSite,
            'day',
            $this->testDate->format('Y-m-d')
        );

        $this->assertEquals(
            'custom_source_value',
            $keywordReport->getColumn('label')[0]
        );
    }

    private function thenMediumDimensionShouldBeTracked()
    {
        $api = AdvancedCampaignReportingAPI::getInstance();

        $keywordReport = $api->getMedium(
            $this->idSite,
            'day',
            $this->testDate->format('Y-m-d')
        );

        $this->assertEquals(
            'custom_medium_value',
            $keywordReport->getColumn('label')[0]
        );
    }

    private function thenContentDimensionShouldBeTracked()
    {
        $api = AdvancedCampaignReportingAPI::getInstance();

        $keywordReport = $api->getContent(
            $this->idSite,
            'day',
            $this->testDate->format('Y-m-d')
        );

        $this->assertEquals(
            'custom_content_value',
            $keywordReport->getColumn('label')[0]
        );
    }

    private function whenWebsiteTracksUrlWithCustomCampaignParameters()
    {
        $this->tracker->setUrl($this->testUrl);

        Fixture::checkResponse($this->tracker->doTrackPageView('Track visit with custom campaign parameters'));
    }

    private function givenWebsite()
    {
        $sitesManager = SitesManager::getInstance();

        $this->idSite = $sitesManager->addSite(
            'TestSite'
        );
    }

    private function givenTracker()
    {
        $this->testDate = new \DateTime();

        $this->tracker = Fixture::getTracker(
            $this->idSite,
            $this->testDate->format('U'),
            $defaultInit = true,
            $useLocal = false
        );
    }

    private function givenUrl()
    {
        $this->testUrl = sprintf(
            'http://example.com/?custom_name_parameter=%s&custom_keyword_parameter=%s&custom_source_parameter=%s&custom_content_parameter=%s&custom_medium_parameter=%s&custom_id_parameter=%s',
            'custom_name_value',
            'custom_keyword_value',
            'custom_source_value',
            'custom_content_value',
            'custom_medium_value',
            'custom_id_value'
        );
    }

    public function provideContainerConfig()
    {
        return array(

            'observers.global' => \DI\add(array(
                array('Environment.bootstrapped', function () {
                    $config = \Piwik\Config::getInstance();



                }),
            )),

        );
    }
}
