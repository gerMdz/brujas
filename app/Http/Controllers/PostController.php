<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PostController extends Controller
{

    public function store()
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . auth()->user()->accessToken->access_token];
        $url = 'http://localhost:8080/v1/posts';
        $params = [
            'nombre' => 'Nombre 1',
            'urlLink' => 'link-1',
            'resumen' => 'Post por cliente',
            'body' => 'Todo es todo',
            'categoria_id' => 1,
        ];

        $response = Http::withHeaders(
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . auth()->user()->accessToken->access_token]
            )
        ->post($url, [
            'nombre' => 'Nombre 1',
            'urlLink' => 'link-1',
            'resumen' => 'Post por cliente',
            'body' => 'Todo es todo',
            'categoria_id' => 1,
        ]);

        return $response->json();
    }
}
