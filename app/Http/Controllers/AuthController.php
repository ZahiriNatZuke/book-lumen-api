<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Login a user on system.
     *
     * @param Request $request
     * @return JsonResponse|ResponseInterface
     */
    public function login(Request $request): JsonResponse|ResponseInterface
    {
        $email = $request['email'];
        $pwd = $request['password'];
        if (empty($email) or empty($pwd)) return response()->json(['error' => 'You must fill all fields.']);

        try {
            $response = Http::post(config('service.passport.login_endpoint'), [
                'client_secret' => config('service.passport.client_secret'),
                'grant_type' => 'password',
                'client_id' => config('service.passport.client_id'),
                'username' => $email,
                'password' => $pwd
            ])->json();
            return response()->json(['data' => $response]);
        } catch (BadResponseException $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Register a new user on system.
     *
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        $result = Validator::make($request->only(['name', 'email', 'password']), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        if ($result->fails())
            return response()->json(['error' => $result->errors()], 422);

        $data = $result->validate();

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = app('hash')->make($data['password']);

        try {
            $user->save();
            return response()->json(compact('data'), 201);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Logout a user on system.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            auth()->user()->tokens()->each(function ($token) {
                $token->delete();
            });

            return response()->json(['msg' => 'Logged out Successfully.']);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }
}
