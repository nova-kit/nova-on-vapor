<?php

namespace NovaKit\NovaOnVapor\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Vapor\Contracts\SignedStorageUrlController as SignedStorageUrlControllerContract;

class SignedStorageUrlController extends Controller implements SignedStorageUrlControllerContract
{
    /**
     * Create a new signed URL.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $disk = config('nova-on-vapor.minio.disk');

        Gate::authorize('uploadFiles', [
            $request->user(),
            $bucket = $request->input('bucket') ?: config("filesystems.disks.{$disk}.bucket"),
        ]);

        $client = $this->storageClient();

        $uuid = (string) Str::uuid();

        $signedRequest = $client->createPresignedRequest(
            $this->createCommand($request, $client, $bucket, $key = ('tmp/'.$uuid)),
            '+10 minutes'
        );

        return response()->json([
            'uuid' => $uuid,
            'bucket' => $bucket,
            'key' => $key,
            'url' => (string) $signedRequest->getUri(),
            'headers' => $this->headers($request, $signedRequest),
        ], 201);
    }

    /**
     * Create a command for the PUT operation.
     *
     * @param  string  $bucket
     * @param  string  $key
     * @return \Aws\CommandInterface
     */
    protected function createCommand(Request $request, S3Client $client, $bucket, $key)
    {
        return $client->getCommand('putObject', array_filter([
            'Bucket' => $bucket,
            'Key' => $key,
        ]));
    }

    /**
     * Get the headers that should be used when making the signed request.
     *
     * @param  \Psr\Http\Message\RequestInterface  $signedRequest
     * @return array
     */
    protected function headers(Request $request, $signedRequest)
    {
        return array_merge(
            $signedRequest->getHeaders(),
            [
                'Content-Type' => $request->input('content_type') ?: 'application/octet-stream',
            ]
        );
    }

    /**
     * Get the S3 storage client instance.
     *
     * @return \Aws\S3\S3Client
     */
    protected function storageClient()
    {
        $disk = config('nova-on-vapor.minio.disk');

        $config = [
            'region' => config("filesystems.disks.{$disk}.region"),
            'version' => 'latest',
            'use_path_style_endpoint' => true,
            'url' => config("filesystems.disks.{$disk}.endpoint"),
            'endpoint' => config("filesystems.disks.{$disk}.endpoint"),
            'credentials' => [
                'key' => config("filesystems.disks.{$disk}.key"),
                'secret' => config("filesystems.disks.{$disk}.secret"),
            ],
        ];

        return S3Client::factory($config);
    }
}
