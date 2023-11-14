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
   /*public function register(Request $request)
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

       $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => Hash::make($request->password),
       ]);

       $role = Role::where('name', 'user')->first();
       $user->assignRole($role);

       return response()->json([
           'message' => 'User created successfully',
           'user' => $user,
           'role' => $role->name,
           'token' => $user->password,
       ], 200);
   }
   public function registeradmin(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $role = Role::where('name', 'admin')->first();
    $user->assignRole($role);

    return response()->json([
        'message' => 'Admin user created successfully',
        'user' => $user,
        'token' => $user->password,
    ], 200);
}
*/

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


    /**
     * Login
     */
   /* public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!auth()->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 400);
        }

        $user = auth()->user();
        $role = '';

        if ($user->hasRole('user')) {
            $role = 'user';
        }

        if ($user->hasRole('admin')) {
            $role = 'admin';
        }

        $token = auth()->login($user);

        return response()->json([
            'message' => 'Login successful',
            'data' => $user,
            'Role' => $role,
            'token' => $token,
        ], 200);
    }*/

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


    public function updateProfile(Request $request)
    {

        $input = $request->all();
        $id = Auth::guard('api')->id();
       $user = User::find($id);
        $validator = validator($input, [
            'name'=>'string',
            'email'=>'string|email|unique:users',
            'number'=>'string',
            'image' => 'nullable',


        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()]);
        }

        if($request->exists('name')){
        $user->name= $input['name'] ;
        }
        if($request->exists('email')){
        $user->email= $input['email'] ;
        }





        $user->save();

        return response()->json(['user'=>$user,'msg'=>'user update succefully']);
    }


    public function changePassword (Request $request){

        $validator = Validator::make($request->all(), [

       'old_password' => 'required',
       'password' => 'required|min:8',
       'confirm_password' => 'required|same:password'

]);
        if ($validator->fails()) {
           return response()->json([
            'message'=> 'Validator fails',
            'error'=>$validator->errors()]);
}

         $user = $request->user();
         if(Hash::check($request->old_password , $user->password)){

            $user->update([
             'password' => Hash::make($request->password)
            ]);
            return response()->json([
                'message' => 'Change password Successsfuly'
                ] ,200);
}

           else {
               return response()->json([
                'message' => 'Old password does not matched'
                ] ,400);
}
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
