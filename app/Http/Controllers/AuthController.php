<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\WebServices;
use Spatie\Permission\Models\Role;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(WebServices $exampleService)
    {
        $this->exampleService = $exampleService;
    }
    protected $db_mysql;
   // public function __construct()
   // {
    //    $this ->db_mysql= config('database.connections.mysql.database');
    // $this->middleware('auth:api',['except'=>['login','register']]);
   // }


   public function register(Request $request)
   {
     $request->all();
       $data = [
           'name' => $request->name,
           'email' => $request->email,
           'password' => $request->password,
       ];

       $result = $this->exampleService->validateData($data);

       if (!isset($result['success']) || !$result['success']) {
           return response()->json([
               'success' => false,
               'message' => 'Validation failed',
           ], 400);
       }

       $data['password'] = bcrypt($request->password);
       $user = User::create($data);
       $credentials = $request->only(['email', 'password']);
       $token = auth()->attempt($credentials);

       return response()->json([
           'success' => true,
           'message' => 'Successfully registered',
           'id' => $user->id,
           'user' => $user,
           'access_token' => $token,
       ], 201);
   }




    public function login(Request $request)
    {
        try {
            $data = [
                'email' => $request->email,
                'password' => $request->password,
            ];

            $result = $this->exampleService->validateDatalog($data);

            if (!isset($result['success']) || !$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $result['errors'] ?? null, // Include validation errors if available
                ], 400);
            }

            $credentials = $request->only(['email', 'password']);

            // If validation in validateData succeeds, continue with the login process
            if ($token = auth()->attempt($credentials)) {
                $user = auth()->user();

                return response()->json([
                    'access_token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                    ],
                ]);
            } else {
                throw new \Exception('Invalid email or password');
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }


     /* Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
   /* public function me()
    {
        return response()->json(auth()->user());
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
   public function logout(Request $request)
    {
        $request->user()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


   
    public function DeleteMyAccount()
    {
        $user = auth()->user();
            User::where('id' , $user->id)->delete();
            Auth::logout();

        return response()->json([
            'message' => 'Account deleted Successsfuly'
        ]);

    }
}
