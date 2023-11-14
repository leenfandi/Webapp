<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([ 'prefix' => 'user', ], function ($router) {

    Route::post('register',[AuthController::class,'register']);
    Route::post('login',[AuthController::class,'login']);
    Route::post('logout',[AuthController::class,'logout']);
    Route::post('updateprofile',[AuthController::class,'updateProfile']);
    Route::post('change_password',[AuthController::class,'changePassword']);
    Route::delete('deletemyaccount',[AuthController::class,'DeleteMyAccount']);

 });
 Route::group([ 'prefix' => 'admin', ], function ($router) {

    Route::post('register',[AuthController::class,'registeradmin']);
    Route::post('login',[AuthController::class,'login']);
 });

 Route::middleware('auth:api')->group(function ()
 {
 Route::post('upload', [FileController::class, 'uploadFile']);
 Route::post('updatestatus/{fileid}', [FileController::class, 'updateStatus']);
 Route::post('reservefile',[FileController::class,'reserveFile']);
 Route::delete('delete-free-files/{id}', [FileController::class, 'deleteFile']);
 Route::get('download/{fileid}', [FileController::class, 'downloadFile']);
 });






















































































/*
 public function deleteFile(Request $request, $id)
    {
        try {
            \Log::info('Received DELETE request for file ID: ' . $id);

            $fileId = $id;
            \Log::info('File ID from route parameter: ' . $fileId);

            $file = File::find($fileId);
            \Log::info('File Object: ' . json_encode($file));

            if (!$file) {
                return response()->json(['message' => 'File not found'], 404);
            }

            if ($file->status == 0) {

                $filePath = 'public/uploads/publicfiles' . $file->path;
               Storage::disk('public')->delete($filePath);
                $result = $file->delete();
                \Log::info('File deleted successfully.');
                return response()->json(['message' => 'File deleted successfully']);
            }
            else {
                \Log::info('File is not free.');
                return response()->json(['message' => 'File is not free'], 400);
            }
        }
         catch (\Exception $e) {
            \Log::error('Exception: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
*/