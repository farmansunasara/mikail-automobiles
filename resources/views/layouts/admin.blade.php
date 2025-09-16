<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | @yield('title')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    
    <!-- Custom Loader Styles -->
    <style>
        /* Page Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }
        
        .page-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        /* Spinner Animation */
        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Button Loader */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* Table Loader */
        .table-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }
        
        /* Form Loader */
        .form-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }
        
        /* Chart Loader */
        .chart-loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }
        
        /* Overlay for content loading */
        .content-loading {
            position: relative;
        }
        
        .content-loading::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 5;
        }
        
        .content-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            margin: -15px 0 0 -15px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 6;
        }
        
        /* Global Pagination Styling Fix */
        .pagination {
            margin: 0;
            display: flex !important;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .pagination .page-item {
            margin: 0 2px;
        }
        
        .pagination .page-link {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 40px;
            min-height: 40px;
            padding: 0.5rem 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            color: #495057;
            text-decoration: none;
            background-color: #fff;
            transition: all 0.15s ease-in-out;
            line-height: 1.5;
            font-weight: 400;
            position: relative;
        }
        
        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #0056b3;
            text-decoration: none;
            z-index: 2;
        }
        
        .pagination .page-link:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            text-decoration: none;
            z-index: 3;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
            z-index: 1;
        }
        
        .pagination .page-item.active .page-link:hover {
            background-color: #0056b3;
            border-color: #004085;
            color: #fff;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
            opacity: 0.65;
            pointer-events: none;
        }
        
        /* Arrow styling */
        .pagination .page-link i {
            font-size: 14px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Fix for Laravel pagination arrows */
        .pagination .page-link[rel="prev"],
        .pagination .page-link[rel="next"] {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
        }
        
        .pagination .page-link[rel="prev"] i,
        .pagination .page-link[rel="next"] i {
            margin: 0;
        }
        
        /* Screen reader text */
        .pagination .sr-only {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }
        
        /* Responsive pagination */
        @media (max-width: 576px) {
            .pagination {
                font-size: 0.875rem;
            }
            
            .pagination .page-link {
                min-width: 36px;
                min-height: 36px;
                padding: 0.375rem 0.5rem;
            }
            
            .pagination .page-link i {
                font-size: 12px;
            }
            
            .pagination .page-item {
                margin: 0 1px;
            }
        }
        
        /* Ensure pagination container is centered */
        .d-flex.justify-content-center .pagination {
            margin: 0 auto;
        }
        
        /* Additional AdminLTE compatibility */
        .pagination .page-item:first-child .page-link {
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
        }
        
        .pagination .page-item:last-child .page-link {
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }
        
        /* Sortable Header Styles */
        .sortable-header a:hover {
            background-color: #f8f9fa;
        }
        .sortable-header a:hover i {
            color: #007bff !important;
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
    <!-- Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="text-center">
            <div class="loader-spinner"></div>
            <div class="mt-2">
                <small class="text-muted">Loading...</small>
            </div>
        </div>
    </div>

<div class="wrapper">

    <!-- Navbar -->
    @include('layouts.partials.navbar')
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    @include('layouts.partials.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('title')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumbs')
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <strong>Copyright &copy; {{ date('Y') }} <a href="#">{{ config('app.name') }}</a>.</strong> All rights reserved.
            </div>
            <div class="text-muted text-sm">
                Developed by <strong><a class="text-muted" href="{{ config('app.developer_url') }}" target="_blank" rel="noopener">{{ config('app.developer') }}</a></strong>
            </div>
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>

<!-- Custom Loader JavaScript -->
<script>
$(document).ready(function() {
    // Hide page loader when document is ready
    setTimeout(function() {
        $('#page-loader').addClass('hidden');
    }, 500);
    
    // Global AJAX setup for loaders
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            // Show loading state for AJAX requests
            if (settings.showLoader !== false) {
                showAjaxLoader();
            }
        },
        complete: function(xhr, status) {
            // Hide loading state after AJAX completes
            hideAjaxLoader();
        },
        error: function(xhr, status, error) {
            hideAjaxLoader();
            // Show error message
            showErrorMessage('An error occurred. Please try again.');
        }
    });
    
    // Form submission loaders
    $('form').on('submit', function(e) {
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"], input[type="submit"]');
        
        // Don't show loader for search forms (they reload the page)
        if (!$form.hasClass('no-loader')) {
            $submitBtn.addClass('btn-loading');
            $submitBtn.prop('disabled', true);
            $form.addClass('form-loading');
        }
    });
    
    // Link click loaders for navigation
    $('a:not(.no-loader)').on('click', function(e) {
        var href = $(this).attr('href');
        
        // Only show loader for internal links
        if (href && href.indexOf('http') !== 0 && href !== '#' && !href.startsWith('javascript:')) {
            showPageLoader();
        }
    });
    
    // PDF download loader
    $('a[href*="pdf"], a[href*="download"]').on('click', function(e) {
        var $btn = $(this);
        $btn.addClass('btn-loading');
        
        // Remove loading state after a delay (since it's a download)
        setTimeout(function() {
            $btn.removeClass('btn-loading');
        }, 3000);
    });
});

