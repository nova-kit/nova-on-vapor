<?php

namespace NovaKit\NovaOnVapor\Actions;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Queue;
use Laravel\Nova\Actions\Response;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\ActionRequest;
use function Laravie\SerializesQuery\serialize;

class VaporQueuedExportAsCsv extends VaporExportAsCsv implements ShouldQueue
{
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
        $query = $request->toSelectedResourceQuery();

        $query->when($this->withQueryCallback instanceof Closure, function ($query) use ($fields) {
            return call_user_func($this->withQueryCallback, $query, $fields);
        });

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

        $job = new QueuedExportAsCsv(
            serialize($query),
            $request->user(),
            $this->withFormatCallback,
            /* @var array{exportFilename: string, deleteFileAfterSend: bool, storageDisk: string|null, notify: string} */
            [
                'exportFilename' => $exportFilename,
                'deleteFileAfterSend' => $this->deleteFileAfterSend,
                'storageDisk' => $this->storageDisk,
                'notify' => 'email',
            ],
        );

        $connection = property_exists($this, 'connection') ? $this->connection : null;
        $queue = property_exists($this, 'queue') ? $this->queue : null;

        Queue::connection($connection)->pushOn($queue, $job);
    }
}
