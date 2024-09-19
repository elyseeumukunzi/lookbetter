<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class DownloadInvoice extends Controller
{
    public function download(Request $id)
    {
        $invoiceid = $id->query('id');
        $invoice= Invoice::find($invoiceid);

        $client = new Party([
            'name'          => $clientinfo['firstname']. " ".$clientinfo['lastname'],
            'phone'         =>  $clientinfo['phonenumber'],
            'id'            =>  $clientinfo['nid'],
        ]);

    }
}
