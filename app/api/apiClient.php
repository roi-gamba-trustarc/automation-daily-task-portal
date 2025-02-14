<?php
require 'vendor/autoload.php';

use Aws\CodeBuild\CodeBuildClient;
use Aws\Exception\AwsException;

class APIClient {
    // Properties
    private string $key, $secret, $token, $accountId;
    // Constructor
    public function __construct(string $key, string $secret, string $token, string $accountId) {
        $this->key       = $key;
        $this->secret    = $secret;
        $this->token     = $token;
        $this->accountId = $accountId;

        // Validate credentials upon object creation
        $this->validateCredentials();
    }

    // Validation method
    private function validateCredentials(): void {
        if (empty($this->key) || empty($this->secret) || empty($this->token) || empty($this->accountId)) {
            throw new InvalidArgumentException("All credentials must be provided and cannot be empty.");
        }

        if (!ctype_alnum($this->key)) {
            throw new InvalidArgumentException("Key must be alphanumeric.");
        }

        if (!ctype_digit($this->accountId)) {
            throw new InvalidArgumentException("Account ID must be a numeric string.");
        }
    }

    public function generateCodeBuildReport(): void  {
        $failedStatus     = false;
        $onlyScanForToday = true;
        $jiraLink         = 'https://trustarc.atlassian.net/';
        $awsLink          = 'https://us-west-2.console.aws.amazon.com/';
        $errorMsg         = '';

        $jsonFile       = __DIR__ . '/../reportgroup.json';
        $jsonData       = file_get_contents($jsonFile);
        $reportGrpList  = json_decode($jsonData, true);
        $outputString   = '';

        try {
            $client = new CodeBuildClient([
                'version' => 'latest',
                'region' => 'us-west-2',
                'credentials' => [
                    'key' => $this->key,
                    'secret' => $this->secret,
                    'token' => $this->token
                ],
            ]);
    
             $outputString .= "
                <div class='table-responsive'>
                    <table class='table table-bordered' id='dataTable' width='100%' cellspacing='0'>
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
                        </thead>";
    
            foreach ($reportGrpList as $this->key => $reportGrp) {
                $reportGroupArn = "arn:aws:codebuild:us-west-2:" . $this->accountId . ":report-group/" . $this->key . "-SurefireReports";
    
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
            
                             $outputString .= "<tr>";
                             $outputString .= "<td><a href='" . $awsLink . "codesuite/codebuild/" . $this->accountId . "/projects/" . $this->key . "/history?region=us-west-2'>" . $this->key . "</a></td>"; // Name
                             $outputString .= "<td>" . $totalTests . "</td>"; // Test Scenarios
                             $outputString .= "<td><a target='_blank' href='" . $awsLink . "codesuite/codebuild/" . $this->accountId . "/testReports/reports/ccm-pr-roi-SurefireReports/" . $reportArnName . "?region=us-west-2'>" . $succeeded . "</a></td>"; // Passed
                             $outputString .= "<td>" . $failed . "</td>"; // Failed
                             $outputString .= "<td>" . $skipped . "</td>"; // Skipped
                             $outputString .= "<td>" . $blocker . "</td>"; // Blocker
                             $outputString .= "<td>" . $passedInLocalRun . "</td>"; // Passed in Local Run
                             $outputString .= "<td>" . $bugs . "</td>"; // Bug Found
                             $outputString .= "<td>" . $autoTicket . "</td>"; // Bug Found
                             $outputString .= "<td>" . $assignee . "</td>"; // Bug Found
                             $outputString .= "<td>" . $remarks . "</td>"; // Bug Found 
                             $outputString .= "</tr>";
                        }
                    }
     
                } catch (AwsException $e) {
                    $failed = true;
                    $errorMsg = $e->getMessage();
                }
            }
            
             $outputString .= "  </table>
                </div>";
    
             $outputString .= " <button id='copyButton' class='btn btn-primary btn-user'>Copy Table</button>";
        } catch (\Throwable $th) {
            $failedStatus = true;
            $errorMsg = $th->getMessage();
        }


        if($failedStatus){
            echo '<div class="text-center">
                <img class="img-fluid px-4 px-sm-4 mt-3 mb-4" style="width: 15rem;"
                    src="img/undraw_warning.svg" alt="...">
                <p>'.$errorMsg.'</p>
            </div>';
        } else {
            echo $outputString;
        }
    }
}

?>
