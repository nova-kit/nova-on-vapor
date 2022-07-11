<?php

namespace NovaKit\NovaOnVapor\Actions;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
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
     * Perform the action request using custom dispatch handler.
     *
     * @param  \Laravel\Nova\Http\Requests\ActionRequest  $request
     * @param  \Laravel\Nova\Actions\Response  $response
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
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

        return $response->successful([
            $this->dispatchExportUsing($eloquentGenerator, $filename),
        ]);
    }

    /**
     * Handle exporting the file.
     *
     * @param  \Closure():\Generator  $generator
     * @param  string  $filename
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function dispatchExportUsing($generator, $filename)
    {
        $exportedFilename = (new FastExcel($generator()))->export("/tmp/{$filename}", $this->withFormatCallback);

        $storedFilename = Storage::disk($this->storageDisk)->putFileAs(
            'nova-actions-export-as-csv', new File($exportedFilename), $filename, 'public'
        );

        (new Filesystem())->delete($exportedFilename);

        return response()->json(
            //static::download(Storage::disk($this->storageDisk)->url($storedFilename), $filename)
        );
    }
}
