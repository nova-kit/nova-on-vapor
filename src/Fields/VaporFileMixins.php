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
     * `downloadViaTemporaryUrl()` macro.
     *
     * @return \Closure(\DateTimeInterface|null):\Laravel\Nova\Fields\VaporFile
     */
    public function downloadViaTemporaryUrl()
    {
        return function (DateTimeInterface $expiration = null) {
            return $this->download(function ($request, $model, $disk, $value) use ($expiration) {
                return redirect(
                    Storage::disk($disk)->temporaryUrl($value, $expiration ?? now()->addMinutes(5))
                );
            });
        };
    }
}
