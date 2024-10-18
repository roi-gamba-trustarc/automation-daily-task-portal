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
$accountId = '';

$jsonFile = 'reportgroup.json';
$jsonData = file_get_contents($jsonFile);
$reportGrpList = json_decode($jsonData, true);

$jiraLink = 'https://trustarc.atlassian.net/';
$awsLink = 'https://us-west-2.console.aws.amazon.com/';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $accountId = htmlspecialchars(trim($_POST["accountId"]));
    $key = htmlspecialchars(trim($_POST["key"]));
    $secret = htmlspecialchars(trim($_POST["secret"]));
    $token = htmlspecialchars(trim($_POST["token"]));

    // Basic validation
    if (empty($accountId) || empty($key) || empty($secret) || empty($token)) {
        echo "All fields are required.";
        exit;
    }

    $enableFetching = true;

} else {
    // If the form wasn't submitted, redirect or show an error
    echo "No Credentials Found. Cannot generate report.";
}

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
                <label for="key">ACCOUNT ID</label>
                <input type="text" id="accountId" name="accountId" required autocomplete="off" value="<?php echo $accountId; ?>">
            </div>
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
        <button id="copyButton">Copy Table</button>
        <h4>Automation Report - <?php echo date('M d, Y'); ?></h4>
        <table id="reportTable">
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
                if ($enableFetching) {

                    $client = new CodeBuildClient([
                        'version' => 'latest',
                        'region' => 'us-west-2',
                        'credentials' => [
                            'key' => $key,
                            'secret' => $secret,
                            'token' => $token
                        ],
                    ]);

                    foreach ($reportGrpList as $key => $reportGrp) {
                        $reportGroupArn = "arn:aws:codebuild:us-west-2:" . $accountId . ":report-group/" . $key . "-SurefireReports";

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

                                $report = $reportDetails['reports'][0];

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

                                    $bugs = '';
                                    $totalBugs = count($reportGrp[0]['bugs']);

                                    foreach ($reportGrp[0]['bugs'] as $index=>$bugsList) {
                                        if ($index === $totalBugs - 1) {
                                            // Last item, do not add a comma
                                            $bugs .= "<a href='" . $jiraLink . "browse/" . $bugsList . "' >" . $bugsList . "</a>";
                                        } else {
                                            // Not the last item, add a comma
                                            $bugs .= "<a href='" . $jiraLink . "browse/" . $bugsList . "' >" . $bugsList . "</a>, ";
                                        }
                                    }

                                    $autoTicket = "<a href='" . $jiraLink . "browse/" . $reportGrp[0]['autoticket'] . "' >" . $reportGrp[0]['autoticket'] . "</a> ";
                                    
                                    $assignee = $reportGrp[0]['assignee'];
                                    $remarks = $reportGrp[0]['remarks'];

                                    echo '<tr>';
                                    echo "<td><a href='" . $awsLink . "codesuite/codebuild/" . $accountId . "/projects/" . $key . "/history?region=us-west-2'>" . $key . "</a></td>"; // Name
                                    echo "<td>" . $totalTests . "</td>"; // Test Scenarios
                                    echo "<td><a target='_blank' href='" . $awsLink . "codesuite/codebuild/" . $accountId . "/testReports/reports/ccm-pr-roi-SurefireReports/" . $reportArnName . "?region=us-west-2'>" . $succeeded . "</a></td>"; // Passed
                                    echo "<td>" . $failed . "</td>"; // Failed
                                    echo "<td>" . $skipped . "</td>"; // Skipped
                                    echo "<td>" . $blocker . "</td>"; // Blocker
                                    echo "<td>" . $passedInLocalRun . "</td>"; // Passed in Local Run
                                    echo "<td>" . $bugs . "</td>"; // Bug Found
                                    echo "<td>" . $autoTicket . "</td>"; // Bug Found
                                    echo "<td>" . $assignee . "</td>"; // Bug Found
                                    echo "<td>" . $remarks . "</td>"; // Bug Found 
                                    echo '</tr>';
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
        overflow: hidden;
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
        line-break: anywhere;
        word-wrap: break-word;
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
        display: flex;
        /* Use flexbox for inline layout */
        justify-content: space-between;
        /* Distribute space between items */
        align-items: center;
        /* Align items vertically */
        margin-bottom: 15px;
    }

    label {
        margin-right: 10px;
        /* Space between label and input */
        flex: 0 0 20%;
        /* Allow label to take up a fixed width */
        font-size: 13px;
    }

    input[type="text"],
    input[type="email"] {
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

    button#copyButton {
        width: 100px;
        padding: 10px;
    }
</style>

<script>
    document.getElementById('copyButton').addEventListener('click', function() {
        const table = document.getElementById('reportTable');
        const range = document.createRange();
        range.selectNode(table);
        
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        
        try {
            const successful = document.execCommand('copy');
            const msg = successful ? 'Table copied to clipboard!' : 'Failed to copy table.';
            alert(msg);
        } catch (err) {
            console.error('Error copying table: ', err);
        }
        
        // Clear the selection
        selection.removeAllRanges();
    });
</script>

</html>