<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        \Log::debug('🔍 CORS Middleware - Requête reçue', [
            'method' => $request->method(),
            'path' => $request->path(),
            'origin' => $request->header('Origin') ?? 'NO_ORIGIN',
            'host' => $request->header('Host'),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Configuration CORS optimisée pour React Native et Expo
        $allowedOrigins = [
            env('MOBILE_APP_URL', 'https://smallpay.godloveshop.com'),
            'http://localhost:19006',  // Expo par défaut
            'http://localhost:19002',  // Expo web
            'http://127.0.0.1:19006',
            'http://127.0.0.1:19002',
            'exp://127.0.0.1:19000',  // Expo sur Android/iOS
            'exp://localhost:19000',
            'http://localhost:8081',  // Metro bundler
            '*',  // Permettre toutes les origines (pour React Native qui ne envoie pas d'Origin header)
        ];

        $origin = $request->header('Origin');
        
        // Vérifier si l'origine est autorisée
        // Si pas d'Origin header, utiliser la valeur par défaut (cas React Native)
        $allowedOrigin = ($origin && in_array($origin, $allowedOrigins)) ? $origin : '*';

        $headers = [
            'Access-Control-Allow-Origin' => $allowedOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN, Pragma, Cache-Control',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
            'Vary' => 'Origin',
        ];

        // Pour les requêtes OPTIONS (preflight)
        if ($request->getMethod() === "OPTIONS") {
            return response('CORS Preflight Successful', 200)->withHeaders($headers);
        }

        $response = $next($request);

        // Appliquer les headers CORS à la réponse
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}