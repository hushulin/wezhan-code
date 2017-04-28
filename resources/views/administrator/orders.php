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
                                    <th>交易标的</th>
                                    <th>买入价格</th>
                                    <th>买入金额</th>
                                    <th>买入方向</th>
                                    <th>买入时长</th>
                                    <th>买入时间</th>
                                    <th>结算价格</th>
                                    <th>结算结果</th>
                                    <th>结算时间</th>
                                    <th>结算调控</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if (count($datas) == 0) { ?>
                                    <tr>
                                        <td colspan="11">暫時還沒有任何記錄</td>
                                    </tr>
                                <?php } ?>

                                <?php foreach ($datas as $item) { ?>
                                <tr>
                                    <td><?php echo $item->id; ?></td>
                                    <td><a href="/administrator/orders?id_user=<?php echo $item->id_user; ?>"><?php echo $item->id_user; ?></a> (<a href="/administrator/users?id_user=<?php echo $item->id_user; ?>"><?php echo $item->user->body_phone; ?></a>)</td>
                                    <td><?php echo $item->user->is_disabled == 0 ? '<span style="color: green;">正常</span>' : '<span style="color: red;">封停</span>'; ?></td>
                                    <td><a href="/administrator/orders?id_object=<?php echo $item->id_object; ?>"><?php echo $item->object->body_name; ?></a></td>
                                    <td><?php echo $item->body_price_buying; ?></td>
                                    <td><?php echo $item->body_stake; ?></td>
                                    <td><?php echo $item->body_direction == 0 ? '<span style="color: green;">看跌</span>' : '<span style="color: red;">看涨</span>'; ?></td>
                                    <td><?php echo $item->body_time; ?>秒</td>
                                    <td><?php echo $item->created_at; ?></td>
                                    <td><?php echo $item->body_price_striked; ?></td>
                                    <td><?php 
                                        if ($item->striked_at == '0000-00-00 00:00:00'){
                                            echo '尚未结算';
                                        } else {
                                            if($item->body_is_draw == 1) echo '平局';
                                            else if($item->body_is_win == 1) echo '<span style="color: red;">盈利</span>';
                                            else echo '<span style="color: green;">亏损</span>';
                                        }
                                    ?></td>
                                    <td><?php echo $item->striked_at; ?></td>
                                    <td><?php echo $item->body_is_controlled == 0 ? '否' : '是'; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="13" style="text-align: center;">
                                        <ul class="pagination">
                                            <li class="paginate_button previous"><a href="<?php echo $datas->appends([
                                                'id_user' => $id_user,
                                                'id_object' => $id_object
                                            ])->previousPageUrl(); ?>">上一页</a></li>
                                            <li class="paginate_button active"><a href="#"><?php echo $datas->currentPage(); ?> / <?php echo $datas->lastPage(); ?>, 共 <?php echo $datas->total(); ?> 条记录</a></li>
                                            <li class="paginate_button next"><a href="<?php echo $datas->appends([
                                                'id_user' => $id_user,
                                                'id_object' => $id_object
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