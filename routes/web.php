<?php

use App\Http\Controllers\DownloadBill;
use App\Http\Controllers\DownloadInvoice;
use App\Http\Controllers\DownloadPrescription;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/prescriptions/pdf/download',[DownloadPrescription::class, 'download'])->name('prescriptions.pdf.download');
Route::get('/bill/pdf/download',[DownloadBill::class, 'download'])->name('bill.pdf.download');
//Route::get('/invoice/pdf/download',[DownloadInvoice::class, 'download'])->name('invoice.pdf.download');



