<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Task;
use App\Renewal;
use App\User;
use App\MatterActors;
use App\Mail\sendCall;
use Locale;
use IntlDateFormatter;

class RenewalController extends Controller
{
    public function index(Request $request)
    {
     // Filters
        $MyRenewals = $request->input ( 'my_renewals' );
        $filters = $request->except([
            'my_renewals',
            'page',
            'tab'
        ]);
        $step = $request->input ( 'step' );
        $invoice_step = $request->input ( 'invoice_step' );
        $tab = $request->input ( 'tab' );

        // Get list of active renewals
        $renewals = Renewal::orderby('due_date');
        if ($MyRenewals) {
            $renewals->where('assigned_to', Auth::user()->login);
        }
        $with_step = false;
        $with_invoice = false;
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($value != '') {
                    switch($key) {
                        case 'Title':
                            $renewals->where('title', 'LIKE', "$value%");
                            break;
                        case 'Case':
                            $renewals->where('caseref', 'LIKE', "$value%");
                            break;
                        case 'Qt':
                            $renewals->where('detail', 'LIKE', "$value%");
                            break;
                        case 'Fromdate':
                            $renewals->where('due_date', '>=', "$value%");
                            break;
                        case 'Untildate':
                            $renewals->where('due_date', '<=', "$value%");
                            break;
                        case 'Name':
                            $renewals->where('applicant_dn', 'LIKE', "$value%");
                            break;
                        case 'Country':
                            $renewals->where('country', 'LIKE', "$value%");
                            break;
                        case 'grace':
                            $renewals->where('grace_period',  "$value");
                            break;
                        case 'step':
                            $renewals->where('step',  "$value");
                            if ($value != 0) $with_step = true;
                            break;
                        case 'invoice_step':
                            $renewals->where('invoice_step', "$value");
                            if ($value != 0) $with_invoice = true;
                            break;
                        default:
                            $renewals->where($key, 'LIKE', "$value%");
                            break;
                    }
                }
            }
        }
        if (! ($with_step || $with_invoice)) {
            $renewals = $renewals->where('done',0);
        }
        $renewals = $renewals->simplePaginate(100)->all();
        //$renewals->appends($request->input())->links(); // Keep URL parameters in the paginator links
        return view('renewals.index', compact('renewals','step', 'invoice_step', 'tab'));
    }
    /*
    la mise à jour à la fin ne correspond pas, il faut mettre en accord avec la requête.
    */
    public function firstcall(Request $request)
    { 
        $notify_type[0] = 'first';
        $rep = $this->_call($request->task_ids, $notify_type, 1.0, false);
        if (is_numeric($rep))
        {
            // Move the renewal task to step 2 : reminder
            Task::whereIn('id',$request->task_ids)->update(['step' => 2]);            
            return response()->json(['success' => 'Calls created for '.$rep.' renewals']);
        }
        else
        {
            return response()->json(['error' => $rep], 501);
        }
    }

    public function remindercall(Request $request)
    { 
        $notify_type[0] = 'first';
        $notify_type[1] = 'warn';
        $rep = $this->_call($request->task_ids, $notify_type, 1.0, true);
        if (is_numeric($rep))
        {  
            return response()->json(['success' => 'Calls sent for '.$rep.' renewals']);
        }
        else
        {
            return response()->json(['error' => $rep], 501);
        }
    }
       
    public function lastcall(Request $request)
    { 
        $fee_factor = config('renewal.validity.fee_factor');
        $notify_type[0] = 'last';
        $rep = $this->_call($request->task_ids, $notify_type, $fee_factor, true);
        if (is_numeric($rep))
        {
            // Move the renewal task to grace_period 1
            Task::whereIn('id',$request->task_ids)->update(['grace_period' => 1]);      
            return response()->json(['success' => 'Calls sent for '.$rep.' renewals']);
        }
        else
        {
            return response()->json(['error' => $rep], 501);
        }
    }
    
    function _call($ids, $notify_type, $fee_factor, $reminder)
    { 
        // TODO Manage languages of the calls
        // TODO Manage small entities
        $fmt = new IntlDateFormatter(
            config('app.locale'),
            IntlDateFormatter::FULL,IntlDateFormatter::FULL,
            config('app.timezone'),
            IntlDateFormatter::GREGORIAN,
            'd MMMM yyyy'
        );
        
        if (! isset( $ids))
        {
            return "No renewal selected.";
        }        
        $client_precedent="ZZZZZZZZZZZZZZZZZZZZZZZZ";
        $premier_passage=true;
        $sum = 0;
        for ($grace = 0; $grace < count($notify_type); $grace++)
        {
            $resql = Renewal::whereIn('id',$ids)->orderBy( 'client_dn', "ASC")->where('grace_period',$grace)->get();
            $num=$resql->count(); 
            $sum = $sum + $num;
            if ($grace == 1 && $sum === 0)
            {
                return "No renewal selected.";
            }
            
            if ($num != 0)
            {
                $i=0;
                $ren = $resql[0]->toArray();
                while ($i < $num)
                {
                    $client = $ren['client_dn'];
                    $email = $ren['email'];
                    $due_date = strtotime($ren['due_date']);
                    if ($grace)
                    {
                    //  Add six months as grace grace_period
                    // TODO Get the grace period from a rule according to country
                        $due_date = strtotime("+6 months", $due_date);
                    }
                    
                    if ($premier_passage) 
                    {
                        $premier_passage=false;
                        $earlier = $due_date;
                        $renewals = [];
                        $total = 0;
                        $total_ht = 0;
                    }
                    else
                    {
                        $earlier = min($earlier, $due_date);
                    }
                    $renewal = [];
                    $desc= $ren['caseref'].$ren['suffix']." : Annuité du titre n°".$ren['number'];
                    if ($ren['event_name']=='FIL') {$desc.=" déposé le ";}
                    if ($ren['event_name']=='GRT' or $ren['event_name']=='PR') {$desc.=" délivré le ";}
                    $desc.= $fmt->format(strtotime($ren['event_date']));
                    if ($ren['title'] != '') {$desc.="<BR>Sujet : ".$ren['title'];}
                    $renewal['due_date'] = $fmt->format($due_date);
                    $renewal['country'] = $ren['country_FR'];
                    $renewal['desc'] = $desc;
                // Détermine le taux de tva // TODO
                    $renewal['annuity'] = $ren['detail'];
                    $tx_tva = 0.2;
                    $renewal['tx_tva'] =  $tx_tva * 100;
                    if ($grace) {
                        $cost = $ren['cost_sup'];
                        $fee =  $ren['fee_sup'];
                    }
                    else
                    {
                        $cost = $ren['cost'];
                        $fee =  $ren['fee'];
                    }
                    $renewal['cost'] =  number_format($cost, 2, ',',' ');
                    $renewal['fee'] =  number_format($fee * $fee_factor, 2, ',',' ');
                    $renewal['tva'] =  $fee * $fee_factor *  $tx_tva;
                    $renewal['total_ht'] = number_format($fee * $fee_factor + $cost, 2, ',',' ');
                    $renewal['total'] = number_format($fee * $fee_factor * (1 + $tx_tva) + $cost, 2, ',',' ');
                    $total = $total + floatval($renewal['total']);
                    $total_ht = $total_ht + floatval($renewal['total_ht']);
                    $client_precedent = $client;
                    $i++;
                    array_push($renewals, $renewal);
                    if ($i < $num) 
                    {
                        $ren = $resql[$i];
                        $client = $ren['client_dn'];
                    }
                    if ($client != $client_precedent || $i == $num) 
                    {
                        // Send mail
                        // TODO  Parameter the delays. No date earlier as today.
                        if ($notify_type =='last') 
                        {
                            $validity_date = $fmt->format($earlier - config('renewal.validity.before_last') * 3600 *24);
                            $instruction_date = $validity_date;
                        }
                        else
                        {
                            $validity_date = $fmt->format($earlier - config('renewal.validity.before') * 3600 *24);
                            $instruction_date = $fmt->format($earlier  - config('renewal.validity.instruct_before') * 3600 *24);
                        }
                        $contacts = new MatterActors();
                        $contacts = $contacts->select('email','name','first_name')->where('matter_id',$ren['matter_id'])->where('role_code','CNT')->get();
                        $dest = "Bonjour,";
                        $email_list = [];
                        if ($contacts->count() === 0) {
                            // No contact registered, using client email
                            $user = new User();
                            $user = $user->where('id',$ren['client_id'])->first();
                            if  ($user->email == '')
                                return "No email address for $user->name.";
                            array_push($email_list,['email' => $user->email, 'name' =>$user->first_name.' '.$user->name]);
                        }
                        else {
                            foreach ($contacts as $contact) {
                                array_push($email_list,['email' => $contact['email'], 'name' =>$contact['first_name'].' '.$contact['name']]);
                            }
                        }
                        Mail::to($email_list)->bcc(Auth::user())
                            ->send(new sendCall(
                                $notify_type[$grace],
                                $renewals, 
                                $validity_date, 
                                $instruction_date, 
                                number_format($total, 2, ',',' '),
                                number_format($total_ht, 2, ',',' '),
                                $reminder ?  '[Rappel] Appel pour le renouvellement de brevets': 'Appel pour le renouvellement de brevets',
                                $dest = $dest
                            ));
                        $premier_passage = true;
                        $renewals = [];
                    }
                }
            }
        }
        return $sum;
    }

    public function topay(Request $request)
    {
        if (isset( $request->task_ids))
        {
            
            Task::whereIn('id',$request->task_ids)->update(['step' => 4, 'invoice_step' => 1]);
            return response()->json(['success' => 'Marked as to pay']);
        }
        else
        {
            return response()->json(['error' => "No renewal selected."]);
        }
    }
   
    public function invoice(Request $request)
    { 

        $fmt = new IntlDateFormatter(
            config('app.locale'),
            IntlDateFormatter::FULL,IntlDateFormatter::FULL,
            config('app.timezone'),
            IntlDateFormatter::GREGORIAN,
            'd MMMM yyyy'
        ); 
        if (isset( $request->task_ids))
        {
            $query = Renewal::whereIn('id',$request->task_ids);
        }
        else
        {
                return response()->json(['error' => "No renewal selected."]);
        }
        $resql = $query->orderBy( 'client_dn', "ASC")->get();
        $client_precedent="ZZZZZZZZZZZZZZZZZZZZZZZZ";
        $premier_passage=true;
        // get from config/renewal.php 
        $apikey =  config('renewal.api.DOLAPIKEY');
        if ($apikey == null) {
            return response()->json(['error' => "Api is not configured"]);
        }
        if ($resql)
        {
            $num=$resql->count();
            if ($num == 0)
            {
                return response()->json(['error' => "No renewal selected."]);
            }
            else
            {
                $i=0;
                $ren = $resql[0];
                while ($i < $num)
                {
                    $client = $ren['client_dn'];
                    if ($premier_passage) 
                    {
                        // retrouve la correspondance de société
                        $result = $this->_client($client, $apikey);
                        if (isset($result["error"]) && $result["error"]["code"] >= "404") {
                            return response()->json(['error' => $client." not found in Dolibarr.\n"]);
                        }
                        $premier_passage=false;
                        $soc_res = $result[0];
                        $earlier = strtotime($ren['due_date']);
                    }
                    else
                    {
                        $earlier = min($earlier, strtotime($ren['due_date']));
                    }
                    $desc = $ren['caseref'].$ren['suffix']." : Annuité pour l'année ".$ren['detail']." du titre n°".$ren['number'];
                    if ($ren['event_name']=='FIL') {$desc.=" déposé le ";}
                    if ($ren['event_name']=='GRT' or $ren['event_name']=='PR') {$desc.=" délivré le ";}
                    $desc.= $fmt->format(strtotime($ren['event_date']));
                    $desc.=' en '.$ren['country_FR'];
                    if ($ren['title'] != '') {$desc.="\nSujet : ".$ren['title'];}
                    $desc.="\nÉchéance le ".$fmt->format(strtotime($ren['due_date']));
                // Détermine le taux de tva
                    if ($soc_res['tva_intra'] == "" || substr($soc_res['tva_intra'],2) == "FR") 
                    {
                        $tx_tva = 0.2;
                    }
                    else 
                    {
                        $tx_tva = 0.0;
                    }
                    if ($ren['grace_period'] == 1)
                    {
                        $fee = $ren['fee'];
                        if( strtotime($ren['done_date']) < $ren['due_date']) {
                        // late payment
                            $fee = $ren['fee'] * 1.2 ;
                            $cost =  $ren['cost'];
                        }
                        else
                        {
                            $fee = $ren['fee_sup'];
                            $cost =  $ren['cost_sup'];
                        }
                    }
                    else
                    {
                        $fee = $ren['fee'];
                        $cost =  $ren['cost'];
                    }
                    if ($cost != 0) 
                    {
                        $desc.="\nHonoraires pour la surveillance et le paiement";
                    }
                    else 
                    {
                        $desc.="\nHonoraires et taxe";
                    }
                    $newlignes[] = [
                    "desc"=>$desc,
                    "product_type"=> 1,
                    "tva_tx"=>($tx_tva * 100),
                    "remise_percent"=>0,
                    "qty"=>1,
                    "subprice"=>$fee,
                    "total_tva"=>$fee * $tx_tva,
                    "total_ttc"=>$fee  * (1.0 +  $tx_tva)
                    ];
                    if ($cost != 0)
                    {
                        // Ajout d'une deuxième ligne 
                        $newlignes[] = [
                        "product_type" => 1,
                        "desc"=>"Taxe",
                        "tva_tx"=>0.0,
                        "remise_percent"=>0,
                        "qty"=>1,
                        "subprice"=>$cost,
                        "total_tva"=>0,
                        "total_ttc"=>$cost
                        ];
                    }
                    $client_precedent = $client;
                    $i++;
                    if ($i < $num) 
                    {
                        $ren = $resql[$i];
                        $client = $ren['client_dn'];
                    }
                    if ($client != $client_precedent || $i == $num) 
                    {
                        // Create propale
                        $newprop = [
                            "socid"	=> $soc_res['id'],
                            "date" => time(),
                            "cond_reglement_id" => 1,
                            "mode_reglement_id" => 2,
                            "lines" => $newlignes,
                            "fk_account" => config('renewal.api.fk_account')
                            ];
                        $rc = $this->create_invoice($newprop,$apikey ); // invoice creation
                        if ($rc[0] != 0) {
                            return response()->json(['error' => $rc[1] ]);
                        }
                        $newlignes = [] ;
                        $premier_passage = true;
                    }
                }
                // Move the renewal task to step  : invoiced
                Task::whereIn('id',$request->task_ids)->update(['invoice_step' => 2]);
                return response()->json(['success' => 'Invoices created for '.$num.' renewals']);
            }
        }
    }

    function _client($client, $apikey) {
        // serach for client correspondance in Dolibarr
        $curl = curl_init();
        $httpheader = ['DOLAPIKEY: '.$apikey];
        $data = ['sqlfilters' =>'(t.nom like "'.$client .'%")'];
        
        // Get from config/renewal.php
        $url = config('renewal.api.dolibarr_url')."/thirdparties?".http_build_query($data);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }

    function create_invoice($newprop, $apikey ) 
    {
        // Create invoice
        $curl = curl_init();
        $url = config('renewal.api.dolibarr_url')."/invoices";
        curl_setopt($curl, CURLOPT_POST, 1);
        $httpheader = ['DOLAPIKEY: '.$apikey];
        $httpheader[] = "Content-Type:application/json";
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($newprop));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        $result =json_decode($result, true);

        if (isset($result["error"]) ) {
            // "Error creating the invoice.\n";
            return [-1, $result["error"]];
        }
        elseif ($status = 0) {
            return [-1, "Invoice API is not reachable"];
        }
        else
        {
            return [0, $result];
        }
    }

    /**
     * clear selected renewals.
     *
     */
    public function done(Request $request)
    {
        $data = json_decode($request->getContent());
        if (isset( $data->task_ids))
        {
            $query = Renewal::whereIn('id',$data->task_ids);
        }
        else
        {
            return response()->json(['error' => "No renewal selected."]);
        }
        $resql = $query->get();
        
        $done_date = now();
        $updated = 0;
        foreach($resql as $ren) {
            $task = Task::find($ren->id);
            $task->done_date = $done_date;
            $task->done = 1;
            $task->step = 6;
            $returncode = $task->save();
            if ($returncode) $updated = $updated + 1;
        }
        return response()->json(['success' => strval($updated).' renewals cleared']);
    }

    /**
     * register receipts.
     *
     */
    public function receipt(Request $request)
    {
        $data = json_decode($request->getContent());
        if (isset( $data->task_ids))
        {
            $query = Renewal::whereIn('id',$data->task_ids);
        }
        else
        {
            return response()->json(['error' => "No renewal selected."]);
        }
        $resql = $query->get();
        
        $updated = 0;
        foreach($resql as $ren) {
            $task = Task::find($ren->id);
            $task->step = 8;
            $returncode = $task->save();
            if ($returncode) $updated = $updated + 1;
        }
        return response()->json(['success' => strval($updated).' receipts registered']);
    }

    
    /**
     * register receipts.
     *
     */
    public function closing(Request $request)
    {
        $data = json_decode($request->getContent());
        if (isset( $data->task_ids))
        {
            $query = Renewal::whereIn('id',$data->task_ids);
        }
        else
        {
            return response()->json(['error' => "No renewal selected."]);
        }
        $resql = $query->get();
        
        $updated = 0;
        foreach($resql as $ren) {
            $task = Task::find($ren->id);
            $task->step = 10;
            $returncode = $task->save();
            if ($returncode) $updated = $updated + 1;
        }
        return response()->json(['success' => strval($updated).' closed']);
    }

    /**
     * Generate order.
     *
     */
    public function renewalOrder(Request $request)
    {
        //Locale::setDefault('fr-FR');
        $fmt = new IntlDateFormatter(
            config('app.locale'),
            IntlDateFormatter::FULL,IntlDateFormatter::FULL,
            config('app.timezone'),
            IntlDateFormatter::GREGORIAN,
            'yyyyMMdd'
        );
        
        $data = json_decode($request->getContent());
        $tids = $data->task_ids;
        $procedure = '';
        
        $clear = boolval($data->clear);
        $done_date = now();
        $xml = config('renewal.xml.header');
        $xml = str_replace('NAME',Auth::user()->name,$xml);
        $xml = str_replace('TRANSACTION','ANNUITY '.$fmt->format(time()),$xml);
        $total = 0;
        foreach($tids as $id) {
            $task = Renewal::find($id);
            $procedure = $task->country;
            $country = $task->country;
            if ($task->country == 'EP')
            {
                $fee_code = "0" .strval(intval($task->detail) + 30);
            }
            else 
            {
                $fee_code = $task->detail;
            }
            $total += floatval($task->cost);
            if ($country == 'EP' || $country == 'FR' )
            {
                if ($task->origin == 'EP')
                {
                    $number = substr_compare($task->pub_num, 'EP', 0, 2) ?  $task->pub_num : substr($task->pub_num, 2, strlen($task->pub_num)) ;
                    $country = 'EP';
                }
                else
                {
                    $number = substr_compare($task->number, $country, 0, 2) ? $task->number : substr($task->number, 2, strlen($task->number)) ;
                }
            }
            $xml = $xml . '<fees procedure="'.$procedure.'">
			<document-id>
				<country>'.$country.'</country>
				<doc-number>'.$number.'</doc-number>
				<date>'.$fmt->format(strtotime($task->event_date)).'</date>
				<kind>application</kind>
			</document-id>
			<file-reference-id>'.$task->caseref.$task->suffix.'</file-reference-id>
			<owner>'.$task->applicant_dn.'</owner>
			<fee>
				<type-of-fee>'.$fee_code.'</type-of-fee>
				<fee-sub-amount>'.$task->cost.'</fee-sub-amount>
				<fee-factor>1</fee-factor>
				<fee-total-amount>'.$task->cost.'</fee-total-amount>
				<fee-date-due>'.$task->due_date.'</fee-date-due>
			</fee>
		</fees>';
        }
        $footer = config('renewal.xml.footer');
        if ($procedure == 'EP')
        {
            $footer = str_replace('DEPOSIT',config('renewal.xml.EP_deposit'),$footer);
        }
        if ($procedure == 'FR')
        {
            $footer = str_replace('DEPOSIT',config('renewal.xml.FR_deposit'),$footer);
        }
        $footer = str_replace('TOTAL',$total, $footer);
        $footer = str_replace('COUNT',count($tids), $footer);
        $xml .= $footer;
        $fd = fopen(storage_path().'/order.xml', 'w');
        fwrite($fd, $xml);
        fclose($fd);
        if ($clear) {
            $updated = 0;
            foreach($tids as $id) {
                $task = Task::find($id);
                $task->done_date = $done_date;
                $task->done = 1;
                $task->step = 6;
                $returncode = $task->save();
                if ($returncode) $updated ++;
            }
        }
        $headers = array(
        'Content-Type: application/octet-stream',
        );
        return response()->download(storage_path(). '/order.xml', 'order.xml', $headers)->deleteFileAfterSend(); 

    }
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Renewal  $renewal
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, Task $renewal)
    {
        $this->validate($request, [
            'cost' => 'nullable|numeric',
            'fee' => 'nullable|numeric'
        ]);

        $renewal->update($request->except(['_token', '_method']));
        return response()->json(['success' => 'Renewal updated']);
    }
}
