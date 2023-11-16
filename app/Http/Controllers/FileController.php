<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\File;
use App\Models\Group;
use App\Models\Groupofuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\FileController;
use PhpOffice\PhpWord\IOFactory;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;


class FileController extends Controller
{
    public function uploadFile(Request $request)
{
    $user = auth()->user();
    $userId = $user->id;

    if ($request->hasFile('path')) {
        $file = $request->file('path');

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'path' => 'required|file|mimes:txt,pdf,docx|max:2048', //2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $hh = $request->name;
        $extension = $file->getClientOriginalExtension();
        $filename = $hh . '.' . $extension;

        $filePath = $file->storeAs('public' . $userId, $filename);

        $fileContents = file_get_contents($file->getRealPath());


       // $encodedContents = mb_convert_encoding($fileContents, 'UTF-8', 'auto');

        $newFile = new File();
        $newFile->name = $filename;
        $newFile->path = $filePath;
        $newFile->user_id = $userId;
        $newFile->status = 0;
        $newFile->save();

        return response()->json([
            'message' => 'File uploaded successfully',
            'file_name' => $filename,
            'file_path' => $filePath,
            'user_id' => $userId,
          //  'file_contents' => $fileContents,
        ], 200);

      //  return response()->download($filePath, $filename);
    }
    else {
        return response()->json(['message' => 'No file selected'], 400);
    }
}
    public function updateStatus(Request $request, $id)
    {
        $file = File::find($id);
        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $file->status = $request->input('status');
        $file->save();

        return response()->json(['message' => 'File status updated successfully'], 200);
    }

    public function reserveFile(Request $request)
    {
        $fileId =  new File();
        $fileId->id = $request['id'];

        $file = File::find($fileId->id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        if ($file->status === 1) {
            return response()->json(['message' => ' Sorry ,File already reserved'], 400);
        }

        $file->status = 1;
        $file->save();

        return response()->json(['message' => 'File reserved successfully'], 200);
    }



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
            $filePath = 'public' . $file->path;
            \Log::info('Complete File Path: ' . public_path($filePath));


            \Log::info('File Exists: ' . (Storage::disk('public')->exists($filePath) ? 'Yes' : 'No'));


            $deleteResult = Storage::disk('public')->delete($filePath);


            \Log::info('File Deletion Result: ' . json_encode($deleteResult));


            $result = $file->delete();
            \Log::info('File deleted successfully.');
            return response()->json(['message' => 'File deleted successfully']);
        } else {
            \Log::info('File is not free.');
            return response()->json(['message' => 'File is not free'], 400);
        }
    } catch (\Exception $e) {
        \Log::error('Exception: ' . $e->getMessage());
        return response()->json(['message' => $e->getMessage()], 500);
    }
}
public function downloadFile(Request $request)
{
    if (!$request->hasFile('file')) {
        return response()->json(['message' => 'you have not selected any file'], 403);

    }

    $file = $request->file('file');
    $filename = $file->getClientOriginalName();
    $publicFilePath = $file->getRealPath();

    $desktopFolder = $request->input('desktop_folder');
    $desktopPath = 'C:/Users/ASUS/Desktop/' . $desktopFolder . '/';
    $desktopFile = $desktopPath . $filename;
    $result = copy($publicFilePath, $desktopFile);

    if (!$result) {
        return response()->json(['message' => 'cannot download the file'], 403);

    }
    return response()->json(['message' => 'file download is completed'], 200);

}

public function downloadManyFiles(Request $request)
{
    if (!$request->hasFile('files')) {
        return response()->json(['message' => 'You have not selected any files'], 403);
    }

    $files = $request->file('files');
    $desktopFolder = $request->input('desktop_folder');
    $desktopPath = 'C:/Users/ASUS/Desktop/' . $desktopFolder . '/';
    $uploadedFiles = [];

   
    if (!is_dir($desktopPath)) {
        mkdir($desktopPath, 0777, true);
    }


    foreach ($files as $file) {
        $filename = $file->getClientOriginalName();
        $desktopFile = $desktopPath . $filename;


        \Log::info('Public File Path: ' . $file->getRealPath());
        \Log::info('Desktop File Path: ' . $desktopFile);


        $result = \Storage::putFileAs($desktopPath, $file, $filename);

        if (!$result) {
          \Log::error('Error copying file: ' . $filename);
            return response()->json(['message' => 'Cannot download one or more files'], 403);
        }

        $uploadedFiles[] = $filename;
    }

    \Log::info('Files download completed');

    return response()->json(['message' => 'Files download completed', 'uploaded_files' => $uploadedFiles], 200);
}


        }
