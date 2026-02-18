<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    /**
     * Display the documentation page
     */
    public function index()
    {
        return view('documentation.index');
    }
}
