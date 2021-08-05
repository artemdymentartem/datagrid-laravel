<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use Schema;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Storage;
use File;

class PersonController extends Controller
{
    public function index()
    {
        $fields = ["_id", "num1", "num2", "num3", "num4", "date1", "date2", "date3", "date4", "date5", "date6", "date7", "date8", "date9", "date10", "name1", "name2", "name3", "name4", "status", "reason", "amount", "balance", "boolean", "there", "settlement", "language", "type", "district", "decision", "phone", "email", "description", "group", "classificaiton", "volume", "contractor", "note"];
        $tab_name = "אדם";
        $table_name = "Main Datatable";
        $tab_en = "person";
        

        return view("dashboard", compact("fields", "table_name", "tab_name", "tab_en"));
    }

    public function datasets($datasets)
    {
        $db_table = "person_" . $datasets;
        $tab_name = "אדם";

        $records = [];
        $fields = [];
        
        switch ($datasets) {
            case 'pr2018':
                $link = "https://data.gov.il/dataset/pr2018/resource/2156937e-524a-4511-907d-5470a6a5264f";
                $table_name = "הכונס הרשמי  פרטיים";
                break;
            case 'notary':
                $link = "https://data.gov.il/dataset/notary/resource/3ead5fae-3513-46f8-a458-959ea3e035ae";
                $table_name = "רשימת הנוטריונים";
                break;
            case 'yerusha':
                $link = "https://data.gov.il/dataset/yerusha/resource/7691b4a2-fe1d-44ec-9f1b-9f2f0a15381b";
                $table_name = "בקשות לרשם הירושות";
                break;    
            case 'pinkashakablanim':
                $link = "https://data.gov.il/dataset/pinkashakablanim/resource/4eb61bd6-18cf-4e7c-9f9c-e166dfa0a2d8";
                $table_name = "קבלנים רשומים";
                break; 
            case 'cpalist':
                $link = "https://data.gov.il/dataset/cpalist/resource/7e86def9-0899-45f4-8942-d6fe38482b1a";
                $table_name = "מרשם רואי חשבון";
                break; 
            default:
                $link = "https://data.gov.il/dataset/pr2018/resource/2156937e-524a-4511-907d-5470a6a5264f";
                $table_name = "הכונס הרשמי  פרטיים";
                break;
        }

        if (Schema::hasTable($db_table)) {
            // $records = DB::table($db_table)->get();
            $fields = Schema::getColumnListing($db_table);
        }
        else {
            $fields=["default"];
        }

        $tab_en = "person";

        return view("datatable", compact("fields", "table_name", "link", "tab_name", "datasets", "tab_en"));
    }

