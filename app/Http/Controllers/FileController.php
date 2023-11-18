<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\File;
use App\Models\Group;
use App\Models\Groupofuser;
use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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



//use Illuminate\Support\Facades\File;

public function downloadMultipleFiles(Request $request)
{
    $zip = new ZipArchive();

    if (!$request->has('files')) {
        return response()->json(['message' => 'You have not selected any files'], 403);
    }

    $filePaths = $request->input('files');
    $desktopFolder = $request->input('desktop_folder');
    $desktopPath = 'C:/Users/ASUS/Desktop/' . $desktopFolder . '/';
    $zipFileName = $desktopFolder . '.zip';
    $zipFilePath = $desktopPath . $zipFileName;

    try {
        // Create the zip archive
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($filePaths as $filePath) {
                $filename = basename($filePath);

                // Check if the file exists
                if (Storage::exists($filePath)) {
                    $fileContent = Storage::get($filePath);

                    // Add the file to the zip archive
                    $zip->addFromString($filename, $fileContent);
                } else {
                    Log::error('File not found: ' . $filePath);
                }
            }
            $zip->close();

            // Return the zip file for download
            $headers = [
                'Content-Type' => 'application/zip',
            ];
            $fileResponse = Response::download($zipFilePath, $zipFileName, $headers);

            return $fileResponse;
        } else {
            return response()->json(['message' => 'Error creating zip file'], 500);
        }
    } catch (\Exception $e) {
        Log::error('Error creating zip file: ' . $e->getMessage());
        return response()->json(['message' => 'An error occurred while creating the zip file'], 500);
    }
}

public function showFilesInGroup(Request $request)
{
    $group_id = $request->input('group_id');
    $group = Group::find($group_id);

    if (!$group) {
        return response()->json(['message' => 'Group not found'], 404);
    }

    $files = File::whereHas('groupoffiles', function ($query) use ($group_id) {
        $query->where('group_id', $group_id);
    })->orderBy('id', 'asc')->get();

    $fileData = [];

    foreach ($files as $file) {
        $userData = null;

        if ($file->status == 1) {

            $reservedBy = $file->user;

            if ($reservedBy) {
                $userData = [
                    'user_id' => $reservedBy->id,
                    'username' => $reservedBy->name,

                ];
            }
        }

        $fileData[] = [
            'file_id' => $file->id,
            'file_name' => $file->name,
            'file_path' =>$file->path,
            'status' => $file->status,
            'reserved_by' => $userData,

        ];
    }

    return response()->json([
        'message' => 'These are the files contained in the group',
        'files' => $fileData,
    ], 200);
}







/*public function downloadManyFiles(Request $request)
{
    if (!$request->hasFile('files')) {
        return response()->json(['message' => 'You have not selected any files'], 403);
    }

    $files = $request->file('files');
    $desktopFolder = $request->input('desktop_folder');
    $desktopPath = 'C:/Users/ASUS/Desktop/' . $desktopFolder . '/';
    $uploadedFiles = [];

    try {
        Storage::makeDirectory($desktopPath);
    } catch (\Exception $e) {
        Log::error('Error creating directory: ' . $desktopPath . ': ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        return response()->json(['message' => 'Error occurred while creating directory'], 500);
    }

    foreach ($files as $file) {
        $filename = $file->getClientOriginalName();
        $desktopFile = $desktopPath . $filename;

        Log::info('Public File Path: ' . $file->getRealPath());
        Log::info('Desktop File Path: ' . $desktopFile);

        try {
            $result = Storage::putFileAs($desktopPath, $file, $filename);

            if (!$result) {
                Log::error('Error copying file: ' . $filename);
                return response()->json(['message' => 'Cannot download one or more files'], 403);
            }

            $uploadedFiles[] = $filename;
        } catch (\Exception $e) {
            Log::error('Error copying file: ' . $filename . ': ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['message' => 'Error occurred while downloading files'], 500);
        }
    }

    Log::info('Files download completed');

    return response()->json(['message' => 'Files download completed', 'uploaded_files' => $uploadedFiles], 200);
}*/

        }

