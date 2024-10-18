<?php
require 'vendor/autoload.php';

use Aws\CodeBuild\CodeBuildClient;
use Aws\Exception\AwsException;

$onlyScanForToday = true;
$showAll = false;

$client = new CodeBuildClient([
    'version' => 'latest',
    'region' => 'us-west-2',
    'credentials' => [
        'key' => 'ASIAZQ3DQD2TPPR6W6W4',
        'secret' => '5KnD5WebaF00kS3CI0xHR42jPAqN8tdB+a8YsPNS',
        'token' => 'IQoJb3JpZ2luX2VjENf//////////wEaCXVzLXdlc3QtMiJHMEUCICcZ7yU0CfhcBfuSpO3qbou59ASTi1BP9THDvrvu4ArdAiEAvwjifrV3oJJpqLO/U+/jYbBGszB9+35vpYDzo96ZoX8qtwMIQBAAGgw2NTQ2NTQzMTYxOTgiDPEoYltOSq1gVnP0UyqUA+87mXyd+ivAUGfaV6be6w/POQV+z+Euxk/QF/0AdMpreGFy3HBYROyuuPvaiNBUKfNMqXtmmNpW2HM61L0F9rWyz95ri0kOS9fT0sMbe/BfPyerCV602O9KQZacQB7qfm6gZ7wyO5yCoV2INQOOY7ju5tkJZijAQ10ueIj9plh7fj7RND1itGWkhctENWq1kVUsKLFixxFU+OapMBiHF7q+cH7j2VdwiQZT69uHTsoetyqdodUeBd5fdoDb71lSx8g6tKNJjO8dX6SDVtBsMPZ8Sdzbk+aFiOEn83kVwtrf8kylOLAxiWGjPohkIn0UcjNYVQAWmjhb1ED3Z+MC/EwZGTMnKmJubCXuxpSfgcWtC7P/2qtZwaCtN7ZcuKkR4RMj1QpuyozGwoF5DfcTEGOHyoz8SdLe6Sw7BWxPzrPj3/wvESppoMK0BDFDJ9hphZSXRttVz5Ld5j/GoCkeDJQPlpgqi1unkapqhqwRAPsieHufxvZiXD/v5d6MIkI4xIlCqpqeAAvnY9T1bFYbc5pEV2vxMMr+x7gGOqYB1BdxorV7MImlBBLeiKOdf4tfdjeHYhJSXxddFny17+2iu3CelBV57kHn0cy+KyZtGaXDf3STsRyxLaeIrfOf9JXI5E/XprqNAl0khJfdyNsIMW6sU1Xw4QCGShoTq4h+xOS+GGwyCD+1M89Jq41UtxB2aRFhwHm5nn1OXekhV5+tPI+iWL7xR1qmW9w+wI/qroRnjWhKaomeAHJdVtMf68CLxSId4A=='
    ],
]);

