<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use Schema;
use Rap2hpoutre\FastExcel\FastExcel;

class AbandonController extends Controller
{
    public function index()
    {
        $fields = ["_id", "number", "date", "deposit", "amount", "deposit date", "currency", "Country", "country"];
        $tab_name = "גושים וחלקות  וכתובת";
        $table_name = "Main Datatable";
        $link = "https://data.gov.il/dataset/pr2018/resource/2156937e-524a-4511-907d-5470a6a5264f";
        $tab_en = "abandon";
        

        return view("dashboard", compact("fields", "table_name", "link", "tab_name", "tab_en"));
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

    public function getMainDatasets(Request $request)
    {
        $table_fields = ["_id", "number", "date", "deposit", "amount", "deposit date", "currency", "Country", "country"];
        
        $db_tables = ["abandon_ezvonot2018"];
        $count_arr = [0];
        $filter_arr = [0];
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
                    case 'abandon_ezvonot2018':
                        $fields = [
                            "_id" => "_id",
                            "number" => "מספר הפקדה",
                            "date" => "תאריך קבלה",
                            "deposit" => "מטבע מקור של ההפקדה",
                            "amount" => "סכום לאחר המרה לשקלים",
                            "deposit date" => "תאריך הפקדה",
                            "currency" => "סוג מטבע מקור",
                            "Country" => "מדינה",
                            "country" => "ארץ",
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
                            $table_values = ["", "", "", "", "", "", "", "", ""];
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
                            $table_values = ["", "", "", "", "", "", "", "", ""];
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
        $db_table = "abandon_" . $datasets;
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
        $db_table = "abandon_" . $datasets;
        $users = DB::table($db_table)->get();
        return (new FastExcel($users))->download('datatable_abandon.csv');
    }
}
