@extends('layouts.app')

@section('content')

<div id="main-wrapper" class="page-wrapper" style="min-height: 207px;">

    <div class="container-fluid">

        <div id="data-table_processing" class="dataTables_processing panel panel-default"
             style="display: none;margin-top:20px;">{{trans('lang.processing')}}
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

</div>

@endsection

@section('scripts')

<script src="{{asset('js/chart.js')}}"></script>

<script>

    jQuery("#data-table_processing").show();

    var db = firebase.firestore();
    var currency = db.collection('settings');

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;
    var refCurrency = database.collection('currencies').where('isActive', '==', true);
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;

        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    $(document).ready(function () {

        db.collection('restaurant_orders').orderBy("createdAt",'desc').get().then(
            (snapshot) => {   
                jQuery("#order_count").empty(); 
                jQuery("#order_count").text(snapshot.docs.length);
            });

        db.collection('vendor_products').get().then(
            (snapshot) => {
                jQuery("#product_count").empty();
                jQuery("#product_count").text(snapshot.docs.length);
            });
 
        db.collection('users').where("role", "==", "customer").orderBy("createdAt",'desc').get().then((snapshot) => {
            jQuery("#users_count").empty();
            jQuery("#users_count").append(snapshot.docs.length);
        });

        db.collection('users').where("role", "==", "driver").orderBy("createdAt",'desc').get().then((snapshot) => {
            jQuery("#driver_count").empty();
            jQuery("#driver_count").append(snapshot.docs.length);
        }); 


        db.collection('vendors').where('title','!=',"").get().then( 
            (snapshot) => {
                jQuery("#vendor_count").empty();
                jQuery("#vendor_count").text(snapshot.docs.length)
                setVisitors();
            });

        
        getTotalEarnings();

        db.collection('restaurant_orders').where('status', 'in', ["Order Placed"]).get().then(
            (snapshot) => {
                jQuery("#placed_count").empty();
                jQuery("#placed_count").text(snapshot.docs.length);
            });

        db.collection('restaurant_orders').where('status', 'in', ["Order Accepted", "Driver Accepted"]).get().then(
            (snapshot) => {
                jQuery("#confirmed_count").empty();
                jQuery("#confirmed_count").text(snapshot.docs.length);
            });

        db.collection('restaurant_orders').where('status', 'in', ["Order Shipped", "In Transit"]).get().then(
            (snapshot) => {
                jQuery("#shipped_count").empty();
                jQuery("#shipped_count").text(snapshot.docs.length);
            });

        db.collection('restaurant_orders').where('status', 'in', ["Order Completed"]).get().then(
            (snapshot) => {
                jQuery("#completed_count").empty();
                jQuery("#completed_count").text(snapshot.docs.length);
            });

        db.collection('restaurant_orders').where('status', 'in', ["Order Rejected"]).get().then(
            (snapshot) => {
                jQuery("#canceled_count").empty();
                jQuery("#canceled_count").text(snapshot.docs.length);
            });

        db.collection('restaurant_orders').where('status', 'in', ["Driver Rejected"]).get().then(
            (snapshot) => {
                jQuery("#failed_count").empty();
                jQuery("#failed_count").text(snapshot.docs.length);
            });

        db.collection('restaurant_orders').where('status', 'in', ["Driver Pending"]).get().then(
            (snapshot) => {
                jQuery("#pending_count").empty();
                jQuery("#pending_count").text(snapshot.docs.length);
            });

        var placeholder = db.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;

        })

        var offest = 1;
        var pagesize = 5;
        var start = null;
        var end = null;
        var endarray = [];
        var inx = parseInt(offest) * parseInt(pagesize);
        var append_listvendors = document.getElementById('append_list');
        append_listvendors.innerHTML = '';

        let ref = db.collection('vendors');
        ref.orderBy('reviewsCount', 'desc').limit(inx).get().then(async (snapshots) => {
            var html = '';
            html = await buildHTML(snapshots);
            if (html != '') {
                append_listvendors.innerHTML = html;
                start = snapshots.docs[snapshots.docs.length - 1];
                endarray.push(snapshots.docs[0]);
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

        });

        var offest = 1;
        var pagesize = 10;
        var start = null;
        var end = null;
        var endarray = [];
        var inx = parseInt(offest) * parseInt(pagesize);
        var append_listrecent_order = document.getElementById('append_list_recent_order');
        append_list.innerHTML = '';

        ref = db.collection('restaurant_orders');
        ref.orderBy('createdAt', 'desc').where('status', 'in', ["Order Placed", "Order Accepted", "Driver Pending", "Driver Accepted", "Order Shipped", "In Transit"]).limit(inx).get().then(async (snapshots) => {
            var html = '';
            html = await buildOrderHTML(snapshots);
            if (html != '') {
                append_listrecent_order.innerHTML = html;
                start = snapshots.docs[snapshots.docs.length - 1];
                endarray.push(snapshots.docs[0]);
            }

            $('#orderTable').DataTable({
                order: [],
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}"
                },
                responsive: true
            });
        });

        var append_list_recent_payouts = document.getElementById('append_list_recent_payouts');
        append_list_recent_payouts.innerHTML = '';
        db.collection('payouts').where('paymentStatus', '==', 'Success').orderBy('paidDate', 'desc').limit(10).get().then(async (snapshots) => {
            var html = '';
            html = await buildRecentPayoutsHTML(snapshots);
            if (html != '') {
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
        });
    });

    async function getTotalEarnings() {
        var intRegex = /^\d+$/;
        var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
        var v01 = 0;
        var v02 = 0;
        var v03 = 0;
        var v04 = 0;
        var v05 = 0;
        var v06 = 0;
        var v07 = 0;
        var v08 = 0;
        var v09 = 0;
        var v10 = 0;
        var v11 = 0;
        var v12 = 0;
        var currentYear = new Date().getFullYear();
        await db.collection('restaurant_orders').where('status', 'in', ["Order Completed"]).get().then(async function (orderSnapshots) {
            var paymentData = orderSnapshots.docs;
            var totalEarning = 0;
            var adminCommission = 0;
            paymentData.forEach((order) => {
                var orderData = order.data();
                var price = 0;
                var minprice = 0;
                orderData.products.forEach((product) => {

                    if (product.price && product.quantity != 0) {
                        var extras_price = 0;
                        if (product.extras_price != undefined && product.extras_price != null) {
                            extras_price = parseFloat(product.extras_price) * parseInt(product.quantity);
                        }
                        if (!isNaN(extras_price)) {
                            var productTotal = (parseFloat(product.price) * parseInt(product.quantity)) + extras_price;
                        } else {
                            var productTotal = (parseFloat(product.price) * parseInt(product.quantity));
                        }
                        if (!isNaN(productTotal)) {
                            price = price + productTotal;
                            minprice = minprice + productTotal;
                        }

                    }
                })

                discount = orderData.discount;
                if ((intRegex.test(discount) || floatRegex.test(discount)) && !isNaN(discount)) {
                    discount = parseFloat(discount).toFixed(decimal_degits);
                    price = price - parseFloat(discount);
                    minprice = minprice - parseFloat(discount);
                }

                tax = 0;
                if (orderData.hasOwnProperty('taxSetting')) {
                    if (orderData.taxSetting.type && orderData.taxSetting.tax) {
                        if (orderData.taxSetting.type == "percent") {
                            tax = (parseFloat(orderData.taxSetting.tax) * minprice) / 100;
                        } else {
                            tax = parseFloat(orderData.taxSetting.tax);
                        }
                    }
                }

                if (!isNaN(tax)) {
                    price = price + tax;
                }

                if (orderData.deliveryCharge != undefined && orderData.deliveryCharge != "" && orderData.deliveryCharge > 0) {
                    price = price + parseFloat(orderData.deliveryCharge);
                }

                if (orderData.adminCommission != undefined && orderData.adminCommissionType != undefined && orderData.adminCommission > 0 && price > 0) {
                    var commission = 0;
                    if (orderData.adminCommissionType == "Percent") {
                        commission = (price * parseFloat(orderData.adminCommission)) / 100;

                    } else {
                        commission = parseFloat(orderData.adminCommission);
                    }

                    adminCommission = commission + adminCommission;
                } else if (orderData.adminCommission != undefined && orderData.adminCommission > 0 && price > 0) {
                    var commission = parseFloat(orderData.adminCommission);
                    adminCommission = commission + adminCommission;
                }

                totalEarning = parseFloat(totalEarning) + parseFloat(price);

                try {

                    if (orderData.createdAt) {
                        var orderMonth = orderData.createdAt.toDate().getMonth() + 1;
                        var orderYear = orderData.createdAt.toDate().getFullYear();
                        if (currentYear == orderYear) {
                            switch (parseInt(orderMonth)) {
                                case 1:
                                    v01 = parseInt(v01) + price;
                                    break;
                                case 2:
                                    v02 = parseInt(v02) + price;
                                    break;
                                case 3:
                                    v03 = parseInt(v03) + price;
                                    break;
                                case 4:
                                    v04 = parseInt(v04) + price;
                                    break;
                                case 5:
                                    v05 = parseInt(v05) + price;
                                    break;
                                case 6:
                                    v06 = parseInt(v06) + price;
                                    break;
                                case 7:
                                    v07 = parseInt(v07) + price;
                                    break;
                                case 8:
                                    v08 = parseInt(v08) + price;
                                    break;
                                case 9:
                                    v09 = parseInt(v09) + price;
                                    break;
                                case 10:
                                    v10 = parseInt(v10) + price;
                                    break;
                                case 11:
                                    v11 = parseInt(v11) + price;
                                    break;
                                default :
                                    v12 = parseInt(v12) + price;
                                    break;
                            }
                        }
                    }

                } catch (err) {


                    var datas = new Date(orderData.createdAt._seconds * 1000);

                    var dates = firebase.firestore.Timestamp.fromDate(datas);

                    db.collection('restaurant_orders').doc(orderData.id).update({'createdAt': dates}).then(() => {

                        console.log('Provided document has been updated in Firestore');

                    }, (error) => {

                        console.log('Error: ' + error);

                    });

                }


            })

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
            var data = [v01, v02, v03, v04, v05, v06, v07, v08, v09, v10, v11, v12];
            var labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            var $salesChart = $('#sales-chart');
            var salesChart = renderChart($salesChart, data, labels);
            setCommision();
        })
        jQuery("#data-table_processing").hide();

    }

    function buildHTML(snapshots) {
        var html = '';
        var count = 1;
        var rating = 0;
        snapshots.docs.forEach((listval) => {
            val = listval.data();
            val.id = listval.id;
            var route = '<?php echo route("admin.vendors.edit", ":id");?>';
            route = route.replace(':id', val.id);

            var routeview = '<?php echo route("admin.vendors.view", ":id");?>';
            routeview = routeview.replace(':id', val.id);

            html = html + '<tr>';
            if (val.photo == '' && val.photo == null) {

                html = html + '<td class="text-center"><img class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' + placeholderImage + '" alt="image"></td>';
            } else {
                html = html + '<td class="text-center"><img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' + val.photo + '" alt="image"></td>';
            }

            html = html + '<td data-url="' + routeview + '" class="redirecttopage">' + val.title + '</td>';

            if (val.hasOwnProperty('reviewsCount') && val.reviewsCount != 0) {
                rating = Math.round(parseFloat(val.reviewsSum) / parseInt(val.reviewsCount));
            } else {
                rating = 0;
            }

            html = html + '<td><ul class="rating" data-rating="' + rating + '">';
            html = html + '<li class="rating__item"></li>';
            html = html + '<li class="rating__item"></li>';
            html = html + '<li class="rating__item"></li>';
            html = html + '<li class="rating__item"></li>';
            html = html + '<li class="rating__item"></li>';
            html = html + '</ul></td>';
            html = html + '<td><a href="' + route + '" > <span class="fa fa-edit"></span></a></td>';
            html = html + '</tr>';

            rating = 0;
            count++;
        });
        return html;
    }


    async function buildRecentPayoutsHTML(snapshots) {
        
        var intRegex = /^\d+$/;
        var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

        var html = '';
        var count = 1;
        
        snapshots.docs.forEach((listval) => {

            val = listval.data();
            val.id = listval.id;
            getStoreName(val.vendorID);
            
            var price = val.amount;
            if (intRegex.test(price) || floatRegex.test(price)) {
                price = parseFloat(price).toFixed(2);
            } else {
                price = 0;
            }

            if (currencyAtRight) {
                price_val = parseFloat(price).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                price_val = currentCurrency + "" + parseFloat(price).toFixed(decimal_degits);
            }

            html = html + '<tr class="payout_'+val.id+'">';
            
            var route = '{{route("admin.vendors.view",":id")}}';
            route = route.replace(':id', val.vendorID);   
            html = html + '<td data-url="'+route+'" class="redirecttopage restname_'+val.vendorID+'" ></td>';
            
            html = html + '<td class="text-red">(' + price_val + ')</td>';
            var date = val.paidDate.toDate().toDateString();
            var time = val.paidDate.toDate().toLocaleTimeString('en-US');
            html = html + '<td class="dt-time">' + date + ' ' + time + '</td>';

            if (val.note != undefined && val.note != '') {
                html = html + '<td>' + val.note + '</td>';
            } else {
                html = html + '<td></td>';
            }
        
            html = html + '</tr>';
        });

        return html;
    }

    function getStoreName(vendorId) {
        database.collection('vendors').doc(vendorId).get().then(async function (snapshots) {
            if(snapshots.exists){
                var data = snapshots.data();
                $(".restname_"+vendorId).text(data.title);
            }
        });
    }
    
    function buildOrderHTML(snapshots) {
        var html = '';
        var count = 1;
        snapshots.docs.forEach((listval) => {
            val = listval.data();
            val.id = listval.id;
            var route = '<?php echo route("admin.orders.show", ":id"); ?>';
            route = route.replace(':id', val.id);

            var vendorroute = '<?php echo route("admin.vendors.view", ":id");?>';
            vendorroute = vendorroute.replace(':id', val.vendorID);

            html = html + '<tr>';

            html = html + '<td data-url="' + route + '" class="redirecttopage">' + val.id + '</td>';

            var price = 0; 
            
                var quan = 0;
            val.products.forEach((product)=> {



                if(product.quantity != 0){
                    

                    quan = quan + product.quantity;
            
                }
            })

            html = html + '<td data-url="' + vendorroute + '" class="redirecttopage">' + val.vendor.title + '</td>';

            var price =  buildHTMLProductstotal(val);

            html = html + '<td data-url="' + route + '" class="redirecttopage">' + price + '</td>';
            html = html + '<td data-url="' + route + '" class="redirecttopage"><i class="fa fa-shopping-cart"></i> ' + quan + '</td>';
            html = html + '</a></tr>';
            count++;
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


     function buildHTMLProductstotal(snapshotsProducts) {

        var adminCommission = snapshotsProducts.adminCommission;
        var discount = snapshotsProducts.discount;
        var couponCode = snapshotsProducts.couponCode;
        var extras = snapshotsProducts.extras;
        var extras_price = snapshotsProducts.extras_price;
        var rejectedByDrivers = snapshotsProducts.rejectedByDrivers;
        var takeAway = snapshotsProducts.takeAway;
        var tip_amount = snapshotsProducts.tip_amount;
        var status = snapshotsProducts.status;
        var products = snapshotsProducts.products;
        var deliveryCharge = snapshotsProducts.deliveryCharge;
        var totalProductPrice = 0;
        var total_price = 0;
        var specialDiscount = snapshotsProducts.specialDiscount;

        var intRegex = /^\d+$/;
        var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

        if (products) {

            products.forEach((product) => {

                var val = product;
                if (val.price) {
                    price_item = parseFloat(val.price).toFixed(2);

                    extras_price_item = 0;
                    if (val.extras_price && !isNaN(extras_price_item) && !isNaN(val.quantity)) {
                        extras_price_item = (parseFloat(val.extras_price) * parseInt(val.quantity)).toFixed(2);
                    }
                    if (!isNaN(price_item) && !isNaN(val.quantity)) {
                        totalProductPrice = parseFloat(price_item) * parseInt(val.quantity);
                    }
                    var extras_price = 0;
                    if (parseFloat(extras_price_item) != NaN && val.extras_price != undefined) {
                        extras_price = extras_price_item;
                    }
                    totalProductPrice = parseFloat(extras_price) + parseFloat(totalProductPrice);
                    totalProductPrice = parseFloat(totalProductPrice).toFixed(2);
                    if (!isNaN(totalProductPrice)) {
                        total_price += parseFloat(totalProductPrice);
                    }


                }

            });
        }

        if (intRegex.test(discount) || floatRegex.test(discount)) {

            discount = parseFloat(discount).toFixed(decimal_degits);
            total_price -= parseFloat(discount);

            if (currencyAtRight) {
                discount_val = discount + "" + currentCurrency;
            } else {
                discount_val = currentCurrency + "" + discount;
            }

        }
        var special_discount = 0;
        if (specialDiscount != undefined) {
            special_discount = parseFloat(specialDiscount.special_discount).toFixed(2);

            total_price = total_price - special_discount;
        }
        var total_item_price = total_price;
        var tax = 0;
        taxlabel = '';
        taxlabeltype = '';

        if (snapshotsProducts.hasOwnProperty('taxSetting')) {
            var total_tax_amount = 0;
            for (var i = 0; i < snapshotsProducts.taxSetting.length; i++) {
                var data = snapshotsProducts.taxSetting[i];

                if (data.type && data.tax) {
                    if (data.type == "percentage") {
                        tax = (data.tax * total_price) / 100;
                        taxlabeltype = "%";
                    } else {
                        tax = data.tax;
                        taxlabeltype = "fix";
                    }
                    taxlabel = data.title;
                }
                total_tax_amount += parseFloat(tax);
            }
            total_price = parseFloat(total_price) + parseFloat(total_tax_amount);
        }


        if ((intRegex.test(deliveryCharge) || floatRegex.test(deliveryCharge)) && !isNaN(deliveryCharge)) {

            deliveryCharge = parseFloat(deliveryCharge).toFixed(decimal_degits);
            total_price += parseFloat(deliveryCharge);

            if (currencyAtRight) {
                deliveryCharge_val = deliveryCharge + "" + currentCurrency;
            } else {
                deliveryCharge_val = currentCurrency + "" + deliveryCharge;
            }  
        }

        if (intRegex.test(tip_amount) || floatRegex.test(tip_amount) && !isNaN(tip_amount)) {

            tip_amount = parseFloat(tip_amount).toFixed(decimal_degits);
            total_price += parseFloat(tip_amount);
            total_price = parseFloat(total_price).toFixed(decimal_degits);
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
                "{{trans('lang.dashboard_total_drivers')}}",
            ],
            datasets: [{
                data: [jQuery("#vendor_count").text(), jQuery("#order_count").text(), jQuery("#product_count").text(), jQuery("#users_count").text(), jQuery("#driver_count").text()],
                backgroundColor: [
                    '#218be1',
                    '#B1DB6F',
                    '#7360ed',
                    '#FFAB2E',
                    '#FF683A',
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

</script>
@endsection

