<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Consultation;
use App\Models\Insurance;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;

class DownloadPrescription extends Controller
{
    public function download(Request $id)
    {
        //lets call to all the data we'll need to display on the spectacle presciption 
        
        $consultationid = $id->query('id');
        $consultationinfo= Consultation::find($consultationid)->toArray();
        $insuranceinfo=Insurance::find($consultationinfo['insurance_id']);
        $clientinfo=Client::find($consultationinfo['client_id'])->toArray();
        $prescriptionstatus=$consultationinfo['prescription_status'];
        if($prescriptionstatus == 1)
        {          
            $prescriptioninfo=Prescription::where('consultation_id', $consultationid)->first();

            if(!empty($prescriptioninfo))
            {
                $prescriptioninfo->toArray();
            }

        }
        $doctorinfo=[];        
        $doctorinfo=User::find($consultationinfo['doctor_id']);
        if(!empty($doctorinfo))
        {
            $doctorinfo->toArray();
        }
        else{
        //
        }
        
        $client = new Party([
            'name'          => $clientinfo['firstname']. " ".$clientinfo['lastname'],
            'phone'         =>  $clientinfo['phonenumber'],
            'id'            =>  $clientinfo['nid'],
            'dates'            =>  $consultationinfo['dates'],
            'RDVsphere'=>$consultationinfo['RDVsphere'],
            'RDVcylinder' => $consultationinfo['RDVcylinder'],
            'RDVaxis' => $consultationinfo['RDVaxis'],
            'LDVsphere' =>$consultationinfo['LDVsphere'],
            'LDVcylinder' => $consultationinfo['LDVcylinder'],
            'LDVaxis' =>$consultationinfo['LDVaxis'],
            'RNV' => $consultationinfo['RNV'],
            'LNV' =>$consultationinfo['LNV'],
            'distant_comment' => $consultationinfo['distant_comment'],
            'near_comment' => $consultationinfo['near_comment'],
            'ingredient' => $prescriptioninfo['ingredient'] ?? 'No lens selected',
            'nature' => $prescriptioninfo['nature'] ?? '',
            'reaction' => $prescriptioninfo['reaction'] ?? '',
            'light' => $prescriptioninfo['light_reaction'] ?? '',
            'doctor_names' => $consultationinfo['doctor_name'] ?? '',
            'insurance' => $insuranceinfo['name'] ?? '',




        ]);
        
        $customer = new Party([
            'name'          => 'Ashley Medina',
            'address'       => 'The Green Street 12',
            'code'          => '#22663214',
            'custom_fields' => [
                'order number' => '> 654321 <',
            ],
        ]);
        
        $items = [
            InvoiceItem::make('Service 1')
                ->description('Your product or service description')
                ->pricePerUnit(47.79)
                ->quantity(2)
                ->discount(10),
            InvoiceItem::make('Service 2')->pricePerUnit(71.96)->quantity(2),
            InvoiceItem::make('Service 3')->pricePerUnit(4.56),
            InvoiceItem::make('Service 4')->pricePerUnit(87.51)->quantity(7)->discount(4)->units('kg'),
            InvoiceItem::make('Service 5')->pricePerUnit(71.09)->quantity(7)->discountByPercent(9),
            InvoiceItem::make('Service 6')->pricePerUnit(76.32)->quantity(9),
            InvoiceItem::make('Service 7')->pricePerUnit(58.18)->quantity(3)->discount(3),
            InvoiceItem::make('Service 8')->pricePerUnit(42.99)->quantity(4)->discountByPercent(3),
            InvoiceItem::make('Service 9')->pricePerUnit(33.24)->quantity(6)->units('m2'),
            InvoiceItem::make('Service 11')->pricePerUnit(97.45)->quantity(2),
            InvoiceItem::make('Service 12')->pricePerUnit(92.82),
            InvoiceItem::make('Service 13')->pricePerUnit(12.98),
            InvoiceItem::make('Service 14')->pricePerUnit(160)->units('hours'),
            InvoiceItem::make('Service 15')->pricePerUnit(62.21)->discountByPercent(5),
            InvoiceItem::make('Service 16')->pricePerUnit(2.80),
            InvoiceItem::make('Service 17')->pricePerUnit(56.21),
            InvoiceItem::make('Service 18')->pricePerUnit(66.81)->discountByPercent(8),
            InvoiceItem::make('Service 19')->pricePerUnit(76.37),
            InvoiceItem::make('Service 20')->pricePerUnit(55.80),
        ];
        
        $notes = [
            'your multiline',
            'additional notes',
            'in regards of delivery or something else',
        ];
        $notes = implode("<br>", $notes);
        
        $invoice = Invoice::make('SPECTACLES PRESCRIPTION')
            ->series('BIG')            
            ->template('prescription')
            // ability to include translated invoice status
            // in case it was paid
            
            ->sequence(667)            
            ->seller($client)
            ->buyer($customer)
            ->date(now()->subWeeks(3))
            ->dateFormat('m/d/Y')
            ->payUntilDays(14)
            ->currencySymbol('$')
            ->currencyCode('USD')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyThousandsSeparator('.')
            ->currencyDecimalPoint(',')
            ->filename($client->name . ' ' . $customer->name)
            ->addItems($items)
            ->notes($notes)
            ->logo(public_path('vendor/invoices/logo.jpeg'))
            // You can additionally save generated invoice to configured disk
            ->save('public');
        
        $link = $invoice->url();
        // Then send email to party with link
        
        // And return invoice itself to browser or have a different view
        return $invoice->stream();
    }
}
