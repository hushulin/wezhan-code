window.app = {
    instance: {
        body: null,
        controller: null
    },
    services: {
        api: function ($method, $url, $params, $callback_success, $callback_error) {
            return $.ajax({
                type: $method, cache: false, data: $params, url: '/api/' + $url,
                error: $callback_error,
                success: $callback_success
            });
        },
        dialog: {
            alert: function ($title, $content) {
                var html = $('#templet_dialog_alert').html();
                html = html.replace(/#TITLE#/, $title);
                html = html.replace(/#CONTENT#/, $content);
                app.instance.body.append(html);
            },
            confirm: function ($title, $content, $callback) {
                var html = $('#templet_dialog_confirm').html();
                html = html.replace(/#TITLE#/, $title);
                html = html.replace(/#CONTENT#/, $content);
                app.instance.body.append(html);
                $('a#app_dialog_callback').one('click', $callback);
            },
            toast: function ($content) {
                $('#contentToast p.weui_toast_content').html($content);
                $('#contentToast').show('fast');
                setTimeout(function () {
                    $('#contentToast').hide('fast');
                }, 2000);
            },
            remove: function () {
                $('div.app_dialog').remove();
            }
        }
    },
    controllers: {
        objectsController: {
            $liveRequest: null,
            $liveRequestInterval: null,
            $liveResponse: null,
            init: function () {
                this.instance.body.on('click', 'td', this.instance.controller.clickedObject);
                this.instance.controller.initRunRequest();
            },
            initRunRequest: function () {
                app.instance.controller.$liveRequestInterval = setInterval(app.instance.controller.runRequest, 1000);
            },
            clickedObject: function () {
                top.location.href = '/objects/' + $(this).parents('tr').attr('data-id') + '/60';
            },
            runRequest: function () {
                if (app.instance.controller.$liveRequest != null) app.instance.controller.$liveRequest.abort();
                app.instance.controller.$liveRequest = app.services.api('GET', 'objects', {}, function ($response) {
                    app.instance.controller.$liveResponse = $response;
                    if (!app.instance.controller.$liveResponse.user) top.location.reload();
                    else app.instance.controller.runRender();
                }, function () { });
            },
            runRender: function () {
                $('span.user_body_balance').html(app.instance.controller.$liveResponse.user.body_balance + ' CNY');
                for (var $index = 0; $index < app.instance.controller.$liveResponse.objects.length; $index++) {
                    var $item = app.instance.controller.$liveResponse.objects[$index];
                    var $itemDom = $('tr[data-id=' + $item.id + ']');
                    $itemDom.find('.price').html(parseFloat($item.body_price).toFixed($item.body_price_decimal));
                    if (parseFloat($item.body_price_previous) > parseFloat($item.body_price)) {
                        $itemDom.find('.price').removeClass('red').addClass('green');
                    } else {
                        $itemDom.find('.price').removeClass('green').addClass('red');
                    }
                }
            }
        },
        objectsDetailController: {
            $liveRequest: null,
            $liveRequestInterval: null,
            $liveRequestEnabled: true,
            $liveResponse: null,
            $liveChart: null,
            $liveChartData: [],
            $liveChartVolumeData: [],
            $liveChartRangeSelected: 0,
            $liveCountDownInterval: null,
            $liveCountDownHeartbeat: 0,
            init: function () {
                
                this.instance.body.on('click', 'td[data-period]', this.instance.controller.clickedPeriod);
                this.instance.body.on('click', 'a#orderUp', this.instance.controller.clickedOrderUp);
                this.instance.body.on('click', 'a#orderDown', this.instance.controller.clickedOrderDown);
                this.instance.body.on('click', 'input#select_stake', this.instance.controller.clickedStakeList);
                this.instance.body.on('click', 'input#select_time', this.instance.controller.clickedTimeList);
                this.instance.controller.runRequest();

                $('#liveChart').css('height', ($('body').height() - $('div.objectsDetail').height() - $('div.bottomLine').height() - $('div.navigator').height()) + 'px');
                $('#liveChart').css('top', $('div.objectsDetail').height() + 'px');

            },
            initLiveChart: function () {

                var originalDrawPoints = Highcharts.seriesTypes.column.prototype.drawPoints;
                Highcharts.seriesTypes.column.prototype.drawPoints = function () {
                    var merge = Highcharts.merge,
                        series = this,
                        chart = this.chart,
                        points = series.points,
                        i = points.length;

                    while (i--) {
                        if (typeof (chart.series[0].points[i]) != "undefined" && typeof (chart.series[0].points[i]) != undefined) {
                            var candlePoint = chart.series[0].points[i];
                            if (candlePoint.open != undefined && candlePoint.close != undefined) {
                                var color = (candlePoint.open < candlePoint.close) ? '#FF3232' : '#54FCFC';
                                var seriesPointAttr = merge(series.pointAttr);
                                seriesPointAttr[''].fill = color;
                                seriesPointAttr.hover.fill = color;
                                seriesPointAttr.select.fill = color;
                            } else {
                                var seriesPointAttr = merge(series.pointAttr);
                            }
                            points[i].pointAttr = seriesPointAttr;
                        }
                    }
                    originalDrawPoints.call(this);
                }

                Highcharts.setOptions({
                    global: {
                        useUTC: false
                    },
                    lang: {
                        resetZoom: '还原',
                        resetZoomTitle: '还原',
                        thousandsSep: ',',
                        months: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                        shortMonths: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                        weekdays: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
                        numericSymbols: null
                    }
                });

                if ($('table.objectsDetail').attr('data-period') == 60) {
                    app.instance.controller.$liveChartRangeSelected = 0;
                } else if ($('table.objectsDetail').attr('data-period') == 300) {
                    app.instance.controller.$liveChartRangeSelected = 1;
                } else if ($('table.objectsDetail').attr('data-period') == 900) {
                    app.instance.controller.$liveChartRangeSelected = 2;
                } else if ($('table.objectsDetail').attr('data-period') == 1800) {
                    app.instance.controller.$liveChartRangeSelected = 3;
                } else if ($('table.objectsDetail').attr('data-period') == 3600) {
                    app.instance.controller.$liveChartRangeSelected = 4;
                } else if ($('table.objectsDetail').attr('data-period') == 86400) {
                    app.instance.controller.$liveChartRangeSelected = 5;
                } else if ($('table.objectsDetail').attr('data-period') == 604800) {
                    app.instance.controller.$liveChartRangeSelected = 6;
                } else if ($('table.objectsDetail').attr('data-period') == 2592000) {
                    app.instance.controller.$liveChartRangeSelected = 7;
                }

                app.instance.controller.$liveChart = $('#liveChart').highcharts('StockChart', {
                    chart: {
                        animation: true,
                        backgroundColor: '#000',
                        margin: '0',
                        marginRight: '50',
                        zoomType: 'x',
                        events: {
                            load: function () {

                                var self = this;

                                if(!app.instance.controller.$liveRequestEnabled){
                                    return false;
                                }

                                app.instance.controller.$liveRequestInterval = setInterval(function () {

                                    if (app.instance.controller.$liveRequest != null) app.instance.controller.$liveRequest.abort();
                                    app.instance.controller.$liveRequest = app.services.api('GET', 'objects/' + $('table.objectsDetail').attr('data-id') + '/' + $('table.objectsDetail').attr('data-period') + '/update', {}, function ($response) {

                                        app.instance.controller.$liveResponse = $response;
                                        app.instance.controller.runRender();

                                        var $created_at = (new Date($response.lines.created_at.replace(/-/g, '/'))).getTime();
                                        if ($created_at != self.series[0].xData[self.series[0].xData.length - 1]) { // 新增
                                            self.series[0].addPoint([$created_at, parseFloat($response.lines.body_open), parseFloat($response.lines.body_high), parseFloat($response.lines.body_low), parseFloat($response.lines.body_close)]);
                                            self.series[1].addPoint([$created_at, parseFloat($response.lines.body_volume)]);
                                        } else { // 更新
                                            self.series[0].removePoint(self.series[0].xData.length - 1);
                                            self.series[1].removePoint(self.series[1].xData.length - 1);
                                            self.series[0].addPoint([$created_at, parseFloat($response.lines.body_open), parseFloat($response.lines.body_high), parseFloat($response.lines.body_low), parseFloat($response.lines.body_close)]);
                                            self.series[1].addPoint([$created_at, parseFloat($response.lines.body_volume)]);
                                        }

                                        self.yAxis[0].removePlotLine('plot-line-1');
                                        self.yAxis[0].addPlotLine({
                                            value: parseFloat($response.object.body_price).toFixed($response.object.body_price_decimal),
                                            width: 1,
                                            color: '#CCCCCC',
                                            dashStyle: 'solid',
                                            id: 'plot-line-1',
                                            zIndex: 99999,
                                            label: {
                                                text: parseFloat($response.object.body_price).toFixed($response.object.body_price_decimal),
                                                align: 'right',
                                                verticalAlign: 'bottom',
                                                x: 48,
                                                style: {
                                                    "color": '#FFF'
                                                }
                                            }
                                        });

                                    }, function () { });
                                }, 1000);

                            }
                        }
                    },
                    exporting: { enabled: false },
                    credits: { enabled: false },
                    navigator: {
                        height: 30,
                        margin: 40
                    },
                    scrollbar: {
                        enabled: true,
                        liveRedraw: true,
                        height: 0,
                        barBackgroundColor: '#F00',
                        barBorderColor: '#000',
                        buttonArrowColor: '#FFF',
                        buttonBackgroundColor: '#FFF',
                        buttonBorderColor: '#000',
                        trackBackgroundColor: '#000',
                        trackBorderColor: '#000'
                    },
                    tooltip: {
                        borderWidth: 0,
                        borderRadius: 0,
                        snap: 10,
                        positioner: function () {
                            return { x: 5, y: 25 };
                        },
                        shadow: false,
                        crosshairs: [true, true]
                    },
                    rangeSelector: {
                        enabled: true,
                        buttons: [{
                            type: 'minute',
                            count: 30,
                            text: '30分钟'
                        }, {
                            type: 'minute',
                            count: 150,
                            text: '150分钟'
                        }, {
                            type: 'minute',
                            count: 450,
                            text: '450分钟'
                        }, {
                            type: 'minute',
                            count: 900,
                            text: '900分钟'
                        }, {
                            type: 'minute',
                            count: 1800,
                            text: '1800分钟'
                        }, {
                            type: 'minute',
                            count: 43200,
                            text: '43200分钟'
                        }, {
                            type: 'minute',
                            count: 302400,
                            text: '302400分钟'
                        }, {
                            type: 'minute',
                            count: 1296000,
                            text: '1296000分钟'
                        }, {
                            type: 'all',
                            text: '所有'
                        }],
                        inputEnabled: false,
                        labelStyle: {
                            color: 'silver',
                            fontWeight: 'bold'
                        },
                        selected: app.instance.controller.$liveChartRangeSelected
                    },
                    title: {
                        text: ''
                    },
                    plotOptions: {
                        column: {
                            groupPadding: 0.1
                        },
                        candlestick: {
                            groupPadding: 0.1
                        }
                    },
                    series: [{
                        type: 'candlestick',
                        name: $('table').attr('data-name'),
                        data: app.instance.controller.$liveChartData,
                        color: '#54FCFC',
                        lineColor: '#54FCFC',
                        upColor: '#000000',
                        upLineColor: "#FF3232"
                    }, {
                        type: 'column',
                        name: '交易量',
                        data: app.instance.controller.$liveChartVolumeData,
                        yAxis: 1
                    }],
                    yAxis: [{
                        title: {
                            text: ''
                        },
                        labels: {
                            align: 'right',
                            x: 48,
                            style: {
                                "color": "#B6322B"
                            },
                            formatter: function () {
                                var valueStr = this.value.toString();
                                var valueStrLen = valueStr.length;
                                var valueStrDotPostion = valueStr.indexOf('.');
                                var zeroStr = '';
                                if (app.instance.controller.$liveResponse.object.body_price_decimal > 0) {
                                    if (valueStrDotPostion < 0) {
                                        for (var j = 1; j <= app.instance.controller.$liveResponse.object.body_price_decimal; j++) {
                                            zeroStr = zeroStr + '0';
                                        }
                                        return this.value + '.' + zeroStr;
                                    }
                                    else if (valueStrLen - valueStrDotPostion - 1 < app.instance.controller.$liveResponse.object.body_price_decimal) {
                                        var len = app.instance.controller.$liveResponse.object.body_price_decimal - (valueStrLen - valueStrDotPostion - 1);
                                        for (var j = 1; j <= len; j++) {
                                            zeroStr = zeroStr + '0';
                                        }
                                        return this.value + zeroStr;
                                    }
                                    else {
                                        return this.value;
                                    }
                                }
                                else {
                                    return this.value;
                                }
                            }
                        },
                        plotLines: [{
                            value: parseFloat(app.instance.controller.$liveResponse.object.body_price).toFixed(app.instance.controller.$liveResponse.object.body_price_decimal),
                            width: 1,
                            color: '#CCCCCC',
                            dashStyle: 'solid',
                            id: 'plot-line-1',
                            zIndex: 99999,
                            label: {
                                text: parseFloat(app.instance.controller.$liveResponse.object.body_price).toFixed(app.instance.controller.$liveResponse.object.body_price_decimal),
                                align: 'right',
                                verticalAlign: 'bottom',
                                x: 48,
                                style: {
                                    "color": '#FFF'
                                }
                            }
                        }],
                        height: '60%',
                        lineWidth: 1,
                        lineColor: '#800000',
                        gridLineColor: '#800000',
                        gridLineDashStyle: 'shortdash'
                    }, {
                        title: {
                            text: ''
                        },
                        labels: {
                            align: 'right',
                            x: 25,
                            style: {
                                "color": "#B6322B"
                            }
                        },
                        top: '65%',
                        height: '35%',
                        lineWidth: 1,
                        lineColor: '#800000',
                        gridLineColor: '#800000',
                        gridLineDashStyle: 'shortdash'
                    }]
                });
            },
            clickedPeriod: function () {
                top.location.href = '/objects/' + $(this).parents('table').attr('data-id') + '/' + $(this).attr('data-period');
            },
            clickedStakeList: function () {
                $('#stakeSelector ul').show().width($('#stakeSelector input').width() + 22);
            },
            clickedTimeList: function () {
                $('#timeSelector ul').show().width($('#timeSelector input').width() + 22);
            },
            clickedOrderUp: function () {
                if (!app.instance.controller.runCheckBeforeOrder()) return false;
                var html = $('#templet_dialog_confirm').html();
                html = html.replace(/#STAKE#/, $('#select_stake').val());
                html = html.replace(/#TIME#/, $('#select_time').val());
                html = html.replace(/#COLOR#/g, '#ed0000');
                app.instance.body.append(html);
                $('#confirm_up').show();
                $('#confirm_down').hide();
                $('a#app_dialog_callback').one('click', function () {
                    app.services.dialog.remove();
                    app.instance.controller.runOrder(1);
                });
            },
            clickedOrderDown: function () {
                if (!app.instance.controller.runCheckBeforeOrder()) return false;
                var html = $('#templet_dialog_confirm').html();
                html = html.replace(/#STAKE#/, $('#select_stake').val());
                html = html.replace(/#TIME#/, $('#select_time').val());
                html = html.replace(/#COLOR#/g, '#00ff0a');
                app.instance.body.append(html);
                $('#confirm_down').show();
                $('#confirm_up').hide();
                $('a#app_dialog_callback').one('click', function () {
                    app.services.dialog.remove();
                    app.instance.controller.runOrder(0);
                });
            },
            runCheckBeforeOrder: function () {
                if (!app.instance.controller.$liveResponse.object.status) {
                    app.services.dialog.alert('非常抱歉', '该商品处于休市状态');
                    return false;
                } else return true;
            },
            runOrder: function ($direction) {

                $('#loadingToast').show();

                var time = 60;
                if ($('#select_time').val() == '5M') time = 300;
                if ($('#select_time').val() == '15M') time = 900;
                if ($('#select_time').val() == '30M') time = 1800;
                if ($('#select_time').val() == '1H') time = 3600;

                app.services.api('POST', 'order', {
                    object: app.instance.controller.$liveResponse.object.id,
                    stake: $('#select_stake').val(),
                    time: time,
                    direction: $direction
                }, function ($response) {
                    $('#loadingToast').hide();
                    if ($response.error) {
                        app.services.dialog.alert('非常抱歉', $response.error);
                    } else {

                        var html = $('#templet_order_countDown').html();
                        html = html.replace(/#STAKE#/, $('#select_stake').val());
                        html = html.replace(/#TIME#/, $('#select_time').val());
                        html = html.replace(/#PRICE#/, parseFloat($response.result.body_price_buying).toFixed(app.instance.controller.$liveResponse.object.body_price_decimal));

                        if ($response.result.body_direction == 1) html = html.replace(/#COLOR#/g, '#ed0000');
                        else html = html.replace(/#COLOR#/g, '#00ff0a');

                        app.instance.body.append(html);

                        if ($response.result.body_direction == 1) {
                            $('#confirm_up').show();
                            $('#confirm_down').hide();
                        } else {
                            $('#confirm_down').show();
                            $('#confirm_up').hide();
                        }

                        $('a#app_dialog_callback').one('click', function () {
                            app.services.dialog.remove();
                        });

                        app.instance.controller.$liveCountDownHeartbeat = 0;
                        clearInterval(app.instance.controller.$liveCountDownInterval);
                        app.instance.controller.$liveCountDownInterval = setInterval(function () {

                            if ($response.result.body_direction == 1) {
                                if (app.instance.controller.$liveResponse.object.body_price > parseFloat($response.result.body_price_buying)) {
                                    $('span#openPrice').css('color', '#ed0000');
                                }
                                if (app.instance.controller.$liveResponse.object.body_price < parseFloat($response.result.body_price_buying)) {
                                    $('span#openPrice').css('color', '#00ff0a');
                                }
                            } else {
                                if (app.instance.controller.$liveResponse.object.body_price > parseFloat($response.result.body_price_buying)) {
                                    $('span#openPrice').css('color', '#00ff0a');
                                }
                                if (app.instance.controller.$liveResponse.object.body_price < parseFloat($response.result.body_price_buying)) {
                                    $('span#openPrice').css('color', '#ed0000');
                                }
                            }

                            var distance = parseInt($response.result.distance) - app.instance.controller.$liveCountDownHeartbeat - 1;

                            var hh = Math.floor(distance / (60 * 60) % 24);
                            var mm = Math.floor(distance / 60 % 60);
                            var ss = Math.floor(distance % 60);


                            if (parseInt(hh) == 0 && parseInt(mm) == 0 && parseInt(ss) == 0) {

                                app.instance.controller.$liveRequestEnabled = false;
                                app.instance.controller.$liveCountDownHeartbeat = 0;
                                clearInterval(app.instance.controller.$liveCountDownInterval);

                                $('span.countDownTitle').html('正在结算中...');

                                setTimeout(function () {
                                    app.services.api('GET', 'orders/' + $response.result.id, {}, function ($response) {

                                        $('#doneTitle').html('成交价格');
                                        $('#donePrice').removeClass('price');
                                        $('#donePrice').html(parseFloat($response.item.body_price_striked).toFixed(app.instance.controller.$liveResponse.object.body_price_decimal));
                                        $('span.countDownTitle').html('交易完成');

                                        var $bonus = 0;
                                        if ($response.item.body_is_win == 1) {
                                            $bonus = $response.item.body_bonus;
                                        } else if ($response.item.body_is_draw == 1) {
                                            $bonus = 0;
                                        } else {
                                            $bonus = '-' + $response.item.body_stake;
                                        }

                                        $('span.countDownClock').html('收益: ' + $bonus);

                                        app.instance.controller.$liveRequestEnabled = true;

                                    }, function () { });
                                }, 2000);

                            } else {
                                app.instance.controller.$liveCountDownHeartbeat = app.instance.controller.$liveCountDownHeartbeat + 1;
                            }

                            $('span.countDownClock').html(app.instance.controller.runInsertZero(hh) + ':' + app.instance.controller.runInsertZero(mm) + ':' + app.instance.controller.runInsertZero(ss));

                        }, 1000);

                    }
                }, function () {
                    $('#loadingToast').hide();
                    app.services.dialog.alert('非常抱歉', '暂时无法连接服务器，请稍后再试');
                });
            },
            runInsertZero: function ($number) {
                if ($number < 10) $number = '0' + $number;
                if ($number == 0) $number = '00';
                return $number;
            },
            runUpdateChartData: function () {
                for (var $index = 0; $index < app.instance.controller.$liveResponse.lines.length; $index++) {
                    var $item = app.instance.controller.$liveResponse.lines[$index];
                    app.instance.controller.$liveChartData.push([
                        (new Date($item.created_at.replace(/-/g, '/'))).getTime(),
                        parseFloat($item.body_open),
                        parseFloat($item.body_high),
                        parseFloat($item.body_low),
                        parseFloat($item.body_close)
                    ]);
                    app.instance.controller.$liveChartVolumeData.push([
                        (new Date($item.created_at.replace(/-/g, '/'))).getTime(),
                        parseFloat($item.body_volume)
                    ]);
                }
                app.instance.controller.$liveChartData.reverse();
                app.instance.controller.$liveChartVolumeData.reverse();
            },
            runRequest: function () {
                if (app.instance.controller.$liveRequest != null) app.instance.controller.$liveRequest.abort();
                app.instance.controller.$liveRequest = app.services.api('GET', 'objects/' + $('table.objectsDetail').attr('data-id') + '/' + $('table.objectsDetail').attr('data-period'), {}, function ($response) {
                    app.instance.controller.$liveResponse = $response;
                    app.instance.controller.runUpdateChartData();
                    app.instance.controller.initLiveChart();
                    app.instance.controller.runRender();
                }, function () { });
            },
            runRender: function () {
                $('td.price,span.price').html(parseFloat(app.instance.controller.$liveResponse.object.body_price).toFixed(app.instance.controller.$liveResponse.object.body_price_decimal));
                if (parseFloat(app.instance.controller.$liveResponse.object.body_price_previous) > parseFloat(app.instance.controller.$liveResponse.object.body_price)) {
                    $('td.price,span.price').removeClass('red').addClass('green');
                } else {
                    $('td.price,span.price').removeClass('green').addClass('red');
                }
                $('span.updateTime').html(app.instance.controller.$liveResponse.object.updated_at);
            }
        },
        ordersHoldController: {
            $liveRequest: null,
            $liveRequestInterval: null,
            $liveResponse: null,
            init: function () {
                this.instance.controller.initRunRequest();
            },
            initRunRequest: function () {
                app.instance.controller.$liveRequestInterval = setInterval(app.instance.controller.runRequest, 1000);
            },
            runRequest: function () {
                if (app.instance.controller.$liveRequest != null) app.instance.controller.$liveRequest.abort();
                app.instance.controller.$liveRequest = app.services.api('GET', 'objects', {}, function ($response) {
                    app.instance.controller.$liveResponse = $response;
                    if (!app.instance.controller.$liveResponse.user) top.location.reload();
                    else app.instance.controller.runRender();
                }, function () { });
            },
            runRender: function () {
                $('span.user_body_balance').html(app.instance.controller.$liveResponse.user.body_balance + ' CNY');
                for (var $index = 0; $index < app.instance.controller.$liveResponse.objects.length; $index++) {
                    var $item = app.instance.controller.$liveResponse.objects[$index];
                    $('tr[data-object-id=' + $item.id + ']').each(function () {
                        if (parseInt($(this).attr('data-seconds')) - 1 < 0) {
                            top.location.reload();
                        } else {
                            $(this).attr('data-seconds', parseInt($(this).attr('data-seconds')) - 1);
                            if (parseInt($(this).attr('data-direction')) == 1) {
                                if ($item.body_price > parseFloat($(this).attr('data-price-buying'))) {
                                    $(this).next().find('.price_now').removeClass('green').addClass('red');
                                } else {
                                    $(this).next().find('.price_now').removeClass('red').addClass('green');
                                }
                            } else {
                                if ($item.body_price < parseFloat($(this).attr('data-price-buying'))) {
                                    $(this).next().find('.price_now').removeClass('green').addClass('red');
                                } else {
                                    $(this).next().find('.price_now').removeClass('red').addClass('green');
                                }
                            }
                            $(this).next().find('.price_now').html(parseFloat($item.body_price).toFixed($item.body_price_decimal));
                        }
                    });
                }
            }
        },
        ordersHistoryController: {
            init: function () {
                this.instance.body.on('click', 'tbody td', this.instance.controller.clickedOrder);
            },
            clickedOrder: function () {
                top.location.href = '/orders/detail/' + $(this).parents('tr').attr('data-id');
            }
        }
    },
    run: function () {
        this.instance.body = $('body');
        if (this.controllers[$('body').attr('data-controller')]) {
            this.instance.controller = this.controllers[$('body').attr('data-controller')];
            this.instance.controller.init.call(this);
        }
    }
};

$(document).ready(function(){
    FastClick.attach(document.body);
    window.app.run();
});