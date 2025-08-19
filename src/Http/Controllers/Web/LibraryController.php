<?php

namespace Hetbo\Shelf\Http\Controllers\Web;

use Illuminate\Routing\Controller;

class LibraryController extends Controller {

    public function index()
    {
        return view('shelf::library.index');
    }
    
}