<?php

namespace Modules\AdminAIConfiguration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminAIConfigurationController extends Controller
{
    public function index()
    {
        return view('adminaiconfiguration::index');
    }
}
