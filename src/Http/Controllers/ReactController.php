<?php

namespace Hetbo\Shelf\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ReactController extends Controller
{
    public function index()
    {
        return view('shelf::shelf');
    }

    public function getFiles()
    {
        $files = Storage::disk('public')->allFiles();
        return response()->json($files);
    }

    public function upload(Request $request)
    {
        $file = $request->file('file');
        $path = $file->store('uploads', 'public');
        return response()->json(['path' => $path]);
    }

    public function delete($file)
    {
        Storage::disk('public')->delete($file);
        return response()->json(['success' => true]);
    }
}