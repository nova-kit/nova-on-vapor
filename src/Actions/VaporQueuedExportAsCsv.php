<?php

namespace NovaKit\NovaOnVapor\Actions;

use Laravel\Nova\Actions\Response;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\ActionRequest;

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
        //
    }
}
