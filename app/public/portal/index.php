<?php include 'template/header.php'; ?>


<?php
    require '../../api/apiClient.php';

    $post_active = 0;
    $key         = '';
    $secret      = '';
    $token       = '';
    $accountId   = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and validate input data
        $accountId   = htmlspecialchars(trim($_POST["accountId"]));
        $key         = htmlspecialchars(trim($_POST["key"]));
        $secret      = htmlspecialchars(trim($_POST["secret"]));
        $token       = htmlspecialchars(trim($_POST["token"]));
        $post_active = 1;
    }

?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daily Automation Report</h1>
    </div>

    <div class="row">
        <div class="col-xl-3 col-lg-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">AWS Credentials</h6>
                </div>
                <div class="card-body">
                    <form class="user" action="#" method="POST">
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" id="accountId" name="accountId"
                                aria-describedby="Account ID" placeholder="Account ID" 
                                required autocomplete="off" value="<?php echo $accountId; ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" id="key" name="key"
                                aria-describedby="Key" placeholder="Key" 
                                required autocomplete="off" value="<?php echo $key; ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" id="secret" name="secret"
                                aria-describedby="Secret" placeholder="Secret"
                                required autocomplete="off" value="<?php echo $secret; ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" id="token" name="token"
                                aria-describedby="Token" placeholder="Token"
                                required autocomplete="off" value="<?php echo $token; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-user btn-block">Generate</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-9 col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daily Report</h6>
                </div>
                <div class="card-body">
                    <?php
                        if ($post_active) {
                            $apiClient = new APIClient($key, $secret, $token, $accountId);
                            $apiClient->generateCodeBuildReport();
                        } else {
                            echo '<div class="text-center">
                                    <img class="img-fluid px-4 px-sm-4 mt-3 mb-4" style="width: 15rem;"
                                        src="img/undraw_drink-coffee.svg" alt="...">
                                    <p>Nothing to see here yet.<br>Generate a report now!</p>
                                </div>';
                        }
                        
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<style>
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
</style>

<script>
    document.getElementById('copyButton').addEventListener('click', function() {
        const table = document.getElementById('dataTable');
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

<?php include 'template/footer.php'; ?>