<?php

namespace NovaKit\NovaOnVapor\Fields;

use DateTimeInterface;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \Laravel\Nova\Fields\VaporFile
 */
class VaporFileMixins
{
    /**
     * Get default pivot attributes using mixin.
     *
     * @return \Closure():\Laravel\Nova\Fields\VaporFile
     */
    public function downloadViaUrl()
    {
        return function () {
            return $this->download(function ($request, $model, $disk, $value) {
                return redirect(
                    Storage::disk($disk)->url($value)
                );
            });
        };
    }

    /**
     * Get default pivot attributes using mixin.
     *
     * @return \Closure(\DateTimeInterface):\Laravel\Nova\Fields\VaporFile
     */
    public function downloadViaTemporaryUrl()
    {
        return function (DateTimeInterface $expiration) {
            return $this->download(function ($request, $model, $disk, $value) use ($expiration) {
                return redirect(
                    Storage::disk($disk)->temporaryUrl($value, $expiration)
                );
            });
        };
    }
}