    public function reload($datasets)
    {
        $db_table = "person_" . $datasets;
        Schema::dropIfExists($db_table);
        
        switch ($datasets) {
            case 'pr2018':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=2156937e-524a-4511-907d-5470a6a5264f&limit=1000";
                $table_name = "הכונס הרשמי  פרטיים";
                break;
            case 'notary':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=3ead5fae-3513-46f8-a458-959ea3e035ae&limit=1000";
                $table_name = "רשימת הנוטריונים";
                break;
            case 'yerusha':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=7691b4a2-fe1d-44ec-9f1b-9f2f0a15381b&limit=1000";
                $table_name = "בקשות לרשם הירושות";
                break;    
            case 'pinkashakablanim':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=4eb61bd6-18cf-4e7c-9f9c-e166dfa0a2d8&limit=1000";
                $table_name = "קבלנים רשומים";
                break; 
            case 'cpalist':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=7e86def9-0899-45f4-8942-d6fe38482b1a&limit=1000";
                $table_name = "מרשם רואי חשבון";
                break;
            default:
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=2156937e-524a-4511-907d-5470a6a5264f&limit=1000";
                $table_name = "הכונס הרשמי  פרטיים";
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
        $db_table = "person_" . $datasets;
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
        $table_fields = ["_id", "num1", "num2", "num3", "num4", "date1", "date2", "date3", "date4", "date5", "date6", "date7", "date8", "date9", "date10", "name1", "name2", "name3", "name4", "status", "reason", "amount", "balance", "boolean", "there", "settlement", "language", "type", "district", "decision", "phone", "email", "description", "group", "classificaiton", "volume", "contractor", "note"];
        
        $db_tables = ["person_pr2018", "person_notary", "person_yerusha", "person_pinkashakablanim", "person_cpalist"];
        $count_arr = [0, 0, 0, 0, 0];
        $filter_arr = [0, 0, 0, 0, 0];
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
                    case 'person_pr2018':
                        $fields = [
                            "_id" => "_id",
                            "num1"=> "מספר רץ תיקים",
                            "name1"=> "עיר בה מתגורר החייב",
                            "name2"=> "שם בית משפט",
                            "status"=> "סטטוס תיק",
                            "date1"=> "תאריך פתיחת תיק",
                            "date2"=> "תאריך צו כינוס",
                            "date3"=> "תאריך צו פשיטת רגל",
                            "date4"=> "תאריך ביטול צו",
                            "reason"=> "סיבת ביטול צו",
                            "date5"=> "תאריך גזירת תיק",
                            "amount"=> "סכום הצו",
                            "balance"=> "יתרה בבנק",
                            "boolean"=> "האם בצו הכינוס נכלל סעיף 42",
                            "date6"=> "תאריך הגשת דוח על ידי מנהל מיוחד",
                            "date7"=> "תאריך הגשה של תכנית פרעון",
                            "date8"=> "תאריך מתוכנן להגשת דוח",
                            "date9"=> "תאריך מתוכנן להגשת תכנית פרעון",
                            "date10"=> "תאריך אישור דוח מסכם"
                        ];
                        break;
                    case 'person_notary':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר תיק רשיון",
                            "there" => "שם",
                            "settlement" => "ישוב",
                            "language" => "שפות"
                        ];
                        break;
                    case 'person_yerusha':
                        $fields = [
                            "_id" => "_id",
                            "date1" => "תאריך הגשת בקשה",
                            "type" => "סוג בקשה",
                            "district" => "מחוז",
                            "boolean" => "מיוצג",
                            "status" => "סטטוס בקשה",
                            "date2" => "תאריך פרסום",
                            "name1" => "עיתון מפרסם",
                            "decision" => "החלטת רשם",
                            "date3" => "תאריך החלטת רשם",
                            "reason" => "סיבת סגירה",
                            "date4" => "תאריך סגירה"
                        ];
                        break;
                    case 'person_pinkashakablanim':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר יישות",
                            "name1" => "שם יישות",
                            "num2" => "מספר קבלן",
                            "name2" => "שם יישוב",
                            "name3" => "שם רחוב",
                            "num3" => "מספר בית",
                            "date1" => "תאריך רישום",
                            "phone" => "מספר טלפון",
                            "email" => "דואר אלקטרוני",
                            "num4" => "מספר ענף",
                            "description" => "תיאור ענף",
                            "group" => "קבוצה",
                            "classification" => "סיווג",
                            "date2" => "סיווג מתאריך",
                            "num5" => "היקף מקסימאלי באלפי שח",
                            "contractor" => "קבלן מוכר",
                            "name4" => "עובדים בענף",
                            "note" => "הערה"
                        ];
                        break;
                    case 'person_cpalist':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר רישיון",
                            "date1" => "תאריך קבלת רישיון",
                            "name1" => "שם פרטי",
                            "name2" => "שם משפחה",
                            "settlement" => "ישוב",
                            "status" => "סטטוס"
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
                            $table_values = ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""];
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
                            $table_values = ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""];
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
        $db_table = "person_" . $datasets;
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

        $path = $file->store('uploads','public');
        
        // Check file extension
        if(in_array(strtolower($extension),$valid_extension)){
            if ($request->has('header')) {
                $data = Excel::load($tempPath, function($reader) {})->get()->toArray();
            } else {
                $data = array_map('str_getcsv', file(public_path('storage/'.$path)));
            }
        }

        if (count($data) > 0) {
            $fields = $data[0];
            $keyArr = array();
            if (!Schema::hasTable($db_table)) {
                Schema::create($db_table, function($table) use($fields)
                {
                    global $keyArr;
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
                        if ($field == '') {
                            $value = "empty";
                        }
                        $table->text(trim($value))->nullable();
                        $keyArr[] = trim($value);
                    }
                });
            }
    
            foreach ($data as $key => $record) {
                if ($key != 0) {
                    global $keyArr;
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
                    array_unshift($resArr, $key);
                    $insertArr = array_combine($keyArr, $resArr);
                    DB::table($db_table)->insert($insertArr);
                }
            }
        }
        return redirect()->back();
    }

    public function csvDownload(Request $request, $datasets)
    {
        $db_table = "person_" . $datasets;
        $users = DB::table($db_table)->get();
        return (new FastExcel($users))->download('datatable_person.csv');
    }
}
