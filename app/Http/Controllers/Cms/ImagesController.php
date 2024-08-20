<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\ServerFactory;
use App\Http\Controllers\Controller;

class ImagesController extends Controller
{
    public function show(Filesystem $filesystem, Request $request, $path)
    {
        $server = ServerFactory::create([
            'response' => new SymfonyResponseFactory($request),
            'source' => $filesystem->getDriver(),
            'cache' => $filesystem->getDriver(),
            'cache_path_prefix' => '.glide-cache',
        ]);

        return $server->getImageResponse($path, $request->all());
    }
}
