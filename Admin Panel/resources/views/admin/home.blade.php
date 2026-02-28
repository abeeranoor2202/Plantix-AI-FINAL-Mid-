@extends('layouts.app')

@section('content')

<div class="container-fluid">

        <div id="data-table_processing" class="card-agri text-center mb-4"
             style="display: none; padding: 20px; color: var(--agri-primary); font-weight: 600; width: 100%;">
             <i class="fa fa-spinner fa-spin mr-2"></i> {{trans('lang.processing')}}
        </div>

        <!-- Business Analytics -->
        <div class="card-agri mb-4" style="border: none; background: transparent; box-shadow: none; padding: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                <h2 style="font-size: 24px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                    {{trans('lang.dashboard_business_analytics')}}
                </h2>
                <div style="background: var(--agri-white); padding: 8px 16px; border-radius: 12px; border: 1px solid var(--agri-border); font-size: 14px; font-weight: 500; color: var(--agri-text-muted);">
                    Real-time Data
                </div>
            </div>

            <div class="row g-4">
                {{-- Total Earnings --}}
                <div class="col-sm-6 col-lg-3">
                    <div class="card-agri" style="cursor: pointer;" onclick="location.href='{!! route('admin.orders.index') !!}'">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div style="background: var(--agri-primary-light); padding: 10px; border-radius: 12px;">
                                <i class="mdi mdi-cash-usd" style="color: var(--agri-primary); font-size: 24px;"></i>
                            </div>
                        </div>
                        <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">{{trans('lang.dashboard_total_earnings')}}</h5>
                        <h2 id="earnings_count" style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">--</h2>
                    </div>
                </div>

                {{-- Total Stores --}}
                <div class="col-sm-6 col-lg-3">
                    <div class="card-agri" style="cursor: pointer;" onclick="location.href='{!! route('admin.vendors') !!}'">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div style="background: #FFFBEB; padding: 10px; border-radius: 12px;">
                                <i class="mdi mdi-shopping" style="color: var(--agri-secondary); font-size: 24px;"></i>
                            </div>
                        </div>
                        <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">{{trans('lang.dashboard_total_stores')}}</h5>
                        <h2 id="vendor_count" style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">--</h2>
                    </div>
                </div>

                {{-- Total Orders --}}
                <div class="col-sm-6 col-lg-3">
                    <div class="card-agri" style="cursor: pointer;" onclick="location.href='{!! route('admin.orders.index') !!}'">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div style="background: #EFF6FF; padding: 10px; border-radius: 12px;">
                                <i class="mdi mdi-cart" style="color: var(--agri-info); font-size: 24px;"></i>
                            </div>
                        </div>
                        <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">{{trans('lang.dashboard_total_orders')}}</h5>
                        <h2 id="order_count" style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">--</h2>
                    </div>
                </div>

                {{-- Admin Commission --}}
                <div class="col-sm-6 col-lg-3">
                    <div class="card-agri" style="cursor: pointer;" onclick="location.href='{!! route('admin.orders.index') !!}'">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div style="background: #F0FDF4; padding: 10px; border-radius: 12px;">
                                <i class="ti-wallet" style="color: var(--agri-primary-hover); font-size: 24px;"></i>
                            </div>
                        </div>
                        <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">{{trans('lang.admin_commission')}}</h5>
                        <h2 id="admincommission_count" style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">--</h2>
                    </div>
                </div>
            </div>

            <!-- Order Status Grid -->
            <div class="row g-3 mt-4">
                <div class="col-md-12">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                        {{-- Individual Status Items --}}
                        <a href="{{ route('admin.orders.index','status=order-placed') }}" class="card-agri" style="text-decoration: none; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h6 style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 4px;">Placed</h6>
                                <h4 id="placed_count" style="margin: 0; font-weight: 700; color: var(--agri-text-heading);">0</h4>
                            </div>
                            <div style="background: var(--agri-bg); padding: 8px; border-radius: 10px;">
                                <i class="mdi mdi-lan-pending" style="color: var(--agri-primary);"></i>
                            </div>
                        </a>

                        <a href="{{ route('admin.orders.index','status=order-confirmed') }}" class="card-agri" style="text-decoration: none; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h6 style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 4px;">Confirmed</h6>
                                <h4 id="confirmed_count" style="margin: 0; font-weight: 700; color: var(--agri-text-heading);">0</h4>
                            </div>
                            <div style="background: var(--agri-bg); padding: 8px; border-radius: 10px;">
                                <i class="mdi mdi-check-circle" style="color: #059669;"></i>
                            </div>
                        </a>

                        <a href="{{ route('admin.orders.index','status=order-shipped') }}" class="card-agri" style="text-decoration: none; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h6 style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 4px;">Shipped</h6>
                                <h4 id="shipped_count" style="margin: 0; font-weight: 700; color: var(--agri-text-heading);">0</h4>
                            </div>
                            <div style="background: var(--agri-bg); padding: 8px; border-radius: 10px;">
                                <i class="mdi mdi-truck-delivery" style="color: var(--agri-info);"></i>
                            </div>
                        </a>

                        <a href="{{ route('admin.orders.index','status=order-completed') }}" class="card-agri" style="text-decoration: none; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h6 style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 4px;">Completed</h6>
                                <h4 id="completed_count" style="margin: 0; font-weight: 700; color: var(--agri-text-heading);">0</h4>
                            </div>
                            <div style="background: var(--agri-primary-light); padding: 8px; border-radius: 10px;">
                                <i class="mdi mdi-check-underline" style="color: var(--agri-primary);"></i>
                            </div>
                        </a>

                        <a href="{{ route('admin.orders.index','status=order-canceled') }}" class="card-agri" style="text-decoration: none; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h6 style="font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 4px;">Canceled</h6>
                                <h4 id="canceled_count" style="margin: 0; font-weight: 700; color: var(--agri-text-heading);">0</h4>
                            </div>
                            <div style="background: #FEF2F2; padding: 8px; border-radius: 10px;">
                                <i class="mdi mdi-close-circle" style="color: var(--agri-error);"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-4">
            <div class="col-lg-4 mb-4">
                <div class="card-agri" style="height: 100%;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{trans('lang.total_sales')}}</h3>
                    </div>
                    <div style="position: relative; height: 250px;">
                        <canvas id="sales-chart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card-agri" style="height: 100%;">
                    <div style="margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{trans('lang.service_overview')}}</h3>
                    </div>
                    <div style="height: 250px;">
                        <canvas id="visitors"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card-agri" style="height: 100%;">
                    <div style="margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{trans('lang.sales_overview')}}</h3>
                    </div>
                    <div style="height: 250px;">
                        <canvas id="commissions"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-lg-6 mb-4">
                <div class="card-agri" style="padding: 0; overflow: hidden;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Top {{trans('lang.store_plural')}}</h3>
                        <a href="{{route('admin.vendors')}}" style="color: var(--agri-primary); font-size: 14px; font-weight: 600; text-decoration: none;">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="storeTable" style="margin: 0;">
                            <thead style="background: var(--agri-bg);">
                                <tr>
                                    <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">IMAGE</th>
                                    <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">{{trans('lang.store')}}</th>
                                    <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">RATING</th>
                                    <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="append_list"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card-agri" style="padding: 0; overflow: hidden;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{trans('lang.recent_orders')}}</h3>
                        <a href="{{route('admin.orders.index')}}" style="color: var(--agri-primary); font-size: 14px; font-weight: 600; text-decoration: none;">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="orderTable" style="margin: 0;">
                            <thead style="background: var(--agri-bg);">
                                <tr>
                                    <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">ID</th>
                                    <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">{{trans('lang.store')}}</th>
                                    <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">AMOUNT</th>
                                    <th style="font-size: 12px; color: var(--agri-text-muted); padding: 12px 24px; border: none;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody id="append_list_recent_order"></tbody>
                        </table>
                    </div>
                </div>
            </div>
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

    jQuery("#data-table_processing").show();

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;
    var placeholderImage = '';

    // Fetch currency settings from API
    $.ajax({
        url: '{{ route("api.admin.settings.currency") }}',
        method: 'GET',
        success: function(response) {
            if (response && response.data) {
                currentCurrency = response.data.symbol;
                currencyAtRight = response.data.symbolAtRight;
                decimal_degits = response.data.decimal_degits || 0;
            }
        }
    });

    $(document).ready(function () {

        // Fetch dashboard statistics
        $.ajax({
            url: '{{ route("api.admin.dashboard.stats") }}',
            method: 'GET',
            success: function(response) {
                if (response && response.data) {
                    jQuery("#order_count").text(response.data.total_orders || 0);
                    jQuery("#product_count").text(response.data.total_products || 0);
                    jQuery("#users_count").text(response.data.total_customers || 0);
                    jQuery("#vendor_count").text(response.data.total_vendors || 0);
                    
                    jQuery("#placed_count").text(response.data.orders_placed || 0);
                    jQuery("#confirmed_count").text(response.data.orders_confirmed || 0);
                    jQuery("#shipped_count").text(response.data.orders_shipped || 0);
                    jQuery("#completed_count").text(response.data.orders_completed || 0);
                    jQuery("#canceled_count").text(response.data.orders_canceled || 0);
                    jQuery("#failed_count").text(response.data.orders_failed || 0);
                    jQuery("#pending_count").text(response.data.orders_pending || 0);
                }
            }
        });

        // Fetch placeholder image
        $.ajax({
            url: '{{ route("api.admin.settings.placeholder") }}',
            method: 'GET',
            success: function(response) {
                if (response && response.data) {
                    placeholderImage = response.data.image;
                }
            }
        });

        getTotalEarnings();

        // Fetch top vendors
        $.ajax({
            url: '{{ route("api.admin.vendors.top") }}?limit=5',
            method: 'GET',
            success: function(response) {
                var append_listvendors = document.getElementById('append_list');
                append_listvendors.innerHTML = '';
                
                if (response && response.data && response.data.length > 0) {
                    var html = buildVendorHTML(response.data);
                    append_listvendors.innerHTML = html;
                }

                $('#storeTable').DataTable({
                    order: [],
                    columnDefs: [
                        {orderable: false, targets: [0, 2, 3]},
                    ],
                    "language": {
                        "zeroRecords": "{{trans("lang.no_record_found")}}",
                        "emptyTable": "{{trans("lang.no_record_found")}}"
                    },
                    responsive: true
                });
            }
        });

        // Fetch recent orders
        $.ajax({
            url: '{{ route("api.admin.orders.recent") }}?limit=10',
            method: 'GET',
            success: function(response) {
                var append_listrecent_order = document.getElementById('append_list_recent_order');
                append_listrecent_order.innerHTML = '';
                
                if (response && response.data && response.data.length > 0) {
                    var html = buildOrderHTML(response.data);
                    append_listrecent_order.innerHTML = html;
                }

                $('#orderTable').DataTable({
                    order: [],
                    "language": {
                        "zeroRecords": "{{trans("lang.no_record_found")}}",
                        "emptyTable": "{{trans("lang.no_record_found")}}"
                    },
                    responsive: true
                });
            }
        });

        // Fetch recent payouts
        $.ajax({
            url: '{{ route("api.admin.payouts.recent") }}?limit=10',
            method: 'GET',
            success: function(response) {
                var append_list_recent_payouts = document.getElementById('append_list_recent_payouts');
                append_list_recent_payouts.innerHTML = '';
                
                if (response && response.data && response.data.length > 0) {
                    var html = buildRecentPayoutsHTML(response.data);
                    append_list_recent_payouts.innerHTML = html;
                }

                setTimeout(function(){
                    $('#recentPayoutsTable').DataTable({
                        columnDefs: [
                            {
                                targets: 2,
                                type: 'date',
                                render: function (data) {
                                    return data;
                                } 
                            },
                            {
                                targets: 1,
                                type: 'num-fmt',
                                render: function (data, type, row, meta) {
                                    if (type === 'display') {
                                        return data;
                                    }
                                    return parseFloat(data.replace(/[^0-9.-]+/g, ""));
                                }
                            },
                        ],
                        order: [['2', 'desc']],
                        "language": {
                            "zeroRecords": "{{trans("lang.no_record_found")}}",
                            "emptyTable": "{{trans("lang.no_record_found")}}"
                        },
                        responsive: true
                    });
                },1500);
            }
        });
    });

    async function getTotalEarnings() {
        $.ajax({
            url: '{{ route("api.admin.dashboard.earnings") }}',
            method: 'GET',
            success: function(response) {
                if (response && response.data) {
                    var totalEarning = response.data.total_earnings || 0;
                    var adminCommission = response.data.admin_commission || 0;
                    var monthlyData = response.data.monthly_data || [0,0,0,0,0,0,0,0,0,0,0,0];

                    if (currencyAtRight) {
                        totalEarning = parseFloat(totalEarning).toFixed(decimal_degits) + "" + currentCurrency;
                        adminCommission = parseFloat(adminCommission).toFixed(decimal_degits) + "" + currentCurrency;
                    } else {
                        totalEarning = currentCurrency + "" + parseFloat(totalEarning).toFixed(decimal_degits);
                        adminCommission = currentCurrency + "" + parseFloat(adminCommission).toFixed(decimal_degits);
                    }

                    $("#earnings_count").append(totalEarning);
                    $("#earnings_count_graph").append(totalEarning);
                    $("#admincommission_count_graph").append(adminCommission);
                    $("#admincommission_count").append(adminCommission);
                    $("#total_earnings_header").text(totalEarning);
                    $(".earnings_over_time").append(totalEarning);
                    
                    var labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                    var $salesChart = $('#sales-chart');
                    var salesChart = renderChart($salesChart, monthlyData, labels);
                    setCommision();
                }
                jQuery("#data-table_processing").hide();
            }
        });
    }

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

