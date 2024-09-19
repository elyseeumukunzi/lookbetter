<?php

namespace App\Exports;

use App\Models\Bill_info;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Insurance;
use App\Models\Product;
use App\Models\TypeOfConsultation;
use App\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportBill implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
    /**
     * @return array
     */

    public function headings(): array
    {
        return [
            'No',
            "Date",
            'Client Names',
            'Insurance',
            'TM',
            'Service',
            'Products Sold',
            'Total Bill',
            'Insurance Bill',
            'Client Bill',
        ];
    }

    public function array(): array
    {
        $bills = $this->data;
        $billsrepport = [];
        $count = 0;
        $totalbill = 0;
        $insurancebill = 0;
        $clientbill = 0;
        $products = [];
        $phone = '0789817969';       
        foreach ($bills as $bill) {            
            $count = $count + 1;
            $consultationid=$bill['consultation_id'];
            $consultationinfo=Consultation::find($consultationid);
            $clientinfo=Client::find($consultationinfo->client_id);
            $insuranceinfo=Insurance::find($consultationinfo->insurance_id);
            $serviceinfo=TypeOfConsultation::find($consultationinfo->type_of_consultations_id);
            $billsinfo=Bill_info::where('bill_id',$bill['id'])->get();
            $billinfos=$billsinfo->toArray();
            $products = [];
            $productcost=0;
            foreach ($billinfos as $billinfo) {
                $productinfo=Product::find($billinfo['product_id']);
                $productname=$productinfo->productname;
                $products [] = [$productname];
                $productcost = $productcost + $billinfo['subtotal'];
            }
            $fullname=$clientinfo->firstname." ".$clientinfo->lastname;
            $consultationcost=$bill['consultation_cost'];
            $subtotal =$productcost + 0;  //consultation cost is not covered by insurance thats why zero
            $totalbi = $subtotal + $consultationcost; //all bills without insurance sh**
            $totalbill = $subtotal + $totalbill;
            $clientbi = ($subtotal * $insuranceinfo->tm) / 100;
            $clientbill =$clientbi + $consultationcost;

            $insurancetm=100-$insuranceinfo->tm;
            $insurancebill=($subtotal * $insurancetm) / 100;
            $dates = $bill['dates'];
            $billsrepport [] = [
                $count,
                $dates,
                $fullname,
                $insuranceinfo->name,
                $insuranceinfo->tm,
                $serviceinfo->type,
                $products,
                $totalbi,
                $insurancebill,
                $clientbill,
                
            ];

        }
       //Input messages to increave the incidence reporting
        // $messagedta = array(
        //     "sender" => 'LOOK BETTER',
        //     "recipients" => $phone,
        //     "message" => 'Total bill:' . $totalbill,
        //     "dlrurl" => ""
        // );
        // $url = "https://www.intouchsms.co.rw/api/sendsms/.json";
        // $data = http_build_query($messagedta);
        // $username = "innovate.solutions";
        // $password = "innovate.solutions";
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $messagedta);
        // curl_close($ch);
       // dd($billsrepport);
        return [$billsrepport];
    }

}

class ExportDailyRepport implements FromArray
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
    /**
     * @return array
     */

     public function array(): array
     {
        $dailyrepport = [];
        dd($dailyrepport);
        return [$dailyrepport];
     }

}
