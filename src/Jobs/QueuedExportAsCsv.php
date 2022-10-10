<?php

namespace NovaKit\NovaOnVapor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use function Laravie\SerializesQuery\unserialize;
use Rap2hpoutre\FastExcel\FastExcel;

class QueuedExportAsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The custom format callback.
     *
     * @var (\Closure(\Illuminate\Database\Eloquent\Model):array<string, mixed>)|null
     */
    public $withFormatCallback;

    /**
     * The configuration options.
     *
     * @var array{exportFilename: string, deleteFileAfterSend: bool, storageDisk: string|null, notify: string}
     */
    public $options;

    /**
     * Create a new job instance.
     *
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

        $exportedFilename = (new FastExcel($eloquentGenerator()))->export("/tmp/{$this->exportFilename}", $this->withFormatCallback);

        $storedFilename = Storage::disk($this->storageDisk)->putFileAs(
            'nova-actions-export-as-csv', new File($this->exportedFilename), $this->exportFilename, 'public'
        );

        (new Filesystem())->delete($this->exportedFilename);

        URL::signedRoute('nova-on-vapor.download', array_filter([
            'disk' => $this->storageDisk,
            'filename' => $storedFilename,
            'deleteFileAfterSend' => $this->deleteFileAfterSend ? 1 : 0,
        ]));
    }
}
