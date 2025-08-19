<?php

namespace Hetbo\Shelf\Http\Controllers\Web;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class AssetController extends Controller {

    public function js()
    {
        $path = __DIR__ . '/../../../../dist/main.js';
        if (!File::exists($path)) { abort(404); }

        return new Response(File::get($path), 200,
            [
                'Content-Type' => 'application/javascript',
                'Cache-Control' => 'public, max-age=31536000',
            ]);

    }

    // Add this method to the existing AssetController
    public function css()
    {
        $path = __DIR__.'/../../../../dist/main.css';
        if (!File::exists($path)) { abort(404); }
        return new Response(File::get($path), 200, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }

}