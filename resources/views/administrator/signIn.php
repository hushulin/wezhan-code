<?php include_once 'header_blank.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">请登录</h3>
                    </div>
                    <div class="panel-body">
                        <form method="post" role="form">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="您的账户名称（邮件地址）" name="email" type="email" autofocus>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="您的密码" name="password" type="password" value="">
                                </div>
                                <input type="submit" class="btn btn-success btn-block" value="确认">
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
<?php include_once 'footer.php'; ?>