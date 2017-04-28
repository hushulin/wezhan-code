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
                                    <th>关联用户</th>
                                    <th>关联订单</th>
                                    <th>关联充值</th>
                                    <th>关联提现</th>
                                    <th>变动缘由</th>
                                    <th>变动方向</th>
                                    <th>变动金额</th>
                                    <th>变动时间</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if (count($datas) == 0) { ?>
                                    <tr>
                                        <td colspan="10">暫時還沒有任何記錄</td>
                                    </tr>
                                <?php } ?>

                                <?php foreach ($datas as $item) { ?>
                                <tr>
                                    <td><?php echo $item->id; ?></td>
                                    <td><a href="/administrator/records?id_user=<?php echo $item->id_user; ?>"><?php echo $item->id_user; ?></a> (<a href="/administrator/users?id_user=<?php echo $item->id_user; ?>"><?php echo $item->user->body_phone; ?></a>)</td>
                                    <td><a href="/administrator/users?id_user=<?php echo $item->id_refer; ?>"><?php echo $item->id_refer; ?></a></td>
                                    <td><a href="/administrator/orders?id_order=<?php echo $item->id_order; ?>"><?php echo $item->id_order; ?></a></td>
                                    <td><a href="/administrator/payRequests?id_payRequest=<?php echo $item->id_payRequest; ?>"><?php echo $item->id_payRequest; ?></a></td>
                                    <td><a href="/administrator/withdrawRequests?id_withdrawRequest=<?php echo $item->id_withdrawRequest; ?>"><?php echo $item->id_withdrawRequest; ?></a></td>
                                    <td><?php echo $item->body_name; ?></td>
                                    <td><?php echo $item->body_direction == 0 ? '<span style="color: green;">支出</span>' : '<span style="color: red;">收入</span>'; ?></td>
                                    <td><?php echo $item->body_stake; ?></td>
                                    <td><?php echo $item->created_at; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="10" style="text-align: center;">
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