<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Bill_info;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Insurance;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\TypeOfConsultation;
use App\Models\User;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;

use Illuminate\Http\Request;

class DownloadBill extends Controller
{
    public function download(Request $id)
    {
        //lets call to all the data we'll need to display on the spectacle presciption 

        $billid = $id->query('id');
        $billinfo = Bill::find($billid)->toArray();
        $consultationid = $billinfo['consultation_id'];
        $consultationinfo = Consultation::find($consultationid);
        $insuranceinfo = Insurance::find($consultationinfo->insurance_id);
        $consultationtypeinfo = TypeOfConsultation::find($consultationinfo->type_of_consultations_id);
        $consultationtype = $consultationtypeinfo->type;
        $consultationcost = $billinfo['consultation_cost'];

        //$patientconsultationbill = ($consultationcost * $insuranceinfo->tm) / 100;
        $patientconsultationbill = $consultationcost;  //consultation cost is always private

        $insuranceconsultationbill = $consultationcost - $patientconsultationbill;
        $clientinfo = Client::find($consultationinfo->client_id);
        $prescriptionstatus = $consultationinfo->prescription_status;
        if ($prescriptionstatus == 1) {
            $prescriptioninfo = Prescription::where('consultation_id', $consultationid)->first();

            if (!empty($prescriptioninfo)) {
                $prescriptioninfo->toArray();
            }

        }

        $doctor = User::find($consultationinfo->doctor_id);
        if(!empty($doctor)) {
        $doctorinfo=$doctor->toArray();


        }

        $client = new Party([
            'name' => $clientinfo['firstname'] . " " . $clientinfo['lastname'],
            'phone' => $clientinfo['phonenumber'],
            'id' => $clientinfo['nid'],
            'insurancename' => $insuranceinfo['name'],
            'tm' => $insuranceinfo['tm'],
            'insurancecontacts' => $insuranceinfo['contacts'],
            'insurancetin' => $insuranceinfo['tin'],
            'dates' => $consultationinfo['dates'],
            'dob' => $clientinfo['dob'],
            'sex' => $clientinfo['sex'],
            'mainmember' => $clientinfo['mainmember'],
            'affiliatesociety' => $clientinfo['affiliatesociety'],
            'cardnumber' => $clientinfo['cardnumber'],
            'relationship' => $clientinfo['relationship'],
            'RDVsphere' => $consultationinfo['RDVsphere'],
            'RDVcylinder' => $consultationinfo['RDVcylinder'],
            'RDVaxis' => $consultationinfo['RDVaxis'],
            'LDVsphere' => $consultationinfo['LDVsphere'],
            'LDVcylinder' => $consultationinfo['LDVcylinder'],
            'LDVaxis' => $consultationinfo['LDVaxis'],
            'RNV' => $consultationinfo['RNV'],
            'LNV' => $consultationinfo['LNV'],
            'distant_comment' => $consultationinfo['distant_comment'],
            'near_comment' => $consultationinfo['near_comment'],
            'ingredient' => $prescriptioninfo['ingredient'] ?? 'No lens selected',
            'nature' => $prescriptioninfo['nature'] ?? '',
            'reaction' => $prescriptioninfo['reaction'] ?? '',
            'light' => $prescriptioninfo['light_reaction'] ?? '',
            'consultationtype' => $consultationtype,
            'consultationcost' => $consultationcost,
            'patientconsultationbill' => $patientconsultationbill,
            'insuranceconsultationbill' => $insuranceconsultationbill

        ]);

        $customer = new Party([
            'name' => 'Ashley Medina',
            'address' => 'The Green Street 12',
            'code' => '#22663214',
            'custom_fields' => [
                'order number' => '> 654321 <',
            ],
        ]);

        // $productsss = new Party([
        //     'name' => 'Ashley Medina',
        //     'priceperunit' => 'The Green Street 12',
        //     'description' => '#22663214',
        //     'custom_fields' => [
        //         'order number' => '> 654321 <',
        //     ],
        // ]);
    

        $productsinfo = Bill_info::where('bill_id', $billinfo['id'])->get()->toArray();
        $items = [];    
        $tm=$insuranceinfo['tm'];
        $insurancetm= 100 - $tm;
        $totalbill = 0;
        $insurancebill=0;
        $clientbill=0;
        foreach ($productsinfo as $products) {
            //single product information
            $productinfo = Product::find($products['product_id']);
            $subtotal=$products['subtotal'];
            $quantity=$products['quantity'];
            $priceperunit=$subtotal / $quantity;   
            $insurancebi=$subtotal * $insurancetm;
            $insurancewillpay=$insurancebi / 100;
            $patientwillpay=$subtotal - $insurancewillpay;
            $clientbill = $clientbill + $patientwillpay;
            $insurancebill = $insurancebill + $insurancewillpay;   
            $totalbill=$subtotal + $totalbill;    
        


            //
            // $insurancebi = $item['subtotal'] * $insurancebill;
            // $insurancewillpay = $insurancebi / 100;
            // $patientwillpay = $item['subtotal'] - $insurancewillpay;
            // $totalbill = $totalbill + $item['subtotal'];
            // $totalinsurancebill = $totalinsurancebill + $insurancewillpay;
            // $totalpatientbill = $totalpatientbill + $patientwillpay;
            

            $items[] = InvoiceItem::make($productinfo->productname)
                ->quantity($quantity)
                ->priceperunit($priceperunit)
                ->subtotal($subtotal)
                ->clientBill($patientwillpay)
                ->insuranceBill($insurancewillpay)
                ->totalInsuranceBill($insurancebill+$insuranceconsultationbill)
                ->totalClientBill($clientbill+$patientconsultationbill)
                ->totalBill($totalbill+$consultationcost);
                




        }





        // $items = [
        //     InvoiceItem::make('Service 1')
        //         ->description('Your product or service description')
        //         ->pricePerUnit(47.79)
        //         ->quantity(2)
        //         ->discount(10),
        //     InvoiceItem::make('Service 2')->pricePerUnit(71.96)->quantity(2),
        //     InvoiceItem::make('Service 3')->pricePerUnit(4.56),
        //     InvoiceItem::make('Service 4')->pricePerUnit(87.51)->quantity(7)->discount(4)->units('kg'),
        //     InvoiceItem::make('Service 5')->pricePerUnit(71.09)->quantity(7)->discountByPercent(9),
        //     InvoiceItem::make('Service 6')->pricePerUnit(76.32)->quantity(9),
        //     InvoiceItem::make('Service 7')->pricePerUnit(58.18)->quantity(3)->discount(3),
        //     InvoiceItem::make('Service 8')->pricePerUnit(42.99)->quantity(4)->discountByPercent(3),
        //     InvoiceItem::make('Service 9')->pricePerUnit(33.24)->quantity(6)->units('m2'),
        //     InvoiceItem::make('Service 11')->pricePerUnit(97.45)->quantity(2),
        //     InvoiceItem::make('Service 12')->pricePerUnit(92.82),
        //     InvoiceItem::make('Service 13')->pricePerUnit(12.98),
        //     InvoiceItem::make('Service 14')->pricePerUnit(160)->units('hours'),
        //     InvoiceItem::make('Service 15')->pricePerUnit(62.21)->discountByPercent(5),
        //     InvoiceItem::make('Service 16')->pricePerUnit(2.80),
        //     InvoiceItem::make('Service 17')->pricePerUnit(56.21),
        //     InvoiceItem::make('Service 18')->pricePerUnit(66.81)->discountByPercent(8),
        //     InvoiceItem::make('Service 19')->pricePerUnit(76.37),
        //     InvoiceItem::make('Service 20')->pricePerUnit(55.80),
        // ];

        $notes = [
            'your multiline',
            'additional notes',
            'in regards of delivery or something else',
        ];
        $notes = implode("<br>", $notes);

        $invoice = Invoice::make('SPECTACLES PRESCRIPTION')
            ->series('BIG')
            ->template('bill')
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
            ->addItems($items ?? '')
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
