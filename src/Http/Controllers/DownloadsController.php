<?php

namespace NovaKit\NovaOnVapor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class DownloadsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function __invoke(Request $request)
    {
        abort_unless($request->filled(['disk', 'filename']), 404);

        // Dispatch deleting file after download.

        return Storage::disk($request->input('disk'))->download($request->input('filename'));
    }
}
