<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use Schema;
use Madzipper;
use Storage;

class AddressController extends Controller
{
    public function index()
    {
        $fields = ["_id", "field1","field2","field3","field4","field5","field6","field7","field8","field9","field10","field11","field12","field13","field14","field15","field16","field17","field18","field19","field20","field21","field22","field23"];
        $tab_name = "גושים וחלקות  וכתובת";
        $table_name = "Main Datatable";
        $link = "https://data.gov.il/dataset/pr2018/resource/2156937e-524a-4511-907d-5470a6a5264f";
        $tab_en = "address";
        

        return view("dashboard", compact("fields", "table_name", "link", "tab_name", "tab_en"));
    }

    public function datasets($datasets)
    {
        $db_table = "address_" . $datasets;
        $tab_name = "גושים וחלקות  וכתובת";

        $records = [];
        $fields = [];
        
        switch ($datasets) {
            case 'tabu_asset':
                $link = "https://data.gov.il/dataset/tabu_asset/resource/a1a91496-d692-4420-bc21-3487600b71a5";
                $table_name = "שימת הנכסים בפנקסי המקרקעין על פי סוג בעלות";
                break;
            
            case 'hitkadmuthabnia':
                $link = "https://data.gov.il/dataset/hitkadmuthabnia/resource/1ec45809-5927-430a-9b30-77f77f528ce3";
                $table_name = "תקדמות בניה";
                break;
            
            case '321':
                $link = "https://data.gov.il/dataset/321/resource/a7296d1a-f8c9-4b70-96c2-6ebb4352f8e3";
                $table_name = "רשימת השותפויות";
                break;
                
            case '826':
                $link = "https://data.gov.il/dataset/826/resource/15b92ae9-9371-4c34-99bb-4a26c5087f65";
                $table_name = "מטה דאטה על שכבות";
                break;

            case 'israel-streets-synom':
                $link = "https://data.gov.il/dataset/israel-streets-synom/resource/bf185c7f-1a4e-4662-88c5-fa118a244bda";
                $table_name = "רשימת רחובות בישראל + סינומים";
                break;

            default:
                $link = "https://data.gov.il/dataset/tabu_asset/resource/a1a91496-d692-4420-bc21-3487600b71a5";
                $table_name = "שימת הנכסים בפנקסי המקרקעין על פי סוג בעלות";
                break;
        }

        if (Schema::hasTable($db_table)) {
            // $records = DB::table($db_table)->get();
            $fields = Schema::getColumnListing($db_table);
        }
        else {
            $fields=["default"];
        }

        $tab_en = "address";

        return view("datatable", compact("fields", "table_name", "link", "tab_name", "datasets", "tab_en"));
    }

    public function reload($datasets)
    {
        $db_table = "address_" . $datasets;
        Schema::dropIfExists($db_table);
        
        switch ($datasets) {
            case 'tabu_asset':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=a1a91496-d692-4420-bc21-3487600b71a5&limit=1000";
                $table_name = "שימת הנכסים בפנקסי המקרקעין על פי סוג בעלות";
                break;
            
            case 'hitkadmuthabnia':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=1ec45809-5927-430a-9b30-77f77f528ce3&limit=1000";
                $table_name = "תקדמות בניה";
                break;
            
            case '321':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=a7296d1a-f8c9-4b70-96c2-6ebb4352f8e3&limit=1000";
                $table_name = "רשימת השותפויות";
                break;
                
            case '826':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=15b92ae9-9371-4c34-99bb-4a26c5087f65&limit=1000";
                $table_name = "מטה דאטה על שכבות";
                break;

            case 'israel-streets-synom':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=bf185c7f-1a4e-4662-88c5-fa118a244bda&limit=1000";
                $table_name = "רשימת רחובות בישראל + סינומים";
                break;

            default:
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=a1a91496-d692-4420-bc21-3487600b71a5&limit=1000";
                $table_name = "שימת הנכסים בפנקסי המקרקעין על פי סוג בעלות";
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
        $db_table = "address_" . $datasets;
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

    public function getMainDatasets(Request $request)
    {
        $table_fields = ["_id", "field1","field2","field3","field4","field5","field6","field7","field8","field9","field10","field11","field12","field13","field14","field15","field16","field17","field18","field19","field20","field21","field22","field23"];
        
        $db_tables = ["address_tabu_asset","address_hitkadmuthabnia","address_321","address_israel-streets-synom"];
        $db_table = "address_tabu_asset";
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
        
        // foreach ($db_tables as $key => $db_table) {
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
                    ->where(function ($query) use($fields, $columnName_arr) {
                        for ($i = 0; $i < count($fields); $i++){
                            if ($columnName_arr[$i]['search']['value'] != "") {
                                $query->where($fields[$i], 'like',  '%' . $columnName_arr[$i]['search']['value'] .'%');
                            }
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
                    ->where(function ($query) use($fields, $columnName_arr) {
                        for ($i = 0; $i < count($fields); $i++){
                            if ($columnName_arr[$i]['search']['value'] != "") {
                                $query->where($fields[$i], 'like',  '%' . $columnName_arr[$i]['search']['value'] .'%');
                            }
                        }  
                    })
                    ->select('*')
                    ->skip($start)
                    ->take($rowperpage)
                    ->get();
    
                $sno = $start+1;
                $data = array();
                foreach ($records as $key => $record) {
                    $table_values = ["", "","","","","","","","","","","","","","","","","","","","","","",""];
                    for ($i = 0; $i < count($fields); $i++){
                        $index = $fields[$i];
                        $table_values[$i] = $record->$index;
                    }
                    $data[] = array_combine($table_fields, $table_values);
                }
    
                $response = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecords,
                    "iTotalDisplayRecords" => $totalRecordswithFilter,
                    "aaData" => $data
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
        // }
        
        exit;
    }

    public function zip(Request $request)
    {
        // $zipper = new \Madnest\Madzipper\Madzipper;

        $url = 'https://data.gov.il/dataset/sheetkshape/resource/e4beff76-0913-464f-8e6c-4162e5fec9a3/download/sheet_k.zip';
        $name = substr($url, strrpos($url, '/') + 1);

        if(file_put_contents( $name,htmlspecialchars(file_get_contents($url)))) {
            echo "File downloaded successfully";
        }
        else {
            echo "File downloading failed.";
        }
        // $name = substr($url, strrpos($url, '/') + 1);
        
        // $curl = curl_init();
        // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/octet-stream'));
        // curl_setopt($curl, CURLOPT_URL, $url);
        // curl_setopt($curl, CURLOPT_FILETIME, true);
        // // curl_setopt($curl, CURLOPT_NOBODY, true);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_HEADER, true);
        // $header = curl_exec($curl);
        // $info = curl_getinfo($curl);
        // curl_close($curl);

        // file_put_contents($name, $header);
        // dd($header);


        // // Storage::put($name, $tempImage);
        // // $filedata = file_get_contents($url);

        // // Storage::put($name, $output);

        // // // $zipper->make('https://data.gov.il/dataset/sheetkshape/resource/e4beff76-0913-464f-8e6c-4162e5fec9a3/download/sheet_k.zip')->extractTo('foo');
        // // $zipper->close();
        // // return response()->download($url,'Download.zip');
    }
}
