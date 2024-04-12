<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    function index(Request $request){
        $client = new \GuzzleHttp\Client();
        $response = $client->request("GET","https://restcountries.com/v3.1/all",['verify' => false]);
        $responseBody = $response->getBody()->getContents();
        $responseData = json_decode($responseBody);
        $page = request()->get('page', 1);
        $perPage = 25; 
        $searchTerm     = $request->search;
        $sortDirection  = $request->sort;
        if ($searchTerm) {
            $responseData = $this->fuzzySearch($searchTerm, $responseData);
        }
        // $sortDirection = "asc";
        if ($sortDirection) {
            $responseData = $this->sortByField($responseData,$sortDirection);
        }
        $total = count($responseData);
        $paginatedData = array_slice($responseData, ($page - 1) * $perPage, $perPage);
        $paginator = new LengthAwarePaginator($paginatedData, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
        // dd($paginator);

        return view("welcome",['paginatedData' => $paginator,"search"=>$searchTerm,"sort"=>$sortDirection]);
    }

    function fuzzySearch($query, $data) {
        return collect($data)->filter(function ($item) use ($query) {
            similar_text(strtolower($item->name->official), strtolower($query), $similarity);
            return $similarity > 40; // You can adjust this threshold as needed
        })->values()->all();
    }
    
    function sortByField($data,$direction) {
        if($direction === "desc"){
            return collect($data)->sortByDesc('name.official')->values()->all();
        }elseif ($direction === "asc") {
            return collect($data)->sortBy('name.official')->values()->all();
        }
        return $data;
    }

    function show($name){
        $client = new \GuzzleHttp\Client();
        $response = $client->request("GET","https://restcountries.com/v3.1/name/".$name,['verify' => false]);
        $responseBody = $response->getBody()->getContents();
        $data = json_decode($responseBody);
        $data = $data[0];

        $str_altSpellings="";
        $str_borders="";
        $str_capitalInfo="";
        $str_languages="";
        $str_translations="";
        $str_currencies="";

        $altSpellings   = $data->altSpellings;
        foreach($altSpellings as $alt){
            $str_altSpellings.="
                <span>$alt</span>
            ";
        }
        $area   = $data->area;
        if(isset($data->borders)){
            $borders   = $data->borders;
            foreach($borders as $alt){
                $str_borders.="
                    <span>$alt</span>
                ";
            }
        }
        $capital   = $data->capital;
        $capitalInfo   = $data->capitalInfo->latlng;
        foreach($capitalInfo as $alt){
            $str_capitalInfo.="
                <span>$alt</span>
            ";
        }
        $car   = $data->car->side;
        $cca2   = $data->cca2;
        $cca3   = $data->cca3;
        $ccn3   = $data->ccn3;
        if(isset($data->cioc) || isset($data->fifa)){
            $cioc   = $data->cioc;
            $fifa   = $data->fifa;
        }else{
            $cioc   = "NONE";
            $fifa   = "NONE";
        }
        $coatOfArms   = $data->coatOfArms;
        $continents   = $data->continents;
        $currencies   = $data->currencies;
        foreach($currencies as $alt){
            $str_currencies.= $alt->name."|";
            if(isset($alt->symbol) ){
                $alt->symbol;
            }
        }
        $demonyms   = $data->demonyms;
        $flag   = $data->flag;
        $flags   = $data->flags->png;
        $idd   = $data->idd;
        $independent   = $data->independent;
        $landlocked   = $data->landlocked;
        $languages   = $data->languages;
        foreach($languages as $alt){
            $str_languages= $alt."|".$alt."|".$alt;
        }
        $latlng   = $data->latlng;
        $maps   = $data->maps;
        $name_official= $data->name->official;
        $population   = $data->population;
        $region   = $data->region;
        $startOfWeek   = $data->startOfWeek;
        $status   = $data->status;
        $subregion   = $data->subregion;
        $timezones   = $data->timezones[0];
        $tld   = $data->tld[0];
        $translations   = $data->translations;
        foreach($translations as $alt){
            $str_translations.="
                        $alt->official
                        $alt->common
            ";
        }
        $unMember   = $data->unMember;
        $form="";
        $form='
            <div class="container-popup">
               <div class="container-popup-top"> 
                <div class="container-popup-top-left">ALL INFORMATION</div>
                <div class="container-popup-top-right btn_remove_popup">
                    <i class="fas fa-times"></i>
                </div>
               </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">Altspellings:</div>
                            <div class="col-md-5 popup-detail">'.$str_altSpellings.'</div>
                            <div class="col-md-1 popup-title">Area:</div>
                            <div class="col-md-5 popup-detail">'.$area.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">Borders:</div>
                            <div class="col-md-5 popup-detail">'.$str_borders.'</div>
                            <div class="col-md-1 popup-title">Capital:</div>
                            <div class="col-md-5 popup-detail">'.$capital[0].'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">CapitalInfo:</div>
                            <div class="col-md-5 popup-detail">'.$str_capitalInfo.'</div>
                            <div class="col-md-1 popup-title">Car:</div>
                            <div class="col-md-5 popup-detail">'.$car.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">CCA2:</div>
                            <div class="col-md-5 popup-detail">'.$cca2.'</div>
                            <div class="col-md-1 popup-title">CCA3:</div>
                            <div class="col-md-5 popup-detail">'.$cca3.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">CCN3:</div>
                            <div class="col-md-5 popup-detail">'.$ccn3.'</div>
                            <div class="col-md-1 popup-title">CIOC:</div>
                            <div class="col-md-5 popup-detail">'.$cioc.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">Continents:</div>
                            <div class="col-md-5 popup-detail">'.$continents[0].'</div>
                            <div class="col-md-1 popup-title ">Translations:</div>
                            <div class="col-md-5 popup-detail popup-translate" title="'.$str_translations.'">'.$str_translations.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">Currencies:</div>
                            <div class="col-md-5 popup-detail">'.$str_currencies.'</div>
                            <div class="col-md-1 popup-title">Demonyms:</div>
                            <div class="col-md-5 popup-detail">'.$demonyms->eng->f.'|'.$demonyms->eng->m.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">Fifa:</div>
                            <div class="col-md-5 popup-detail">'.$fifa.'</div>
                            <div class="col-md-1 popup-title">Flag:</div>
                            <div class="col-md-5 popup-detail">'.$flag.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">Flags:</div>
                            <div class="col-md-5 popup-detail">'.$flags.'</div>
                            <div class="col-md-1 popup-title">IDD:</div>
                            <div class="col-md-5 popup-detail">'.$idd->root.'|'.$idd->suffixes[0].'</div>
                        </div>
                         <div class="row">
                            <div class="col-md-1 popup-title">Independent:</div>
                            <div class="col-md-5 popup-detail">'.$independent.'</div>
                            <div class="col-md-1 popup-title">Landlocked:</div>
                            <div class="col-md-5 popup-detail">'.$landlocked.'</div>
                        </div>
                         <div class="row">
                            <div class="col-md-1 popup-title">Languages:</div>
                            <div class="col-md-5 popup-detail">'.$str_languages.'</div>
                            <div class="col-md-1 popup-title">Latlng:</div>
                            <div class="col-md-5 popup-detail">'.$latlng[0].'</div>
                        </div>
                         <div class="row">
                            <div class="col-md-1 popup-title">Maps:</div>
                            <div class="col-md-5 popup-detail">'.$maps->googleMaps.'</div>
                            <div class="col-md-1 popup-title">Name:</div>
                            <div class="col-md-5 popup-detail">'.$name_official.'</div>
                        </div>
                         <div class="row">
                            <div class="col-md-1 popup-title">Population:</div>
                            <div class="col-md-5 popup-detail">'.$population.'</div>
                            <div class="col-md-1 popup-title">Region:</div>
                            <div class="col-md-5 popup-detail">'.$region.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">StartOfWeek:</div>
                            <div class="col-md-5 popup-detail">'.$startOfWeek.'</div>
                            <div class="col-md-1 popup-title">Status:</div>
                            <div class="col-md-5 popup-detail">'.$status.'</div>
                        </div>
                        <div class="row">
                            <div class="col-md-1 popup-title">Subregion:</div>
                            <div class="col-md-5 popup-detail">'.$subregion.'</div>
                            <div class="col-md-1 popup-title">Timezones:</div>
                            <div class="col-md-5 popup-detail">'.$timezones[0].'</div>
                        </div>
                         <div class="row">
                            <div class="col-md-1 popup-title">Tld:</div>
                            <div class="col-md-5 popup-detail">'.$tld.'</div>
                            <div class="col-md-1 popup-title">UnMember:</div>
                            <div class="col-md-5 popup-detail">'.$unMember.'</div>
                        </div>
            </div>
        ';
        
        return response()->json([
            'result'=> $form,
            'status'=>true,
        ]); 
    }

}
