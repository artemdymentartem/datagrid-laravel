<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use Schema;

class CorporationController extends Controller
{
    public function index()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://data.gov.il/api/3/action/datastore_search?resource_id=be5b7935-3922-45d4-9638-08871b17ec95');

        $response->getStatusCode(); // 200
        $response->getHeaderLine('content-type'); // 'application/json; charset=utf8'
        $result = json_decode($response->getBody()); // '{"id": 1420053, "name": "guzzle", ...}'
        
        $datas = $result->result->records;
        $indexes = [];

        if (count($datas) > 0)  {
            $json = $datas[0];
            $resArr = json_decode( json_encode($json), true);
            $indexes = array_keys($resArr);
        }

        return view("datatable", compact("datas", "indexes"));
    }

    public function datasets($datasets)
    {
        $url = "";
        $table_name = "";
        $db_table = "corporation_" . $datasets;
        $tab_name = "תאגיד";

        $records = [];
        $fields = [];
        
        switch ($datasets) {
            case 'gsa':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=2d5abbad-4809-4900-b74f-b2f8b40bcfb8&limit=1000";
                $link = "https://data.gov.il/dataset/gsa/resource/2d5abbad-4809-4900-b74f-b2f8b40bcfb8";
                $table_name = "רשימת חברות ממשלתיות";
                break;
            
            case 'ica_companies':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=f004176c-b85f-4542-8901-7b3176f9a054&limit=1000";
                $link = "https://data.gov.il/dataset/ica_companies/resource/f004176c-b85f-4542-8901-7b3176f9a054";
                $table_name = "מאגר חברות - רשם החברות";
                break;
            
            case 'ica_partnerships':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=139aa193-fabb-4f6b-a71b-0bb40fd73eb2&limit=1000";
                $link = "https://data.gov.il/dataset/ica_partnerships/resource/139aa193-fabb-4f6b-a71b-0bb40fd73eb2";
                $table_name = "רשימת השותפויות";
                break;
                
            case 'membership-in-liquidation':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=6f3f0df3-5968-4135-81c5-8dd76bf89410&limit=1000";
                $link = "https://data.gov.il/dataset/membership-in-liquidation/resource/6f3f0df3-5968-4135-81c5-8dd76bf89410";
                $table_name = "חברות בפרוק מרצון בהליך מזורז";
                break;

            case 'moj-amutot1':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=be5b7935-3922-45d4-9638-08871b17ec95&limit=1000";
                $link = "https://data.gov.il/dataset/moj-amutot/resource/be5b7935-3922-45d4-9638-08871b17ec95";
                $table_name = "מאגר עמותות לתועלת הציבור";
                break;
                
            case 'moj-amutot2':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=85e40960-5426-4f4c-874f-2d1ec1b94609&limit=1000";
                $link = "https://data.gov.il/dataset/moj-amutot/resource/85e40960-5426-4f4c-874f-2d1ec1b94609";
                $table_name = "מאגר חברות לתועלת הציבור";
                break;
                
            case 'pr2018':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=d8715392-287f-49b7-9ae3-f21ec5bf55f3&limit=1000";
                $link = "https://data.gov.il/dataset/pr2018/resource/d8715392-287f-49b7-9ae3-f21ec5bf55f3";
                $table_name = "הכונס הרשמי  חברות";
                break;
                
            case 'pinkashakablanim':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=4eb61bd6-18cf-4e7c-9f9c-e166dfa0a2d8&limit=1000";
                $link = "https://data.gov.il/dataset/pinkashakablanim/resource/4eb61bd6-18cf-4e7c-9f9c-e166dfa0a2d8";
                $table_name = "קבלנים רשומים להוציא את ";
                break;

            case 'ica-changes':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=1000";
                $link = "https://data.gov.il/dataset/ica-changes/resource/28780ab5-3ef1-44c7-8377-da82c0aa6781";
                $table_name = "פרטי שינויים במאגר חברות חברות";
                break;

            case 'limit':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=1000";
                $link = "https://data.gov.il/dataset/ica-changes/resource/28780ab5-3ef1-44c7-8377-da82c0aa6781";
                $table_name = "תאגידים מוגבלים";
                break;

            default:
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=1000";
                $link = "https://data.gov.il/dataset/ica-changes/resource/28780ab5-3ef1-44c7-8377-da82c0aa6781";
                $table_name = "תאגידים מוגבלים";
                break;
        }

        if (Schema::hasTable($db_table)) {
            // $records = DB::table($db_table)->get();
            $fields = Schema::getColumnListing($db_table);
        }
        else {
            $fields=["default"];
        }

        $tab_en = "corporation";

        return view("datatable", compact("fields", "table_name", "link", "tab_name", "datasets", "tab_en"));
    }

    public function reload($datasets)
    {
        $db_table = "corporation_" . $datasets;
        Schema::dropIfExists($db_table);
        
        switch ($datasets) {
            case 'gsa':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=2d5abbad-4809-4900-b74f-b2f8b40bcfb8&limit=1000";
                $table_name = "רשימת חברות ממשלתיות";
                break;
            
            case 'ica_companies':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=f004176c-b85f-4542-8901-7b3176f9a054&limit=1000";
                $table_name = "מאגר חברות - רשם החברות";
                break;
            
            case 'ica_partnerships':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=139aa193-fabb-4f6b-a71b-0bb40fd73eb2&limit=1000";
                $table_name = "רשימת השותפויות";
                break;
                
            case 'membership-in-liquidation':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=6f3f0df3-5968-4135-81c5-8dd76bf89410&limit=1000";
                $table_name = "חברות בפרוק מרצון בהליך מזורז";
                break;

            case 'moj-amutot1':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=be5b7935-3922-45d4-9638-08871b17ec95&limit=1000";
                $table_name = "מאגר עמותות לתועלת הציבור";
                break;
                
            case 'moj-amutot2':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=85e40960-5426-4f4c-874f-2d1ec1b94609&limit=1000";
                $table_name = "מאגר חברות לתועלת הציבור";
                break;
                
            case 'pr2018':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=d8715392-287f-49b7-9ae3-f21ec5bf55f3&limit=1000";
                $table_name = "הכונס הרשמי  חברות";
                break;
                
            case 'pinkashakablanim':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=4eb61bd6-18cf-4e7c-9f9c-e166dfa0a2d8&limit=1000";
                $table_name = "קבלנים רשומים להוציא את ";
                break;

            case 'ica-changes':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=1000";
                $table_name = "פרטי שינויים במאגר חברות חברות";
                break;

            case 'limit':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=1000";
                $table_name = "תאגידים מוגבלים";
                break;

            default:
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=1000";
                $table_name = "תאגידים מוגבלים";
                break;
        }
        
        do {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $url);

            $response->getStatusCode(); // 200
            $response->getHeaderLine('content-type'); // 'application/json; charset=utf8'
            $result = json_decode($response->getBody()); // '{"id": 1420053, "name": "guzzle", ...}'
            
            
            if ($result->result) {
                $fields = $result->result->fields;
                $records = $result->result->records;

                if (!Schema::hasTable($db_table)) {
                    Schema::create($db_table, function($table) use($fields)
                    {
                        $GLOBALS['exchange_list'] = array();
                        $GLOBALS['changed_list'] = array();
                        foreach ($fields as $key => $field) {
                            $mystring = $field->id;
                            $findme   = '.';
                            $pos = strpos($mystring, $findme);

                            if ($pos === false) {
                                $value = $mystring;
                            }
                            else {
                                $value = str_replace(".", "-", $mystring);
                                
                                array_push($GLOBALS['exchange_list'],json_encode($mystring));
                                array_push($GLOBALS['changed_list'],json_encode($value));
                            }
                            switch ($field->type) {
                                case 'int':
                                    $table->integer($value)->nullable();
                                    break;
                                case 'text':
                                    $table->text($value)->nullable();
                                    break;
                                default:
                                    $table->text($value)->nullable();
                                    break;
                            }
                        }
                    });
                }

                foreach ($records as $key => $record) {
                    $record_str = json_encode($record);
                    
                    if (count($GLOBALS['exchange_list']) > 0) {
                        for ($i=0; $i < count($GLOBALS['exchange_list']); $i++) {
                            $exchange = $GLOBALS['exchange_list'][$i];
                            $changed = $GLOBALS['changed_list'][$i];
                            $record_str = str_replace($exchange, $changed , $record_str);
                        }
                    }
                    
                    $resArr = json_decode($record_str, true); 
                    DB::table($db_table)->insert($resArr);
                }
                $url = 'https://data.gov.il' . $result->result->_links->next;
            }
        // } while (false);
        } while (count($records) > 0);

        return response()->json(['success'=>'Data is successfully added']);
    }

    public function getDatasets(Request $request, $datasets)
    {
        $db_table = "corporation_" . $datasets;
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        
        if (Schema::hasTable($db_table)) {
            $fields = Schema::getColumnListing($db_table);
            
            // Total records
            $totalRecords = DB::table($db_table)->select('count(*) as allcount')->count();
            $totalRecordswithFilter = DB::table($db_table)
                ->select('count(*) as allcount')
                ->where(function ($query) use($searchValue, $fields) {
                    for ($i = 0; $i < count($fields); $i++){
                    $query->orwhere($fields[$i], 'like',  '%' . $searchValue .'%');
                    }      
                })
                ->count();

            // Fetch records
            $records = DB::table($db_table)->orderBy($columnName,$columnSortOrder)
                ->where(function ($query) use($searchValue, $fields) {
                    for ($i = 0; $i < count($fields); $i++){
                    $query->orwhere($fields[$i], 'like',  '%' . $searchValue .'%');
                    }      
                })
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->get();

            $sno = $start+1;

            $response = array(
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordswithFilter,
                "aaData" => $records
            ); 

            echo json_encode($response);
        }
        else {
            $response = array(
                "draw" => intval($draw),
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => []
            ); 
            echo json_encode($response);
        }
        exit;
    }
}
