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
                                    <th>用户状态</th>
                                    <th>提现金额</th>
                                    <th>开户银行</th>
                                    <th>开户名称</th>
                                    <th>开户帐号</th>
                                    <th>开户网点</th>
                                    <th>流水编号</th>
                                    <th>申请时间</th>
                                    <th>处理时间</th>
                                    <th>操作</th>
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
                                    <td><a href="/administrator/withdrawRequests?id_user=<?php echo $item->id_user; ?>"><?php echo $item->id_user; ?></a> (<a href="/administrator/users?id_user=<?php echo $item->id_user; ?>"><?php echo $item->user->body_phone; ?></a>)</td>
                                    <td><?php echo $item->user->is_disabled == 0 ? '<span style="color: green;">正常</span>' : '<span style="color: red;">封停</span>'; ?></td>
                                    <td><?php echo $item->body_stake; ?></td>
                                    <td><?php 
                                        if($item->body_bank == 'ccb') echo '建设银行';
                                        if($item->body_bank == 'icbc') echo '工商银行';
                                        if($item->body_bank == 'boc') echo '中国银行';
                                        if($item->body_bank == 'abc') echo '农业银行';
                                        if($item->body_bank == 'comm') echo '交通银行';
                                        if($item->body_bank == 'spdb') echo '浦发银行';
                                        if($item->body_bank == 'ecb') echo '光大银行';
                                        if($item->body_bank == 'cmbc') echo '民生银行';
                                        if($item->body_bank == 'cib') echo '兴业银行';
                                        if($item->body_bank == 'cmb') echo '招商银行';
                                        if($item->body_bank == 'psbc') echo '邮政储蓄';
                                    ?></td>
                                    <td><?php echo $item->body_name; ?></td>
                                    <td><?php echo $item->body_number; ?></td>
                                    <td><?php echo $item->body_deposit; ?></td>
                                    <td><?php echo $item->body_transfer_number; ?></td>
                                    <td><?php echo $item->created_at; ?></td>
                                    <td><?php echo $item->processed_at; ?></td>
                                    <td><?php 
                                        if ($item->processed_at == '0000-00-00 00:00:00'){
                                            echo('<a href="/administrator/withdrawRequests/' . $item->id . '">处理提现</a>');
                                        }
                                    ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="12" style="text-align: center;">
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