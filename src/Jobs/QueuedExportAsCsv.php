<?php

namespace NovaKit\NovaOnVapor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Fluent;
use Rap2hpoutre\FastExcel\FastExcel;
use function Laravie\SerializesQuery\unserialize;

class QueuedExportAsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The query builder.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    public $query;

    /**
     * The user.
     *
     * @var \Illuminate\Foundation\Auth\User
     */
    public $user;

    /**
     * The custom format callback.
     *
     * @var (\Closure(\Illuminate\Database\Eloquent\Model):array<string, mixed>)|null
     */
    public $withFormatCallback;

    /**
     * The configuration options.
     *
     * @var array{filename: string, deleteFileAfterSend: bool, storageDisk: string|null, notify: string}
     */
    public $options;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $query
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @param  callable  $withFormatCallback
     * @param  array{filename: string, deleteFileAfterSend: bool, storageDisk: string|null, notify: string}  $options
     * @return void
     */
    public function __construct($query, $user, $withFormatCallback, array $options)
    {
        $this->query = unserialize($query);
        $this->user = $user;
        $this->withFormatCallback = $withFormatCallback;

        $this->options = array_merge([
            'notify' => 'email',
            'deleteFileAfterSend' => false,
            'storageDisk' => null,
        ], $options);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $eloquentGenerator = function () {
            foreach ($this->query->cursor() as $model) {
                yield $model;
            }
        };

        $storageDisk = $this->options['storageDisk'];
        $filename = $this->options['filename'];
        $exportedFilename = (new FastExcel($eloquentGenerator()))->export("/tmp/{$filename}", $this->withFormatCallback);

        $storedFilename = Storage::disk($storageDisk)->putFileAs(
            'nova-actions-export-as-csv', new File($exportedFilename), $filename, 'public'
        );

        (new Filesystem())->delete($exportedFilename);

        URL::signedRoute('nova-on-vapor.download', array_filter([
            'disk' => $storageDisk,
            'filename' => $storedFilename,
            'deleteFileAfterSend' => $this->options['deleteFileAfterSend'] ? 1 : 0,
        ]));
    }
}
