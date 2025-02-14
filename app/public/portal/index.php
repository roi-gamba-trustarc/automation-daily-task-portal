<?php include 'template/header.php'; ?>

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
                    <form class="user">
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" id="accountId" name="accountId"
                                aria-describedby="Account ID" placeholder="Account ID">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" id="key" name="key"
                                aria-describedby="Key" placeholder="Key">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" id="secret" name="secret"
                                aria-describedby="Secret" placeholder="Secret">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control form-control-user" id="token" name="token"
                                aria-describedby="Token" placeholder="Token">
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
                    <div class="text-center">
                        <img class="img-fluid px-4 px-sm-4 mt-3 mb-4" style="width: 15rem;"
                            src="img/undraw_drink-coffee.svg" alt="...">
                        <p>Nothing to see here yet.<br>Generate a report now!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<?php include 'template/footer.php'; ?>