<?php

namespace NovaKit\NovaOnVapor\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CsvExported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The User instance.
     *
     * @var \Illuminate\Foundation\Auth\User
     */
    public $user;

    /**
     * The storage filename.
     *
     * @var string
     */
    public $filename;

    /**
     * The storage disk.
     *
     * @var string|null
     */
    public $storageDisk;

    /**
     * The download URL.
     *
     * @var string
     */
    public $downloadUrl;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @param  string  $filename
     * @param  string|null  $storageDisk
     * @param  string  $downloadUrl
     * @return void
     */
    public function __construct($user, string $filename, $storageDisk, string $downloadUrl)
    {
        $this->user = $user;
        $this->filename = $filename;
        $this->storageDisk = $storageDisk;
        $this->downloadUrl = $downloadUrl;
    }
}
