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
        $fields = ["_id", "num1", "num2", "num3", "num4", "num5", "date1", "date2", "date3", "date4", "name1", "name2", "name3", "name4", "address1", "address2", "address3", "address4", "address5", "address6", "address7", "address8", "address9", "address10", "activity1", "activity2", "activity3", "activity4", "status1", "status2", "report1", "report2", "report3", "report4", "goal1", "goal2", "occupation", "office", "type", "description", "purpose", "boolean", "limit", "fertilizer", "postal code", "T-D-", "country", "at", "settlement", "turnover", "association", "liquidation", "reason", "district", "phone", "email", "group", "classification", "contractor", "note"];
        $tab_name = "תאגיד";
        $table_name = "Main Datatable";
        $link = "https://data.gov.il/dataset/pr2018/resource/2156937e-524a-4511-907d-5470a6a5264f";
        $tab_en = "corporation";
        

        return view("dashboard", compact("fields", "table_name", "link", "tab_name", "tab_en"));
    }

    public function datasets($datasets)
    {
        $db_table = "corporation_" . $datasets;
        $tab_name = "תאגיד";

        $records = [];
        $fields = [];
        
        switch ($datasets) {
            case 'gsa':
                $link = "https://data.gov.il/dataset/gsa/resource/2d5abbad-4809-4900-b74f-b2f8b40bcfb8";
                $table_name = "רשימת חברות ממשלתיות";
                break;
            
            case 'ica_companies':
                $link = "https://data.gov.il/dataset/ica_companies/resource/f004176c-b85f-4542-8901-7b3176f9a054";
                $table_name = "מאגר חברות - רשם החברות";
                break;
            
            case 'ica_partnerships':
                $link = "https://data.gov.il/dataset/ica_partnerships/resource/139aa193-fabb-4f6b-a71b-0bb40fd73eb2";
                $table_name = "רשימת השותפויות";
                break;
                
            case 'membership-in-liquidation':
                $link = "https://data.gov.il/dataset/membership-in-liquidation/resource/6f3f0df3-5968-4135-81c5-8dd76bf89410";
                $table_name = "חברות בפרוק מרצון בהליך מזורז";
                break;

            case 'moj-amutot1':
                $link = "https://data.gov.il/dataset/moj-amutot/resource/be5b7935-3922-45d4-9638-08871b17ec95";
                $table_name = "מאגר עמותות לתועלת הציבור";
                break;
                
            case 'moj-amutot2':
                $link = "https://data.gov.il/dataset/moj-amutot/resource/85e40960-5426-4f4c-874f-2d1ec1b94609";
                $table_name = "מאגר חברות לתועלת הציבור";
                break;
                
            case 'pr2018':
                $link = "https://data.gov.il/dataset/pr2018/resource/d8715392-287f-49b7-9ae3-f21ec5bf55f3";
                $table_name = "הכונס הרשמי  חברות";
                break;
                
            case 'pinkashakablanim':
                $link = "https://data.gov.il/dataset/pinkashakablanim/resource/4eb61bd6-18cf-4e7c-9f9c-e166dfa0a2d8";
                $table_name = "קבלנים רשומים להוציא את ";
                break;

            case 'ica-changes':
                $link = "https://data.gov.il/dataset/ica-changes/resource/28780ab5-3ef1-44c7-8377-da82c0aa6781";
                $table_name = "פרטי שינויים במאגר חברות חברות";
                break;

            case 'limit':
                $link = "https://data.gov.il/dataset/ica-changes/resource/28780ab5-3ef1-44c7-8377-da82c0aa6781";
                $table_name = "תאגידים מוגבלים";
                break;

            default:
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

    public function getMainDatasets(Request $request)
    {
        $table_fields = ["_id", "num1", "num2", "num3", "num4", "num5", "date1", "date2", "date3", "date4", "name1", "name2", "name3", "name4", "address1", "address2", "address3", "address4", "address5", "address6", "address7", "address8", "address9", "address10", "activity1", "activity2", "activity3", "activity4", "status1", "status2", "report1", "report2", "report3", "report4", "goal1", "goal2", "occupation", "office", "type", "description", "purpose", "boolean", "limit", "fertilizer", "postal code", "T-D-", "country", "at", "settlement", "turnover", "association", "liquidation", "reason", "district", "phone", "email", "group", "classification", "contractor", "note"];
        
        $db_tables = ["corporation_gsa", "corporation_ica_companies", "corporation_ica_partnerships", "corporation_membership-in-liquidation", "corporation_moj-amutot1", "corporation_moj-amutot2", "corporation_pr2018", "corporation_pinkashakablanim", "corporation_ica-changes", "corporation_limit"];
        $count_arr = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $filter_arr = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
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
                    case 'corporation_gsa':
                        $fields = [
                            "_id" => "_id",
                            "name1" => "COMPANY_NAME",
                            "name2" => "COMPANY_SITE",
                            "occupation" => "PRIMARY_OCCUPATION",
                            "status1" => "STATUS",
                            "office" => "GOVERMENT_OFFICE"
                        ];
                        break;

                    case 'corporation_ica_companies':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר חברה",
                            "name1" => "שם חברה",
                            "name2" => "שם באנגלית",
                            "type" => "סוג תאגיד",
                            "status1" => "סטטוס חברה",
                            "description" => "תאור חברה",
                            "purpose" => "מטרת החברה",
                            "date1" => "תאריך התאגדות",
                            "boolean" => "חברה ממשלתית",
                            "limit" => "מגבלות",
                            "fertilizer" => "מפרה",
                            "num2" => "שנה אחרונה של דוח שנתי (שהוגש)",
                            "name3" => "שם עיר",
                            "name4" => "שם רחוב",
                            "num3" => "מספר בית",
                            "postal code" => "מיקוד",
                            "T-D-" => "ת-ד-",
                            "country" => "מדינה",
                            "at" => "אצל",
                            "status2" => "תת סטטוס",
                        ];
                        break;

                    case 'corporation_ica_partnerships':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר שותפות",
                            "name1" => "שם שותפות",
                            "name2" => "שם באנגלית",
                            "type" => "סוג תאגיד",
                            "status1" => "סטטוס תאגיד",
                            "date1" => "תאריך התאגדות",
                            "settlement" => "ישוב",
                            "name3" => "רחוב",
                            "num2" => "מספר בית",
                            "postal code" => "מיקוד",
                            "T-D-" => "ת-ד",
                            "country" => "מדינה",
                            "at" => "אצל",
                        ];
                        break;
                        
                    case 'corporation_membership-in-liquidation':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר חברה",
                            "name1" => "שם חברה",
                            "name2" => "שם באנגלית",
                            "type" => "סוג תאגיד",
                            "status1" => "סטטוס חברה",
                            "date1" => "אישור בקשת הפירוק",
                            "date2" => "מועד החיסול הצפוי",
                            "status2" => "תת סטטוס",
                            "fertilizer" => "מפרה",
                            "name3" => "שם עיר",
                            "name3" => "שם רחוב",
                            "num2" => "מספר בית",
                            "postal code" => "מיקוד",
                            "T-D-" => "ת-ד-",
                            "country" => "מדינה",
                            "at" => "אצל",
                        ];
                        break;

                    case 'corporation_moj-amutot1':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר עמותה",
                            "date1" => "תאריך רישום עמותה",
                            "name1" => "שם עמותה בעברית",
                            "name2" => "שם עמותה באנגלית",
                            "status1" => "סטטוס עמותה",
                            "activity1" => "סיווג פעילות ענפי",
                            "activity2" => "תחום פעילות משני",
                            "report1" => "שנת דיווח דוח כספי אחרון",
                            "turnover" => "מחזור כספי",
                            "num2" => "כמות מתנדבים",
                            "num3" => "כמות עובדים",
                            "activity3" => "איזורי פעילות",
                            "activity4" => "מקומות פעילות",
                            "date2" => "תאריך עדכון אחרון של נתוני עמותה",
                            "name3" => "שם אגודה עותומנית",
                            "address1" => "כתובת - ישוב",
                            "address2" => "כתובת - רחוב",
                            "address3" => "כתובת - מספר דירה",
                            "address4" => "כתובת - מיקוד",
                            "address5" => "מען - ישוב",
                            "address6" => "מען - רחוב",
                            "address7" => "מען - מספר דירה",
                            "address8" => "מען - מיקוד",
                            "address9" => "מען - מיקוד תיבת דואר",
                            "address10" => "מען - תיבת דואר",
                            "date3" => "תאריך התחלה תוקף מטרות עמותה",
                            "association" => "מטרות עמותה"
                        ];
                        break;

                    case 'corporation_moj-amutot2':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר חל~צ",
                            "date1" => "תאריך רישום חל~צ",
                            "name1" => "שם חל~צ בעברית",
                            "name2" => "שם חל~צ באנגלית",
                            "status1" => "סטטוס חל~צ",
                            "goal1" => "מטרות ארגון רשמיות",
                            "activity1" => "סיווג פעילות ענפי",
                            "activity2" => "תחום פעילות",
                            "report1" => "שנת דיווח דוח כספי אחרון",
                            "report2" => "מחזור - נתונים מדוח מילולי מקוון",
                            "report3" => "מתנדבים- נתונים מדוח מילולי מקוון",
                            "report4" => "עובדים- נתונים מדוח מילולי מקוון",
                            "activity3" => "איזורי פעילות",
                            "date2" => "תאריך עדכון אחרון של נתוני חל~צ",
                            "address1" => "כתובת - ישוב",
                            "address2" => "כתובת - רחוב",
                            "address3" => "כתובת - מספר דירה",
                            "address4" => "כתובת - מיקוד",
                            "address5" => "מען - ישוב",
                            "address6" => "מען - רחוב",
                            "address7" => "מען - מספר דירה",
                            "address8" => "מען - מיקוד",
                            "address9" => "מען - מיקוד תיבת דואר",
                            "address10" => "מען - תיבת דואר",
                            "goal2" => "תאריך התחלה תוקף מטרות עמותה",
                            "date3" => "תאריך ביקורת עומק אחרונה",
                        ];
                        break;

                    case 'corporation_pr2018':
                        $fields = [
                            "_id" => "_id",
                            "liquidation" => "מזהה תיק פירוק חברה",
                            "activity1" => "עיר פעילות חברה",
                            "status1" => "סטטוס תיק",
                            "date1" => "תאריך הגשת הבקשה",
                            "date2" => "תאריך קבלת צו פירוק",
                            "date3" => "תאריך ביטול / הקפאת צו פירוק",
                            "date4" => "תאריך סגירת תיק",
                            "reason" => "סיבת סגירה",
                            "district" => "בית משפט מחוזי בו מתנהל התיק",
                            "name1" => "שם החברה",
                            "num1" => "מספר זיהוי של החברה",
                        ];
                        break;

                    case 'corporation_pinkashakablanim':
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
                            "note" => "הערה",
                        ];
                        break;

                    case 'corporation_ica-changes':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר תאגיד",
                            "name1" => "שם תאגיד",
                            "type" => "סוג בקשה",
                            "date1" => "תאריך עדכון סטטוס",
                        ];
                        break;

                    case 'corporation_limit':
                        $fields = [
                            "_id" => "_id",
                            "num1" => "מספר תאגיד",
                            "name1" => "שם תאגיד",
                            "type" => "סוג בקשה",
                            "date1" => "תאריך עדכון סטטוס",
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
                            $table_values = ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""];
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
                            $table_values = ["", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""];
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
}
