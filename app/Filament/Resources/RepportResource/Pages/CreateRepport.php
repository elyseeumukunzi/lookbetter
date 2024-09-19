<?php

namespace App\Filament\Resources\RepportResource\Pages;

use App\Filament\Resources\RepportResource;
use App\Models\Bill;
use App\Models\Bill_info;
use App\Models\Consultation;
use App\Models\Insurance;
use App\Models\Repport;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRepport extends CreateRecord
{
 
    protected static string $resource = RepportResource::class;

    protected function handleRecordCreation(array $data): Model
    {
       //dd($data);
       $repportinfo = [];
       $fromdates=$data['from_date'];
       $todates=$data['to_date'];
       $bills=Bill::where('dates', '>=', $fromdates, 'and' , 'dates' , '<=', $todates)->get()->toArray();
       $totalsales=0;
       $totalcashathand=0;
       $totalcashatpartners=0;
       $totalexpense=0;
       $totaltax=0;
       $productcost=0;
       $insurancebill=0;
       $clientbill=0;
       $subtotal=0;

       foreach ($bills as $bill) {
        $consultationid=$bill['consultation_id'];
        $consultationcost=$bill['consultation_cost'];
        $billid=$bill['id'];
        $subtotal=$productcost+0;
        $totalsales=$subtotal + $totalsales;
        $billinfo=Bill_info::where('Bill_id', $billid)->get()->toArray();
        foreach ($billinfo as $product) {
            $productcost=$productcost+ $product['subtotal'];         
            
        }
        $consultationinfo=Consultation::find($consultationid);
        $insuranceinfo=Insurance::find($consultationinfo->insurance_id);
        $tm=$insuranceinfo->tm;        
        $clientbill = ($subtotal * $tm) / 100;
        $insurancebill=$subtotal - $clientbill;
        $totalcashatpartners=$totalcashatpartners + $insurancebill;
        $totalclientbill=$clientbill + $consultationcost;
        $totalcashathand=$totalcashathand + $totalclientbill; 
       }
       //dd($insurancebill);
       //retrieve total expenses for the selected date range
      $repportinfo = [
        'type' => $data['type'],
        'total_sales' => $totalsales,
        'cash_at_hand' => $totalcashathand,
        'cash_at_partners' => $totalcashatpartners,
        'total_expence' => 100000,
        'total_tax' => $data['total_tax'],
        'from_date' => $fromdates,
        'to_date' => $todates,
        'sms_sent_status' => $data['sms_sent_status']
      ];      
      Repport::insert($repportinfo);
      $sms=$data['sms_sent_status'];
      $phone='0789817969';
      $repporttype=$data['type'];
      if($sms)
      {
        //Input messages to increase the incidence reporting on higher level of managements if the status is on  
        $messagedta = array(
            "sender" => 'LOOK BETTER',
            "recipients" => $phone,
            "message" => $repporttype.'from '.$fromdates.' to '.$todates. 'Cash at hands:'. $totalcashathand.'RWF, Cash at partners:'.$totalcashatpartners.' Total expense:'.$totalexpense,
            "dlrurl" => ""
        );
        $url = "https://www.intouchsms.co.rw/api/sendsms/.json";
        $data = http_build_query($messagedta);
        $username = "innovate.solutions";
        $password = "innovate.solutions";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $messagedta);
        curl_close($ch);
      }
      
      return new Repport();

       //dd($totalsales);

       

    }
}
