<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use Schema;

class AbandonController extends Controller
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
        $db_table = "abandon_" . $datasets;
        $tab_name = "גושים וחלקות  וכתובת";

        $records = [];
        $fields = [];
        
        switch ($datasets) {
            case 'ezvonot2018':
                $link = "https://data.gov.il/dataset/ezvonot2018/resource/4dc59d4d-26ea-49c6-b83c-b019addc6ec9";
                $table_name = "עזבונות לטובת המדינה";
                break;
            
            default:
                $link = "https://data.gov.il/dataset/ezvonot2018/resource/4dc59d4d-26ea-49c6-b83c-b019addc6ec9";
                $table_name = "עזבונות לטובת המדינה";
                break;
        }

        if (Schema::hasTable($db_table)) {
            // $records = DB::table($db_table)->get();
            $fields = Schema::getColumnListing($db_table);
        }
        else {
            $fields=["default"];
        }

        $tab_en = "abandon";

        return view("datatable", compact("fields", "table_name", "link", "tab_name", "datasets", "tab_en"));
    }

    public function reload($datasets)
    {
        $db_table = "abandon_" . $datasets;
        Schema::dropIfExists($db_table);
        
        switch ($datasets) {
            case 'ezvonot2018':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=4dc59d4d-26ea-49c6-b83c-b019addc6ec9&limit=1000";
                $table_name = "עזבונות לטובת המדינה";
                break;
            
            default:
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=4dc59d4d-26ea-49c6-b83c-b019addc6ec9&limit=1000";
                $table_name = "עזבונות לטובת המדינה";
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
        $db_table = "abandon_" . $datasets;
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
