<?php include 'template/header.php'; ?>


<?php
    $jsonFile       = __DIR__ . '/../../reportgroup.json';
    $jsonData       = file_get_contents($jsonFile);
    $reportGrpList  = json_encode($jsonData, true);
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
                    <form class="user" action="#" method="POST">
                        <textarea class="form-control" style="height: 700px;"><?= $jsonData; ?></textarea>
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