$reportGrpList = [
    'ccm-ss-portal-api-regression-tests',
    'ccm-ss-portal-ui-regression-tests',
    'ccm-ss-advanced-cm-only-regression-tests',
    'ccm-ss-standard-cm-only-regression-tests',
    'ccm-reporting-api',
    'ccm-ss-portal-api-smoke-qa-tests',
    'ccm-ss-portal-ui-smoke-qa-tests',
    'ccm-ss-advanced-cm-only-smoke-qa-tests',
    'ccm-ss-standard-cm-only-smoke-qa-tests',
    'ccm-legacy-two-step-optin-component',
    'ccm-legacy-cms-component',
    'ccm-legacy-icon-server-component',
    'ccm-legacy-tdp-component',
    'ccm-legacy-uat-parallel-tests',
    'ccm-legacy-autoblock-component',
    'ccm-legacy-widget-regression',
    'ccm-legacy-dnt-component',
    'ccm-legacy-iab-component',
    'ccm-portal-ui-regression-tests-playwright',
    'ccm-advanced-cm-regression-tests-playwright',
    'ccm-portal-ui-smoke-tests-playwright'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automation Report - <?php echo date('M d, Y'); ?></title>
</head>

<body>
    <h4>Automation Report - <?php echo date('M d, Y'); ?></h4>
    <table>
        <thead>
            <td>Name</td>
            <td>Test<br/>Scenarios</td>
            <td>Passed</td>
            <td>Failed</td>
            <td>Skipped</td>
            <td>Blocker</td>
            <td>Pass in<br/>Local Run</td>
            <td>Bug<br/>Found</td>
            <td>Auto Ticket<br/>for fixes</td>
            <td>Assignee</td>
            <td>Remarks</td>
        </thead>
        <tbody>
            <?php
                foreach ($reportGrpList as $reportGrp) {
                    
                    $reportGroupArn = 'arn:aws:codebuild:us-west-2:654654316198:report-group/' . $reportGrp . '-SurefireReports';

                    try {

                        $result = $client->listReportsForReportGroup([
                            'reportGroupArn' => $reportGroupArn,
                            'sortBy' => 'CREATED_TIME',
                            'maxResults' => 1,
                        ]);

                        $reportArns = $result['reports'];

                        foreach ($reportArns as $reportArn) {

                            // Get details of each report
                            $reportDetails = $client->batchGetReports([
                                'reportArns' => [$reportArn],
                            ]);

                            foreach ($reportDetails['reports'] as $report) {
                                $showAll = false;
                                $created = date_format(date_create($report['created']), 'M d, Y H:i:s');

                                if ($onlyScanForToday) {
                                    if (date('Y-m-d', strtotime($report['created'])) == date('Y-m-d')) {
                                        $showAll = true;
                                    }
                                } else {
                                    $showAll = true;
                                }

                                if ($showAll) {
                                    $reportArnName = explode("/", $report['arn'])[1];

                                    $testSummary = $report['testSummary']['statusCounts'];
                                    $succeeded = $testSummary['SUCCEEDED'];
                                    $failed = $testSummary['FAILED'];
                                    $skipped = $testSummary['SKIPPED'];
                                    $totalTests  = $succeeded + $failed + $skipped;
                                    $blocker = '';
                                    $passedInLocalRun = '';
                                    $bugFound = '';
                                    $autoTicket = '';
                                    $assignee = '';
                                    $remarks = '';
                                    echo '<tr>';
                                    echo "<td><a href='https://us-west-2.console.aws.amazon.com/codesuite/codebuild/654654316198/projects/" . $reportGrp . "/history?region=us-west-2'>" . $reportGrp . "</a></td>"; // Name
                                    echo "<td>" . $totalTests . "</td>"; // Test Scenarios
                                    echo "<td><a target='_blank' href='https://us-west-2.console.aws.amazon.com/codesuite/codebuild/654654316198/testReports/reports/ccm-pr-roi-SurefireReports/" . $reportArnName . "?region=us-west-2'>" . $succeeded . "</a></td>"; // Passed
                                    echo "<td>" . $failed . "</td>"; // Failed
                                    echo "<td>" . $skipped . "</td>"; // Skipped
                                    echo "<td>" . $blocker . "</td>"; // Blocker
                                    echo "<td>" . $passedInLocalRun . "</td>"; // Passed in Local Run
                                    echo "<td>" . $bugFound . "</td>"; // Bug Found
                                    echo "<td>" . $autoTicket . "</td>"; // Bug Found
                                    echo "<td>" . $assignee . "</td>"; // Bug Found
                                    echo "<td>" . $remarks . "</td>"; // Bug Found 
                                    echo '</tr>';
                                }
                            }
                        }
                    } catch (AwsException $e) {
                        // Catch and display errors
                        echo $e->getMessage();
                        echo "<br/>";
                    }
                }
            ?>
        </tbody>
    </table>
</body>

<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
    }

    td:not(:first-child) {
        text-align: center;
    }
    table {
        border-collapse: collapse;
    }
    table td {
        padding: 15px;
        line-break: anywhere;
    }
    table thead td {
        background-color: #a4c2f4;
        color: #000000;
        font-weight: bold;
        font-size: 13px;
        border: 1px solid #000000;
    }
    table tbody td {
        color: #000000;
        font-size: 13px;
        border: 1px solid #000000;
    }
    table tbody tr {
        background-color: #f9fafb;
    }
    table tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }
</style>

</html>