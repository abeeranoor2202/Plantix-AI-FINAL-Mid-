@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <x-platform.dashboard-shell
        title="Admin Dashboard"
        subtitle="Users, vendors, experts, disputes, reports, and system activity"
        :summary-cards="$unifiedSummary ?? []"
        :alerts="$dashboardAlerts ?? []"
        :recent-activity="$unifiedRecentActivity ?? []"
        :pending-actions="$unifiedPendingActions ?? []"
    />
</div>

        <!-- ============================================================== -->

        <!-- End Right sidebar -->

        <!-- ============================================================== -->

    </div>

    <!-- ============================================================== -->

    <!-- End Container fluid  -->

    <!-- ============================================================== -->

    <!-- ============================================================== -->

    <!-- footer -->

    <!-- ============================================================== -->


    <!-- ============================================================== -->

    <!-- End footer -->

    <!-- ============================================================== -->

@endsection

@section('scripts')

<script src="{{asset('js/chart.js')}}"></script>

<script>
    // ── Data injected server-side — no API calls needed ───────────────────
    var currentCurrency  = @json($currencySymbol);
    var currencyAtRight  = @json($currencyAtRight);
    var decimal_degits   = @json($decimalDigits);
    var placeholderImage = @json($placeholderImage);

    var dashboardVendors = @json($topVendors);
    var dashboardOrders  = @json($recentOrders);
    var dashboardPayouts = @json($recentPayouts);
    var monthlyData      = @json($monthlyData);

    $(document).ready(function () {

        // ── Top vendors table ─────────────────────────────────────────────
        var append_listvendors = document.getElementById('append_list');
        if (append_listvendors) {
            append_listvendors.innerHTML = buildVendorHTML(dashboardVendors);
        }
        // ── Recent orders table ───────────────────────────────────────────
        var append_listrecent_order = document.getElementById('append_list_recent_order');
        if (append_listrecent_order) {
            append_listrecent_order.innerHTML = buildOrderHTML(dashboardOrders);
        }
        // ── Recent payouts table ──────────────────────────────────────────
        var append_list_recent_payouts = document.getElementById('append_list_recent_payouts');
        if (append_list_recent_payouts) {
            append_list_recent_payouts.innerHTML = buildRecentPayoutsHTML(dashboardPayouts);
        }
        setTimeout(function(){
        }, 1500);

        // ── Charts (use server-side data directly) ────────────────────────────
        var labels = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
        renderChart($('#sales-chart'), monthlyData, labels);
        setCommision();
        setVisitors();

        jQuery("#data-table_processing").hide();

        $(document.body).on('click', '.redirecttopage', function () {
            window.location.href = $(this).attr('data-url');
        });
    });

    function buildVendorHTML(vendors) {
        var html = '';
        vendors.forEach((val) => {
            var route = '<?php echo route("admin.vendors.edit", ":id");?>';
            route = route.replace(':id', val.id);

            var routeview = '<?php echo route("admin.vendors.view", ":id");?>';
            routeview = routeview.replace(':id', val.id);

            html += '<tr>';
            if (!val.photo || val.photo == '') {
                html += '<td class="text-center"><img class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' + placeholderImage + '" alt="image"></td>';
            } else {
                html += '<td class="text-center"><img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' + val.photo + '" alt="image"></td>';
            }

            html += '<td data-url="' + routeview + '" class="redirecttopage">' + val.title + '</td>';

            var rating = 0;
            if (val.reviews_count && val.reviews_count > 0) {
                rating = Math.round(parseFloat(val.reviews_sum) / parseInt(val.reviews_count));
            }

            html += '<td><ul class="rating" data-rating="' + rating + '">';
            for (let i = 0; i < 5; i++) {
                html += '<li class="rating__item"></li>';
            }
            html += '</ul></td>';
            html += '<td><a href="' + route + '" > <span class="fa fa-edit"></span></a></td>';
            html += '</tr>';
        });
        return html;
    }

    async function buildRecentPayoutsHTML(payouts) {
        var html = '';
        
        payouts.forEach((val) => {
            var price = val.amount || 0;
            price = parseFloat(price).toFixed(2);

            if (currencyAtRight) {
                price_val = parseFloat(price).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                price_val = currentCurrency + "" + parseFloat(price).toFixed(decimal_degits);
            }

            html += '<tr class="payout_'+val.id+'">';
            
            var route = '{{route("admin.vendors.view",":id")}}';
            route = route.replace(':id', val.vendor_id);   
            html += '<td data-url="'+route+'" class="redirecttopage">' + (val.vendor ? val.vendor.title : '') + '</td>';
            
            html += '<td class="text-red">(' + price_val + ')</td>';
            var date = new Date(val.paid_date).toDateString();
            var time = new Date(val.paid_date).toLocaleTimeString('en-US');
            html += '<td class="dt-time">' + date + ' ' + time + '</td>';

            if (val.note) {
                html += '<td>' + val.note + '</td>';
            } else {
                html += '<td></td>';
            }
        
            html += '</tr>';
        });

        return html;
    }

    function buildOrderHTML(orders) {
        var html = '';
        orders.forEach((val) => {
            var route = '<?php echo route("admin.orders.show", ":id"); ?>';
            route = route.replace(':id', val.id);

            var vendorroute = '<?php echo route("admin.vendors.view", ":id");?>';
            vendorroute = vendorroute.replace(':id', val.vendor_id);

            html += '<tr>';
            html += '<td data-url="' + route + '" class="redirecttopage">' + val.id + '</td>';

            var quantity = 0;
            if (val.products && Array.isArray(val.products)) {
                val.products.forEach((product) => {
                    quantity += parseInt(product.quantity || 0);
                });
            }

            html += '<td data-url="' + vendorroute + '" class="redirecttopage">' + (val.vendor ? val.vendor.title : '') + '</td>';

            var price = buildHTMLProductstotal(val);

            html += '<td data-url="' + route + '" class="redirecttopage">' + price + '</td>';
            html += '<td data-url="' + route + '" class="redirecttopage"><i class="fa fa-shopping-cart"></i> ' + quantity + '</td>';
            html += '</tr>';
        });
        return html;
    }

    function renderChart(chartNode, data, labels) {
        var ticksStyle = {
            fontColor: '#495057',
            fontStyle: 'bold'
        };

        var mode = 'index';
        var intersect = true;
        return new Chart(chartNode, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        backgroundColor: '#2EC7D9',
                        borderColor: '#2EC7D9',
                        data: data
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    mode: mode,
                    intersect: intersect
                },
                hover: {
                    mode: mode,
                    intersect: intersect
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        gridLines: {
                            display: true,
                            lineWidth: '4px',
                            color: 'rgba(0, 0, 0, .2)',
                            zeroLineColor: 'transparent'
                        },
                        ticks: $.extend({
                            beginAtZero: true,
                            callback: function (value, index, values) {
                                return currentCurrency + value.toFixed(decimal_degits);
                            }


                        }, ticksStyle)
                    }],
                    xAxes: [{
                        display: true,
                        gridLines: {
                            display: false
                        },
                        ticks: ticksStyle
                    }]
                }
            }
        })
    }

    $(document).ready(function () {
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
    });


     function buildHTMLProductstotal(order) {
        var intRegex = /^\d+$/;
        var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

        var adminCommission = order.admin_commission || 0;
        var discount = order.discount || 0;
        var deliveryCharge = order.delivery_charge || 0;
        var tipAmount = order.tip_amount || 0;
        var products = order.products || [];
        var taxAmount = order.tax_amount || 0;

        var totalProductPrice = 0;
        var total_price = 0;

        if (products && Array.isArray(products)) {
            products.forEach((product) => {
                var productPrice = parseFloat(product.price || 0) * parseInt(product.quantity || 0);
                var extrasPrice = parseFloat(product.extras_price || 0) * parseInt(product.quantity || 0);
                totalProductPrice = productPrice + extrasPrice;
                total_price += totalProductPrice;
            });
        }

        if (intRegex.test(discount) || floatRegex.test(discount)) {
            discount = parseFloat(discount).toFixed(decimal_degits);
            total_price -= parseFloat(discount);
        }

        if (!isNaN(taxAmount)) {
            total_price += parseFloat(taxAmount);
        }

        if (intRegex.test(deliveryCharge) || floatRegex.test(deliveryCharge)) {
            deliveryCharge = parseFloat(deliveryCharge).toFixed(decimal_degits);
            total_price += parseFloat(deliveryCharge);
        }

        if (intRegex.test(tipAmount) || floatRegex.test(tipAmount)) {
            tipAmount = parseFloat(tipAmount).toFixed(decimal_degits);
            total_price += parseFloat(tipAmount);
        }

        if (currencyAtRight) {
            var total_price_val = parseFloat(total_price).toFixed(decimal_degits) + "" + currentCurrency;
        } else {
            var total_price_val = currentCurrency + "" + parseFloat(total_price).toFixed(decimal_degits);
        }
        return total_price_val;
    }

    function setVisitors() {
        const data = {
            labels: [
                "{{trans('lang.dashboard_total_stores')}}",
                "{{trans('lang.dashboard_total_orders')}}",
                "{{trans('lang.dashboard_total_products')}}",
                "{{trans('lang.dashboard_total_clients')}}",
            ],
            datasets: [{
                data: [jQuery("#vendor_count").text(), jQuery("#order_count").text(), jQuery("#product_count").text(), jQuery("#users_count").text()],
                backgroundColor: [
                    '#218be1',
                    '#B1DB6F',
                    '#7360ed',
                    '#FFAB2E',
                ],
                hoverOffset: 4
            }]
        };

        return new Chart('visitors', {
            type: 'doughnut',
            data: data,
            options: {
                maintainAspectRatio: false,
            }
        })
    }

    function setCommision() {
        const data = {
            labels: [
                "{{trans('lang.dashboard_total_earnings')}}",
                "{{trans('lang.admin_commission')}}"
            ],
            datasets: [{
                data: [jQuery("#earnings_count").text().replace(currentCurrency, ""), jQuery("#admincommission_count").text().replace(currentCurrency, "")],
                backgroundColor: [
                    '#feb84d',
                    '#9b77f8',
                    '#fe95d3'
                ],
                hoverOffset: 4
            }]
        };
        return new Chart('commissions', {
            type: 'doughnut',
            data: data,
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        label: function (tooltipItems, data) {
                            return data.labels[tooltipItems.index] + ': ' + currentCurrency + data.datasets[0].data[tooltipItems.index];
                        }
                    }
                }
            }
        })
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            cookie = cookie.trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length);
            }
        }
        return '';
    }

</script>
@endsection

