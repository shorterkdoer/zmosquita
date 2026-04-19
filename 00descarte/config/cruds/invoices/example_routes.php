<?php
/**
 * Master-Detail Routes Example
 *
 * Add these routes to your config/routes.php file
 */

use App\Controllers\InvoiceController;

// Master-Detail CRUD routes for invoices
Router::get('/invoices', [InvoiceController::class, 'index']);
Router::get('/invoices/create', [InvoiceController::class, 'create']);
Router::post('/invoices/store', [InvoiceController::class, 'store']);
Router::get('/invoices/edit/{id}', [InvoiceController::class, 'edit']);
Router::post('/invoices/update/{id}', [InvoiceController::class, 'update']);
Router::get('/invoices/view/{id}', [InvoiceController::class, 'view']);
Router::post('/invoices/delete/{id}', [InvoiceController::class, 'delete']);
Router::get('/invoices/print/{id}', [InvoiceController::class, 'print']);

// API endpoint for DataTables
Router::get('/api/invoices/data', [InvoiceController::class, 'apiData']);
