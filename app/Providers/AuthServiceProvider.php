<?php

namespace App\Providers;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Providers\stdClass;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $auth = $request->header('Authorization');

            // Log the Authorization header
            error_log('Authorization Header: ' . print_r($auth, true));

            if (empty($auth)) {
                error_log('Authorization header is empty');
                return null;
            }

            $authParts = explode(' ', $auth);
            if (count($authParts) != 2 || $authParts[0] != 'Bearer') {
                error_log('Authorization header is not in the expected format');
                return null;
            }

            $token = $authParts[1];
            if (empty($token)) {
                error_log('Token is empty');
                return null;
            }

            // Log the JWT_SECRET value to ensure it's being read correctly
            $jwtSecret = env('JWT_SECRET');
            error_log('JWT_SECRET: ' . $jwtSecret);

            if (empty($jwtSecret)) {
                error_log('JWT_SECRET is empty');
                return null;
            }

            try {
                // Use the correct decoding method
                $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
                error_log('Decoded JWT: ' . print_r($decoded, true));

                // Check if token is expired
                if ($decoded->exp < time()) {
                    error_log('Token is expired');
                    return null;
                }

                $user = User::find($decoded->uid);
                if ($user) {
                    error_log('User found: ' . $user->id);
                } else {
                    error_log('User not found');
                }
                return $user;

            } catch (\Throwable $th) {
                // Log the error
                error_log('JWT decoding error: ' . $th->getMessage());
                return null;
            }
        });
    }
}
