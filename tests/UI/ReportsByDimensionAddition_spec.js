/*!
 * Piwik PRO - cloud hosting and enterprise analytics consultancy
 * from the creators of Piwik.org
 *
 * @link http://piwik.pro
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
describe("AdvancedCampaignReporting_ReportsByDimensionAddition", function () {
    this.timeout(0);

    this.fixture = 'Piwik\\Plugins\\AdvancedCampaignReporting\\tests\\Fixtures\\TrackAdvancedCampaigns';

    var withVisitsParams = "&idSite=1&date=2013-01-23&period=week",
        urlPrefix = "?module=CoreHome&action=index&",
        goalsUrl = urlPrefix + withVisitsParams + "#?" + withVisitsParams + "&category=Goals_Goals&subcategory=General_Overview",
        ecommerceUrl = urlPrefix + withVisitsParams + "#?" + withVisitsParams + "&category=Goals_Ecommerce&subcategory=Ecommerce_Sales";

    it("should load correctly within the Goals page", function (done) {
        expect.screenshot("loaded_goals").to.be.captureSelector('.reportsByDimensionView,.reportsByDimensionView .reportContainer', function (page) {
            page.load(goalsUrl);
            page.evaluate(function () {
                $('.dimensionCategory .dimension:contains(Campaign Names)').click();
            });
        }, done);
    });

    it("should load correctly within the Ecommerce page", function (done) {
        expect.screenshot("loaded_ecommerce").to.be.captureSelector('.reportsByDimensionView,.reportsByDimensionView .reportContainer', function (page) {
            page.load(ecommerceUrl);
            page.evaluate(function () {
                $('.dimensionCategory .dimension:contains(Campaign Names)').click();
            });
        }, done);
    });
});
