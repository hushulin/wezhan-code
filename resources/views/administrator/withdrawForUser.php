<?php include_once 'header.php'; ?>
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row" style="margin-top: 40px;">
                    <div class="col-lg-12">
                        <form method="post" role="form">
                            <div class="form-group input-group">
                                <span class="input-group-addon">提现编号</span>
                                <input disabled="disabled" type="text" name="id_user" class="form-control" value="<?php echo $id; ?>">
                            </div>
                            <div class="form-group input-group">
                                <span class="input-group-addon">流水编号</span>
                                <input type="text" name="transfer_number" class="form-control" value="<?php echo $transfer_number; ?>">
                            </div>
<?php if($processed_at == '0000-00-00 00:00:00'){ ?>
                            <div style="text-align: center">
                                <button type="submit" class="btn btn-primary">标记该提现已经处理完毕</button>
                                <a href="/administrator/withdrawRequests/<?php echo $id; ?>/cancel" class="btn btn-danger">标记该提现取消并退回余额</a>
                            </div>
<?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
<?php include_once 'footer.php'; ?>