<?php
/**
 * Dev: Scrap Beast
 * Emai: info@scrapbeast.com
 * Date: 02/13/2020
 * Time: 20:30 AM
 */

include 'src/ScrapBeastImport.php';

api_expose_admin('scrapbeast/import', function($params) {

    if (isset($params['live_report_url'])) {

        $liveReportUrl = trim($params['live_report_url']);

        $optionData = array();
        $optionData['option_value'] = $liveReportUrl;
        $optionData['option_key'] = 'live_report_url';
        $optionData['option_group'] = 'scrapbeast';
        save_option($optionData);

        $import = new ScrapBeastImport();
        $import->setSourceUrl($liveReportUrl);
        $import->setImportStep($params['step']);

        return $import->start();
    }

});