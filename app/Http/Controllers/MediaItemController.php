<?php

namespace App\Http\Controllers;

use App\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MediaItemController extends Controller
{
    protected $validations = [

        'external_id' => 'nullable|max:200',
        'url' => 'url|max:1000',
        'size' => 'nullable|integer',
        'meta' => 'nullable|max:1000',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return MediaItem::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $mediaItem = new MediaItem();

        $mediaItem->forceFill($this->validate($request, $this->validations));

        $mediaItem->save();

        return $mediaItem;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(MediaItem $mediaItem)
    {
        return $mediaItem;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(MediaItem $mediaItem)
    {
        $mediaItem->delete();

        return response(null, 200);
    }

    public function getExampleClient(Request $request, $type = 'vue')
    {
        $clientId = $request->input('client_id');
        $tenant = route('ice.login.ui', []);

        $files = [];

        foreach (File::allFiles(resource_path(sprintf('examples/%s', $type))) as $file) {
            $name = $file->getRelativePathname();
            $contents = $file->getContents();

            $contents = str_replace('{{ client_id }}', $clientId, $contents);
            $contents = str_replace('{{ tenant }}', $tenant, $contents);

            $files[$name] = [
                'content' => $contents,
            ];
        }

        return $files;
    }
}
