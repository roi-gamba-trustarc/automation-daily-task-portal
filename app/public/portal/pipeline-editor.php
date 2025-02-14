<?php include 'template/header.php'; ?>


<?php
    $jsonFile       = __DIR__ . '/../../reportgroup.json';
    $success        = false;

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $jsonData = $_POST['jsonData'];

        // Validate JSON before saving
        if (json_decode($jsonData) === null && json_last_error() !== JSON_ERROR_NONE) {
            $message = "Invalid JSON format!";
        } else {
            file_put_contents($jsonFile, $jsonData);
            $success = true;
            $message = "JSON file successfully updated!";
        }
    }

    $jsonContent    = file_exists($jsonFile) ? file_get_contents($jsonFile) : "";
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pipeline Details Editor</h1>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">JSON Editor</h6>
                </div>
                <div class="card-body">
                    <p>This is where I'm modifying the details for static columns for now (Blocker, Pass in Local Run, Bug Found, Auto Ticket for fixes, Assignee, and Remarks)</p>
                    
                    <?php
                        if($success):
                            echo '<div class="alert alert-success alert-dismissible" id="alert" role="alert">
                                    '.$message.'
                                </div>';
                        endif;
                    ?>
                    <form class="user" action="#" method="POST">
                        <textarea class="form-control" style="height: 700px;" name="jsonData"><?= $jsonContent; ?></textarea>
                        <button type="submit" class="btn btn-primary btn-icon-split mt-2">
                            <span class="icon text-white-50">
                                <i class="fas fa-save"></i>
                            </span>
                            <span class="text">Save Changes</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->


<?php include 'template/footer.php'; ?>

<script>
    setTimeout(() => {
        let alert = document.getElementById("alert");
        if (alert) {
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close(); // Triggers Bootstrap's fade-out animation
        }
    }, 3000); // 3000ms = 3 seconds
</script>

<style>
    .alert {
        transition: opacity 0.5s ease-in-out;
    }
</style>