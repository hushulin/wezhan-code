<?php include_once 'header.php'; ?>
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row" style="margin-top: 20px;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>状态</th>
                                    <th>介绍人编号</th>
                                    <th>电话号码</th>
                                    <th>账户余额</th>
                                    <th>累积交易</th>
                                    <th>累积盈利</th>
                                    <th>下线交易</th>
                                    <th>注册时间</th>
                                    <th width="600">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datas as $item) { ?>
                                <tr>
                                    <td><?php echo $item->id; ?></td>
                                    <td><?php echo $item->is_disabled == 0 ? '<span style="color: green;">正常</span>' : '<span style="color: red;">封停</span>'; ?></td>
                                    <td><?php echo $item->id_introducer == 0 ? '自发注册' : '<a href="/administrator/users?id_user=' . $item->id_introducer . '">' . $item->id_introducer . '</a>'; ?></td>
                                    <td><?php echo $item->body_phone == 0 ? '尚未激活' : $item->body_phone; ?></td>
                                    <td><?php echo $item->body_balance; ?></td>
                                    <td><?php echo $item->body_transactions; ?></td>
                                    <td><?php echo $item->body_bonus; ?></td>
                                    <td><?php echo $item->body_transactions_network; ?></td>
                                    <td><?php echo $item->created_at; ?></td>
                                    <td>
                                        <a href="/administrator/orders?id_user=<?php echo $item->id; ?>">订单记录</a> | <a href="/administrator/records?id_user=<?php echo $item->id; ?>">资金记录</a> | <a href="/administrator/payRequests?id_user=<?php echo $item->id; ?>">充值记录</a> | <a href="/administrator/withdrawRequests?id_user=<?php echo $item->id; ?>">提现记录</a> | <a href="/administrator/payRequests/<?php echo $item->id; ?>">人工充值</a> | <a href="/administrator/users/<?php echo $item->id; ?>/withhold">人工扣款</a> | <a href="/administrator/users?id_introducer=<?php echo $item->id; ?>">名下用户</a> | <a href="/administrator/users/<?php echo $item->id; ?>/status">封禁切换</a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="10" style="text-align: center;">
                                        <ul class="pagination">
                                            <li class="paginate_button previous"><a href="<?php echo $datas->previousPageUrl(); ?>">上一页</a></li>
                                            <li class="paginate_button active"><a href="#"><?php echo $datas->currentPage(); ?> / <?php echo $datas->lastPage(); ?>, 共 <?php echo $datas->total(); ?> 条记录</a></li>
                                            <li class="paginate_button next"><a href="<?php echo $datas->nextPageUrl(); ?>">下一页</a></li>
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