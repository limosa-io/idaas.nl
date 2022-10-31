<?php

namespace App\Http\Controllers;

use App\Client;
use Illuminate\Support\Facades\Route;

class GitHubController extends Controller
{
    public function index()
    {

        $clients = Client::all();

        foreach ($clients as $client) {
            $client_json = json_encode($client);
        }


        // Accept: application/vnd.github+json

        // https://api.github.com/repos/arietimmerman/idaas-config/contents/
        // {
        //     "message": "This repository is empty.",
        //     "documentation_url": "https://docs.github.com/v3/repos/contents/#get-contents"
        //   }


        // TODO: Als response is een array, dan is het een directory. Anders een file.
        // Voor elk


        // TODO: loop over Applications ??

        // Create folder "/application"\
        // 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


        // https://api.github.com/repos/arietimmerman/idaas-config/contents/test123.txt
        // Update:
        // {
        //     "owner": "arietimmerman",
        //     "repo": "idaas-config",
        //     "sha": "0d5a690c8fad5e605a6e8766295d9d459d65de42",
        //     "path": "test123.txt",
        //     "message": "dit is een eerste commit",
        //     "committer": {
        //       "name": "Idaas Syncer",
        //       "email": "no-reply@idaas.nl"
        //     },
        //       "content": "bXkgbmV3IGZpbGUgY29udGVudHM="
        //   }
    }
}
