<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use Schema;
use Madzipper;
use Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class AddressController extends Controller
{
    public function index()
    {
        $fields = ["_id", "building1", "building2", "building3", "building4", "building5", "building6", "building7", "building8", "building9", "building10", "building11", "code1", "code2", "code3", "code4", "name1", "name2", "name3", "tax1", "tax2", "sign1", "sign2", "block", "smooth", "subdivision", "description", "type", "district", "site", "court", "area", "date", "year", "table", "location", "status" ];
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
        $table_fields = ["_id", "building1", "building2", "building3", "building4", "building5", "building6", "building7", "building8", "building9", "building10", "building11", "code1", "code2", "code3", "code4", "name1", "name2", "name3", "tax1", "tax2", "sign1", "sign2", "block", "smooth", "subdivision", "description", "type", "district", "site", "court", "area", "date", "year", "table", "location", "status" ];
        
        $db_tables = ["address_tabu_asset", "address_hitkadmuthabnia", "address_321", "address_israel-streets-synom"];
        $count_arr = [0, 0, 0, 0];
        $filter_arr = [0, 0, 0, 0];
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

        $temp_start = $start;
        $temp_rowperpage = $rowperpage;
        $data = array();

        $record_flag = false;
        
        foreach ($db_tables as $key => $db_table) {
            if (Schema::hasTable($db_table)) {
                $fields = [];
                switch ($db_table) {
                    case 'address_tabu_asset':
                        $fields = [
                            "_id" => "_id",
                            "block" => "גוש",
                            "smooth" => "חלקה",
                            "subdivision" => "תת חלקה",
                            "description" => "תיאור שיטה",
                            "type" => "סוג בעלות",
                        ];
                        break;
                    case 'address_hitkadmuthabnia':
                        $fields = [
                            "_id" => "_id",
                            "district" => "מחוז",
                            "tax1" => "ישוב למס",
                            "site" => "אתר",
                            "tax2" => "מס- מתחם",
                            "name1" => "שם מתחם",
                            "court" => "מגרש",
                            "block" => "גוש",
                            "smooth" => "חלקה",
                            "building1" => "מספר בניין",
                            "building2" => "קומות בניין",
                            "building3" => "יחידות דיור בניין",
                            "area" => "שטח",
                            "description" => "תאור שיטת שיווק",
                            "date" => "תאריך קובע",
                            "year" => "שנת חוזה",
                            "building4" => "שלב 5 בניין",
                            "building5" => "שלב 7 בניין",
                            "building6" => "שלב 8 בניין",
                            "building7" => "שלב 16 בניין",
                            "building8" => "שלב 18 בניין",
                            "building9" => "שלב 29 בניין",
                            "building10" => "שלב 39 בניין",
                            "building11" => "שלב 42 בניין",
                        ];
                        break;
                    case 'address_321':
                        $fields = [
                            "_id" => "_id",
                            "table" => "טבלה",
                            "city" => "סמל_ישוב",
                            "location" => "שם_ישוב",
                            "street" => "סמל_רחוב",
                            "name1" => "שם_רחוב",
                        ];
                        break;
                    case 'address_israel-streets-synom':
                        $fields = [
                            "_id" => "_id",
                            "code1" => "region_code",
                            "name1" => "region_name",
                            "code2" => "city_code",
                            "name2" => "city_name",
                            "code3" => "street_code",
                            "name3" => "street_name",
                            "status" => "street_name_status",
                            "code4" => "official_code",
                        ];
                        break;
                    
                    default:
                        
                        break;
                }
                
                // Total records
                $count_arr[$key] = DB::table($db_table)->select('count(*) as allcount')->count();

                $filter_arr[$key] = DB::table($db_table)
                    ->select('count(*) as allcount')
                    ->where(function ($query) use($searchValue, $fields) {
                        foreach ($fields as $key => $field) {
                            $query->orwhere($field, 'like',  '%' . $searchValue .'%');
                        }      
                    })
                    ->where(function ($query) use($fields, $columnName_arr, $table_fields) {
                        foreach ($fields as $key => $field) {
                            $i = array_search($key, $table_fields);
                            if ($columnName_arr[$i]['search']['value'] != "") {
                                $query->where($field, 'like',  '%' . $columnName_arr[$i]['search']['value'] .'%');
                            }
                        } 
                    })
                    ->count();
                
                $temp_start = $temp_start - $count_arr[$key];
                if ($temp_start < 0 && !$record_flag) {
                    if ($temp_start + $temp_rowperpage <= 0) {
                        // Fetch records
                        $records = DB::table($db_table)->orderBy($columnName,$columnSortOrder)
                        ->where(function ($query) use($searchValue, $fields) {
                            foreach ($fields as $key => $field) {
                                $query->orwhere($field, 'like',  '%' . $searchValue .'%');
                            }      
                        })
                        ->where(function ($query) use($fields, $columnName_arr, $table_fields) {
                            foreach ($fields as $key => $field) {
                                $i = array_search($key, $table_fields);
                                if ($columnName_arr[$i]['search']['value'] != "") {
                                    $query->where($field, 'like',  '%' . $columnName_arr[$i]['search']['value'] .'%');
                                }
                            } 
                        })
                        ->select('*')
                        ->skip($temp_start + $count_arr[$key])
                        ->take($temp_rowperpage)
                        ->get();

                        foreach ($records as $key => $record) {
                            $table_values = ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "" ];
                            foreach ($fields as $key => $field) {
                                $i = array_search($key, $table_fields);
                                $table_values[$i] = $record->$field;
                            }
                            $data[] = array_combine($table_fields, $table_values);
                        }

                        $record_flag = true;
                    }
                    else {
                        $records = DB::table($db_table)->orderBy($columnName,$columnSortOrder)
                        ->where(function ($query) use($searchValue, $fields) {
                            foreach ($fields as $key => $field) {
                                $query->orwhere($field, 'like',  '%' . $searchValue .'%');
                            }      
                        })
                        ->where(function ($query) use($fields, $columnName_arr, $table_fields) {
                            foreach ($fields as $key => $field) {
                                $i = array_search($key, $table_fields);
                                if ($columnName_arr[$i]['search']['value'] != "") {
                                    $query->where($field, 'like',  '%' . $columnName_arr[$i]['search']['value'] .'%');
                                }
                            } 
                        })
                        ->select('*')
                        ->skip($temp_start + $count_arr[$key])
                        ->take($temp_rowperpage)
                        ->get();

                        foreach ($records as $key => $record) {
                            $table_values = ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "" ];
                            foreach ($fields as $key => $field) {
                                $i = array_search($key, $table_fields);
                                $table_values[$i] = $record->$field;
                            }
                            $data[] = array_combine($table_fields, $table_values);
                        }
                    }
                }
            }
        }

        

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => array_sum($count_arr),
            "iTotalDisplayRecords" => array_sum($filter_arr),
            "aaData" => $data
        ); 
        echo json_encode($response);
        
        exit;
    }

    
    public function csvUpload(Request $request, $datasets)
    {
        $db_table = "address_" . $datasets;
        Schema::dropIfExists($db_table);

        $file = $request->file('csv');

        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        // Valid File Extensions
        $valid_extension = array("csv");

        // 200MB in Bytes
        $maxFileSize = 209715200; 

        Storage::disk('public')->putFileAs("uploads", $file, $filename);
        
        // Check file extension
        if(in_array(strtolower($extension),$valid_extension)){
            if ($request->has('header')) {
                $data = Excel::load($tempPath, function($reader) {})->get()->toArray();
            } else {
                $data = array_map('str_getcsv', file($tempPath));
            }
        }

        if (count($data) > 0) {
            $fields = $data[0];
            $keyArr = array();
            $key_remove_count = 0;
            if (!Schema::hasTable($db_table)) {
                Schema::create($db_table, function($table) use($fields)
                {
                    global $keyArr, $key_remove_count;
                    $keyArr[] = "_id";
                    $GLOBALS['exchange_list'] = array();
                    $GLOBALS['changed_list'] = array();
                    $table->integer("_id")->nullable();
                    foreach ($fields as $key => $field) {
                        $mystring = $field;
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
                        if ($field != '') {
                            $table->text(trim($value))->nullable();
                            $keyArr[] = trim($value);
                        } else {
                            $key_remove_count ++;
                        }
                        
                    }
                });
            }
    
            foreach ($data as $key => $record) {
                if ($key != 0) {
                    global $keyArr, $key_remove_count;
                    $record = array_map('utf8_encode', $record);
                    $record_str = json_encode($record, JSON_UNESCAPED_UNICODE);
                
                    if (count($GLOBALS['exchange_list']) > 0) {
                        for ($i=0; $i < count($GLOBALS['exchange_list']); $i++) {
                            $exchange = $GLOBALS['exchange_list'][$i];
                            $changed = $GLOBALS['changed_list'][$i];
                            $record_str = str_replace($exchange, $changed , $record_str);
                        }
                    }

                    $resArr = json_decode($record_str, true); 
                    $resArr = array_map('utf8_decode', $resArr);
                    
                    array_unshift($resArr, $key);
                    for ($i=0; $i < $key_remove_count; $i++) { 
                        array_pop($resArr);
                    }
                    $insertArr = array_combine($keyArr, $resArr);
                    DB::table($db_table)->insert($insertArr);
                }
            }
        }
        return redirect()->back();
    }

    public function csvDownload(Request $request, $datasets)
    {
        $db_table = "address_" . $datasets;
        $users = DB::table($db_table)->get();
        return (new FastExcel($users))->download('datatable_address.csv');
    }
}
