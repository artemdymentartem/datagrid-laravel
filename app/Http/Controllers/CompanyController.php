<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://data.gov.il/api/3/action/datastore_search?resource_id=be5b7935-3922-45d4-9638-08871b17ec95&limit=100');

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
        switch ($datasets) {
            case 'gsa':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=2d5abbad-4809-4900-b74f-b2f8b40bcfb8&limit=100";
                break;
            
            case 'ica_companies':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=f004176c-b85f-4542-8901-7b3176f9a054&limit=100";
                break;
            
            case 'ica_partnerships':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=139aa193-fabb-4f6b-a71b-0bb40fd73eb2&limit=100";
                break;
                
            case 'membership-in-liquidation':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=6f3f0df3-5968-4135-81c5-8dd76bf89410&limit=100";
                break;

            case 'moj-amutot1':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=be5b7935-3922-45d4-9638-08871b17ec95&limit=100";
                break;
                
            case 'moj-amutot2':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=85e40960-5426-4f4c-874f-2d1ec1b94609&limit=100";
                break;
                
            case 'pr2018':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=d8715392-287f-49b7-9ae3-f21ec5bf55f3&limit=100";
                break;
                
            case 'pinkashakablanim':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=4eb61bd6-18cf-4e7c-9f9c-e166dfa0a2d8&limit=100";
                break;

            case 'ica-changes':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=100";
                break;

            case 'limit':
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=100";
                break;

            default:
                $url = "https://data.gov.il/api/3/action/datastore_search?resource_id=28780ab5-3ef1-44c7-8377-da82c0aa6781&limit=100";
                break;
        }
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
