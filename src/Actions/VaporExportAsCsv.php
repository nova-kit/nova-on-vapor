<?php

namespace NovaKit\NovaOnVapor\Actions;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Actions\Response;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\ActionRequest;
use Rap2hpoutre\FastExcel\FastExcel;

class VaporExportAsCsv extends ExportAsCsv
{
    /**
     * Storage disk used to store the file.
     *
     * @var string|null
     */
    public $storageDisk;

    /**
     * Determine if file should be deleted after send.
     *
     * @var bool
     */
    public $deleteFileAfterSend = false;

    /**
     * Construct a new action instance.
     *
     * @param  string|null  $name
     * @param  string|null  $storageDisk
     * @return void
     */
    public function __construct($name = null, $storageDisk = null)
    {
        parent::__construct($name);

        $this->withStorageDisk($storageDisk);
    }

    /**
     * Set the storage disk.
     *
     * @param  string|null  $storageDisk
     * @return $this
     */
    public function withStorageDisk($storageDisk)
    {
        $this->storageDisk = $storageDisk;

        return $this;
    }

    /**
     * Set to delete file after send.
     *
     * @return $this
     */
    public function deleteFileAfterSend(bool $deleteFileAfterSend = true)
    {
        $this->deleteFileAfterSend = $deleteFileAfterSend;

        return $this;
    }

    /**
     * Perform the action request using custom dispatch handler.
     *
     * @return \Laravel\Nova\Actions\Response
     */
    protected function dispatchRequestUsing(ActionRequest $request, Response $response, ActionFields $fields)
    {
        $this->then(function ($results) {
            return $results->first();
        });

        $query = $request->toSelectedResourceQuery();

        $query->when($this->withQueryCallback instanceof Closure, function ($query) use ($fields) {
            return call_user_func($this->withQueryCallback, $query, $fields);
        });

        $eloquentGenerator = function () use ($query) {
            foreach ($query->cursor() as $model) {
                yield $model;
            }
        };

        $filename = $fields->get('filename') ?? sprintf('%s-%d.csv', $this->uriKey(), now()->format('YmdHis'));

        $extension = 'csv';

        if (Str::contains($filename, '.')) {
            [$filename, $extension] = explode('.', $filename);
        }

        $exportFilename = sprintf(
            '%s.%s',
            $filename,
            $fields->get('writerType') ?? $extension
        );

        $exportedFilename = (new FastExcel($eloquentGenerator()))->export("/tmp/{$exportFilename}", $this->withFormatCallback);

        $storedFilename = Storage::disk($this->storageDisk)->putFileAs(
            'nova-actions-export-as-csv', new File($exportedFilename), $exportFilename, 'public'
        );

        (new Filesystem())->delete($exportedFilename);

        return $response->successful([
            response()->json(
                static::download(
                    URL::signedRoute('nova-on-vapor.download', array_filter([
                        'disk' => $this->storageDisk,
                        'filename' => $storedFilename,
                        'deleteFileAfterSend' => $this->deleteFileAfterSend ? 1 : 0,
                    ])),
                    $filename
                )
            ),
        ]);
    }
}