// Loader utility functions
function showPageLoader() {
    $('#page-loader').removeClass('hidden');
}

function hidePageLoader() {
    $('#page-loader').addClass('hidden');
}

function showAjaxLoader() {
    // You can customize this based on the context
    $('body').addClass('ajax-loading');
}

function hideAjaxLoader() {
    $('body').removeClass('ajax-loading');
}

function showTableLoader($table) {
    var $tableContainer = $table.closest('.table-responsive, .card-body');
    if ($tableContainer.find('.table-loader').length === 0) {
        $tableContainer.css('position', 'relative');
        $tableContainer.append('<div class="table-loader"><div class="loader-spinner"></div></div>');
    }
}

function hideTableLoader($table) {
    var $tableContainer = $table.closest('.table-responsive, .card-body');
    $tableContainer.find('.table-loader').remove();
}

function showContentLoader($element) {
    $element.addClass('content-loading');
}

function hideContentLoader($element) {
    $element.removeClass('content-loading');
}

function showErrorMessage(message) {
    // Create a toast-like error message
    var errorHtml = '<div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 10000; min-width: 300px;" role="alert">' +
        message +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '</div>';
    
    $('body').append(errorHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert.position-fixed').fadeOut();
    }, 5000);
}

// Chart loader utility
function showChartLoader($chartContainer) {
    if ($chartContainer.find('.chart-loader').length === 0) {
        $chartContainer.css('position', 'relative');
        $chartContainer.append('<div class="chart-loader"><div class="loader-spinner"></div></div>');
    }
}

function hideChartLoader($chartContainer) {
    $chartContainer.find('.chart-loader').remove();
}

// Enhanced Alert and Confirm System for Order Management
function showOrderAlert(type, title, message, confirmCallback = null) {
    const icons = {
        'success': 'success',
        'error': 'error', 
        'warning': 'warning',
        'info': 'info',
        'confirm': 'question'
    };
    
    const colors = {
        'success': '#28a745',
        'error': '#dc3545',
        'warning': '#ffc107',
        'info': '#17a2b8',
        'confirm': '#007bff'
    };

    if (typeof Swal !== 'undefined') {
        if (type === 'confirm') {
            Swal.fire({
                title: title,
                text: message,
                icon: icons[type],
                showCancelButton: true,
                confirmButtonColor: colors[type],
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'No, cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed && confirmCallback) {
                    confirmCallback();
                }
            });
        } else {
            Swal.fire({
                title: title,
                text: message,
                icon: icons[type],
                confirmButtonColor: colors[type],
                timer: type === 'success' ? 3000 : null,
                timerProgressBar: type === 'success'
            });
        }
    } else {
        // Fallback to native alerts if SweetAlert2 is not loaded
        if (type === 'confirm') {
            if (confirm(title + '\n\n' + message) && confirmCallback) {
                confirmCallback();
            }
        } else {
            alert(title + '\n\n' + message);
        }
    }
}

// Specific order management alerts
function showOrderSuccess(message) {
    showOrderAlert('success', 'Success!', message);
}

function showOrderError(message) {
    showOrderAlert('error', 'Error!', message);
}

function showOrderWarning(message) {
    showOrderAlert('warning', 'Warning!', message);
}

function showOrderInfo(message) {
    showOrderAlert('info', 'Information', message);
}

function confirmOrderAction(title, message, callback) {
    showOrderAlert('confirm', title, message, callback);
}

// Specific confirmations for order operations
function confirmOrderCancellation(orderNumber, callback) {
    const message = `Are you sure you want to cancel order #${orderNumber}?\n\nThis action cannot be undone and will:\n• Cancel the order permanently\n• Cancel all related manufacturing requirements\n• Release any reserved stock`;
    confirmOrderAction('Cancel Order', message, callback);
}

function confirmOrderDeletion(orderNumber, callback) {
    const message = `Are you sure you want to delete order #${orderNumber}?\n\nThis action cannot be undone and will permanently remove all order data.`;
    confirmOrderAction('Delete Order', message, callback);
}

function confirmInvoiceGeneration(orderNumber, callback) {
    const message = `Generate invoice for order #${orderNumber}?\n\nThis will:\n• Create an official invoice\n• Deduct stock quantities\n• Mark the order as completed\n• Cannot be reversed`;
    confirmOrderAction('Generate Invoice', message, callback);
}

function showStockWarning(stockWarnings, callback) {
    let message = 'Stock shortage detected for the following items:\n\n';
    stockWarnings.forEach(warning => {
        message += `• ${warning}\n`;
    });
    message += '\nManufacturing requirements will be generated for these items.\n\nDo you want to continue?';
    
    confirmOrderAction('Stock Shortage Warning', message, callback);
}

function showValidationErrors(errors) {
    let message = 'Please fix the following errors:\n\n';
    errors.forEach(error => {
        message += `• ${error}\n`;
    });
    showOrderError(message);
}
</script>

@stack('scripts')
</body>
</html>
