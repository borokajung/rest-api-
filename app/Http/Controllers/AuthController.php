<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @apiDefine Auth Bearer token szükséges
 * Az `Authorization: Bearer <token>` fejléc megadása kötelező (a token a /api/login végponttól kapható).
 */
class AuthController extends Controller
{
    /**
     * @api {post} /api/login Bejelentkezés
     * @apiName Login
     * @apiGroup Auth
     * @apiVersion 1.0.0
     * @apiDescription Email + jelszó alapján Bearer tokent ad vissza. A felhasználó korábbi tokenjei érvénytelenné válnak.
     *
     * @apiBody {String} email Felhasználó email címe
     * @apiBody {String} password Jelszó
     *
     * @apiSuccess {Object} user A bejelentkezett felhasználó
     * @apiSuccess {Number} user.id Felhasználó azonosító
     * @apiSuccess {String} user.name Név
     * @apiSuccess {String} user.email Email
     * @apiSuccess {String} token Bearer token a védett végpontokhoz
     *
     * @apiSuccessExample {json} Sikeres válasz:
     *     HTTP/1.1 200 OK
     *     {
     *       "user": { "id": 1, "name": "user", "email": "user@example.com" },
     *       "token": "1|abcdef..."
     *     }
     *
     * @apiError (401) {String} message Hibás email vagy jelszó
     * @apiError (422) {String} message Validációs hiba
     * @apiErrorExample {json} Hibás belépési adatok:
     *     HTTP/1.1 401 Unauthorized
     *     { "message": "Invalid email or password" }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('access')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * @api {post} /api/logout Kijelentkezés
     * @apiName Logout
     * @apiGroup Auth
     * @apiVersion 1.0.0
     * @apiPermission Auth
     * @apiDescription Az aktuálisan használt token törlése.
     *
     * @apiHeader {String} Authorization Bearer token (`Bearer <token>`)
     *
     * @apiSuccess {String} message Visszajelzés
     * @apiSuccessExample {json} Sikeres válasz:
     *     HTTP/1.1 200 OK
     *     { "message": "Logged out" }
     *
     * @apiError (401) {String} message Hiányzó vagy érvénytelen token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
