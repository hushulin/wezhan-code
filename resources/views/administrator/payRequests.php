<?php include_once 'header.php'; ?>
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row" style="margin-top: 20px;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>用户编号</th>
                                    <th>充值金额</th>
                                    <th>充值方式</th>
                                    <th>流水编号</th>
                                    <th>申请时间</th>
                                    <th>入账时间</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if (count($datas) == 0) { ?>
                                    <tr>
                                        <td colspan="7">暫時還沒有任何記錄</td>
                                    </tr>
                                <?php } ?>

                                <?php foreach ($datas as $item) { ?>
                                <tr>
                                    <td><?php echo $item->id; ?></td>
                                    <td><a href="/administrator/payRequests?id_user=<?php echo $item->id_user; ?>"><?php echo $item->id_user; ?></a> (<a href="/administrator/users?id_user=<?php echo $item->id_user; ?>"><?php echo $item->user->body_phone; ?></a>)</td>
                                    <td><?php echo $item->body_stake; ?></td>
                                    <td><?php 
                                        if($item->body_gateway == 'staff') echo '人工充值';
                                        else echo '在线支付';
                                    ?></td>
                                    <td><?php echo $item->body_transfer_number; ?></td>
                                    <td><?php echo $item->created_at; ?></td>
                                    <td><?php echo $item->processed_at; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" style="text-align: center;">
                                        <ul class="pagination">
                                            <li class="paginate_button previous"><a href="<?php echo $datas->appends([
                                                'id_user' => $id_user
                                            ])->previousPageUrl(); ?>">上一页</a></li>
                                            <li class="paginate_button active"><a href="#"><?php echo $datas->currentPage(); ?> / <?php echo $datas->lastPage(); ?>, 共 <?php echo $datas->total(); ?> 条记录</a></li>
                                            <li class="paginate_button next"><a href="<?php echo $datas->appends([
                                                'id_user' => $id_user
                                            ])->nextPageUrl(); ?>">下一页</a></li>
                                        </ul>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
<?php include_once 'footer.php'; ?>