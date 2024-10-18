<?php
require 'vendor/autoload.php';

use Aws\CodeBuild\CodeBuildClient;
use Aws\Exception\AwsException;

$onlyScanForToday = true;
$showAll = false;
$enableFetching = false;

$key = '';
$secret = '';
$token = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $key = htmlspecialchars(trim($_POST["key"]));
    $secret = htmlspecialchars(trim($_POST["secret"]));
    $token = htmlspecialchars(trim($_POST["token"]));

    // Basic validation
    if (empty($key) || empty($secret) || empty($token)) {
        echo "All fields are required.";
        exit;
    } 

    $enableFetching = true;
    
} else {
    // If the form wasn't submitted, redirect or show an error
    echo "NO CREDENTIALS SUPPLEMENTED";
}

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

    <div class="form-container">
        <h4>AWS Credentials</h4>
        <form action="#" method="POST">
            <div class="form-group">
                <label for="key">KEY</label>
                <input type="text" id="key" name="key" required autocomplete="off" value="<?php echo $key; ?>">
            </div>
            <div class="form-group">
                <label for="secret">SECRET</label>
                <input type="text" id="secret" name="secret" required autocomplete="off" value="<?php echo $secret; ?>">
            </div>
            <div class="form-group">
                <label for="token">TOKEN</label>
                <input type="text" id="token" name="token" required autocomplete="off" value="<?php echo $token; ?>">
            </div>
            <button type="submit">Generate</button>
        </form>
    </div>

    <div class="report-container">
        <h4>Automation Report - <?php echo date('M d, Y'); ?></h4>
        <table>
            <thead>
                <td>Name</td>
                <td>Test<br />Scenarios</td>
                <td>Passed</td>
                <td>Failed</td>
                <td>Skipped</td>
                <td>Blocker</td>
                <td>Pass in<br />Local Run</td>
                <td>Bug<br />Found</td>
                <td>Auto Ticket<br />for fixes</td>
                <td>Assignee</td>
                <td>Remarks</td>
            </thead>
            <tbody>
                <?php
                if($enableFetching) {

                    $client = new CodeBuildClient([
                        'version' => 'latest',
                        'region' => 'us-west-2',
                        'credentials' => [
                            'key' => $key,
                            'secret' => $secret,
                            'token' => $token
                        ],
                    ]);


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
                                        $totalTests = $succeeded + $failed + $skipped;
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
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

<style>
    /* TABLE STYLES */
    body {
        font-family: Arial, Helvetica, sans-serif;
    }


        /* FORM STYLES */
    .report-container {
        background: white;
        padding: 20px;
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


    /* FORM STYLES */
    .form-container {
        background: white;
        max-width: 400px;
        padding: 20px;
    }

    .form-group {
        display: flex; /* Use flexbox for inline layout */
        justify-content: space-between; /* Distribute space between items */
        align-items: center; /* Align items vertically */
        margin-bottom: 15px;
    }

    label {
        margin-right: 10px; /* Space between label and input */
        flex: 0 0 20%; /* Allow label to take up a fixed width */
        font-size: 13px;
    }

    input[type="text"],
    input[type="email"]{
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    textarea:focus {
        border-color: #007bff;
        outline: none;
    }

    button {
        width: 50%;
        padding: 10px 5px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 13px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }
</style>

</html>