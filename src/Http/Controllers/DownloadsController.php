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
        abort_unless($request->filled(['filename']), 404);

        $disk = $request->input('disk');
        $filename = $request->input('filename');

        if ($request->boolean('deleteFileAfterSend') === true) {
            dispatch(function () use ($disk, $filename) {
                Storage::disk($disk)->delete($filename);
            })->onConnection(
                config('nova-on-vapor.actions.queues.connection')
            )->onQueue(
                config('nova-on-vapor.actions.queues.queue')
            )->afterResponse();
        }

        return Storage::disk($disk)->download($filename);
    }
}
