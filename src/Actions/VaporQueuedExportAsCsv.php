<?php

namespace NovaKit\NovaOnVapor\Actions;

use Closure;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Response;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\ActionRequest;
use NovaKit\NovaOnVapor\Jobs\QueuedExportAsCsv;
use function Laravie\SerializesQuery\serialize;

class VaporQueuedExportAsCsv extends VaporExportAsCsv
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
        $this->then(function ($results) {
            return $results->first();
        });

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
            $request->user()->getKey(),
            $this->withFormatCallback,
            /* @var array{exportFilename: string, deleteFileAfterSend: bool, storageDisk: string|null, notify: string} */
            [
                'filename' => $exportFilename,
                'extension' => $extension,
                'deleteFileAfterSend' => $this->deleteFileAfterSend,
                'storageDisk' => $this->storageDisk,
                'notify' => 'email',
            ],
        );

        $connection = property_exists($this, 'connection') ? $this->connection : null;
        $queue = property_exists($this, 'queue') ? $this->queue : null;

        Queue::connection($connection)->pushOn($queue, $job);

        return $response->successful([
            response()->json(
                static::message(__('The action was executed successfully.'))
            ),
        ]);
    }
}
