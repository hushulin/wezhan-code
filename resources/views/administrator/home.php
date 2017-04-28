<?php include_once 'header.php'; ?>
<style>
    .list-group-item {
        border: 0;
        border-top: 1px solid #ddd;
    }
    .list-group-item:first-child {
        border-radius: 0;
        border: 0;
    }
</style>

        <div id="page-wrapper">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">数据总览</h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-comments fa-5x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo number_format($data['today']['users']);?></div>
                                        <div>今日新增用户</div>
                                    </div>
                                </div>
                            </div>
                            <a href="/administrator/users">
                                <div class="panel-footer" style="border-top: 0;">
                                    <span class="pull-left">查看所有用户</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-green">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-tasks fa-5x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo number_format($data['today']['orders']);?></div>
                                        <div>今日新增订单</div>
                                    </div>
                                </div>
                            </div>
                            <a href="/administrator/orders">
                                <div class="panel-footer" style="border-top: 0;">
                                    <span class="pull-left">查看所有订单</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-yellow">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-shopping-cart fa-5x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo number_format($data['today']['payRequests']);?> CNY</div>
                                        <div>今日新增充值</div>
                                    </div>
                                </div>
                            </div>
                            <a href="/administrator/payRequests">
                                <div class="panel-footer" style="border-top: 0;">
                                    <span class="pull-left">查看所有充值</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-support fa-5x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?php echo number_format($data['today']['withdrawRequests']);?> CNY</div>
                                        <div>今日新增提现</div>
                                    </div>
                                </div>
                            </div>
                            <a href="/administrator/withdrawRequests">
                                <div class="panel-footer" style="border-top: 0;">
                                    <span class="pull-left">查看所有提现</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart-o fa-fw"></i> 今日盈亏
                            </div>
                            <div class="panel-body" style="padding: 0;">
                                <div class="list-group" style="margin: 0;">
                                    <a href="#" class="list-group-item">
                                        本日订单结算 <span class="pull-right text-muted"><em><?php echo number_format($data['count']['day']['stake']);?> CNY</em></span>
                                    </a>
                                    <a href="#" class="list-group-item">
                                        本日赠金总额 <span class="pull-right text-muted"><em><?php echo number_format($data['count']['day']['free']);?> CNY</em></span>
                                    </a>
                                    <a href="#" class="list-group-item">
                                        本日净利计算（本日订单结算 - 本日赠金总额） <span class="pull-right text-muted"><em><?php echo number_format($data['count']['day']['profit']);?> CNY</em></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart-o fa-fw"></i> 本月盈亏
                            </div>
                            <div class="panel-body" style="padding: 0;">
                                <div class="list-group" style="margin: 0;">
                                    <a href="#" class="list-group-item">
                                        本月订单结算 <span class="pull-right text-muted"><em><?php echo number_format($data['count']['month']['stake']);?> CNY</em></span>
                                    </a>
                                    <a href="#" class="list-group-item">
                                        本月赠金总额 <span class="pull-right text-muted"><em><?php echo number_format($data['count']['month']['free']);?> CNY</em></span>
                                    </a>
                                    <a href="#" class="list-group-item">
                                        本月净利计算（本月订单结算 - 本月赠金总额） <span class="pull-right text-muted"><em><?php echo number_format($data['count']['month']['profit']);?> CNY</em></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart-o fa-fw"></i> 累计盈亏
                            </div>
                            <div class="panel-body" style="padding: 0;">
                                <div class="list-group" style="margin: 0;">
                                    <a href="#" class="list-group-item">
                                        充值总额 <span class="pull-right text-muted"><em><?php echo number_format($data['count']['all']['payRequests']);?> CNY</em></span>
                                    </a>
                                    <a href="#" class="list-group-item">
                                        提现总额 <span class="pull-right text-muted"><em><?php echo number_format($data['count']['all']['withdrawRequests']);?> CNY</em></span>
                                    </a>
                                    <a href="#" class="list-group-item">
                                        结余总额 <span class="pull-right text-muted"><em><?php echo number_format($data['count']['all']['balance']);?> CNY</em></span>
                                    </a>
                                    <a href="#" class="list-group-item">
                                        赠金总额 <span class="pull-right text-muted"><em><?php echo number_format($data['count']['all']['free']);?> CNY</em></span>
                                    </a>
                                    <a href="#" class="list-group-item">
                                        净利计算（充值总额 - 结余总额 - 提现总额） <span class="pull-right text-muted"><em><?php echo number_format($data['count']['all']['profit']);?> CNY</em></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
<?php include_once 'footer.php'; ?>