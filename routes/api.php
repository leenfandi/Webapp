<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;

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
    Route::delete('deletemyaccount',[AuthController::class,'DeleteMyAccount']);

 });

 Route::middleware('auth:api')->group(function ()
 {
 Route::post('upload', [FileController::class, 'uploadFile']);
 Route::post('updatestatus/{fileid}', [FileController::class, 'updateStatus']);
 Route::post('reservefile',[FileController::class,'reserveFile']);
 Route::delete('delete-free-files/{id}', [FileController::class, 'deleteFile']);
 Route::post('download', [FileController::class, 'downloadFile']);
 Route::post('download_many_files',[FileController::class,'downloadFiles']);
 Route::post('addgroup',[GroupController::class,'addgroup']);
 Route::post('adduser_to_group',[GroupController::class,'adduser']);
 Route::post('addfile_to_group',[GroupController::class,'addfile_to_group']);
 Route::delete('delete_file_from_group/{group_id}/{file_name}',[GroupController::class,'deletefile_from_group']);
 Route::delete('delete_user_from_group/{user_id}/{group_id}',[GroupController::class,'deleteUserFromGroup']);
 Route::delete('delete_group/{group_id}', [GroupController::class,'deleteGroupIfNoReservedFiles']);
 Route::post('show_files_in_group',[FileController::class,'showFilesInGroup']);
 Route::post('unreseved_file',[FileController::class,'cancelReservation']);
 Route::post('replace_file/{file_id}',[FileController::class,'replaceFile']);

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
