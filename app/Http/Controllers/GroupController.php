<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\File;
use App\Models\Group;
use App\Models\Groupofuser;
use App\Models\Groupoffile;
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


class GroupController extends Controller
{
    public function addgroup(Request $request){

        $user = auth()->user();
        $userId = $user->id;
        $input = $request->all();
        $group = new Group();

      $group->name = $input['name'];
      $group->owner = $userId;
      $group->save();

      return response()->json([
         'messege'=> 'Group create seccesfuly ',
         'name' =>   $group->name ,
         'owner' => $group->owner,
      ]);
      }
      public function adduser(Request $request)
      {
          $user = auth()->user();
          $ownerId = $user->id;
          $input = $request->all();
          $username = new User();
          $group = new Group();

          $group = Group::find($input['group_id']);

          if (!$group) {
              return response()->json([
                  'message' => 'Group not found',
              ], 404);
          }

          if ($group->owner !== $ownerId) {
              return response()->json([
                  'message' => 'You are not the owner of this group. Only the owner can add users.',
              ], 403);
          }

          $username->name = $input['name'];
          $group->id = $input['group_id'];

          $user = User::where('name', $username->name)->first();

          if (!$user) {
              return response()->json([
                  'message' => 'User not found',
              ], 404);
          }

          $group_of_users = Groupofuser::create([
              'user_id' => $user->id,
              'group_id' => $request->group_id,
          ]);

          return response()->json([
              'message' => 'User added',
              'data' => [
                  'id' => $user->id,
                  'name' => $user->name,
                  'group_id' => $request->group_id,
              ],
          ], 200);
      }
      public function addfile_to_group(Request $request)
      {
          $user = auth()->user();
          $ownerId = $user->id;
          $input = $request->all();
          $file = new File();
          $group = new Group();

          $group = Group::find($input['group_id']);

          if (!$group) {
              return response()->json([
                  'message' => 'Group not found',
              ], 404);
          }

          if ($group->owner !== $ownerId) {
              return response()->json([
                  'message' => 'You are not the owner of this group Only the owner can add filess.',
              ], 403);
          }

          $file->name = $input['file_name'];
          $group->id = $input['group_id'];

          $file = File::where('name', $file->name)->first();

          if (!$file) {
              return response()->json([
                  'message' => 'File not found',
              ], 404);
          }

          try {
              $group_of_files = Groupoffile::create([
                  'file_id' => $file->id,
                  'group_id' => $request->group_id,
              ]);

              return response()->json([
                  'message' => 'File added to your group',
                  'data' => [
                      'id' => $group_of_files->id,
                      'file_name' => $file->name,
                      'file_id' => $file->id,
                      'group_id' => $request->group_id,
                  ],
              ], 200);
          } catch (QueryException $e) {
              return response()->json([
                  'message' => 'Error occurred while adding the file to the group',
              ], 500);
          }
      }

      public function deletefile_from_group($group_id,$file_name)
      {
          $user = auth()->user();
          $ownerId = $user->id;
         // $input = $request->all();
           $g = new Groupoffile();

          $group = Group::find($group_id);

          if (!isset($group_id)) {
            return response()->json([
                'message' => 'Group ID is missing in the request',
            ], 400);
        }



          $file = File::where('name', $file_name)->first();

          if (!$file) {
              return response()->json([
                  'message' => 'File not found',
              ], 404);
          }

          $groupOfFile = Groupoffile::where('group_id',  $group_id)
              ->where('file_id', $file->id)
              ->first();

          if (!$groupOfFile) {
              return response()->json([
                  'message' => 'File is not associated with the group',
              ], 404);
          }

          if ($file->user_id !== $ownerId) {
              return response()->json([
                  'message' => 'You are not the owner of this file. Only the owner can delete it from the group.',
              ], 403);
          }

          $groupOfFile->delete();

          return response()->json([
              'message' => 'File deleted successfully from the group',
          ]);
      }

public function deleteUserFromGroup($user_id,$group_id)
{
  

        $user = auth()->user();
        $ownerId = $user->id;
        $userId = $user_id;
        $groupId = $group_id;
        $group = Group::where('id', $groupId)->where('owner', $ownerId)->first();

        if (!$group) {
            return response()->json([
                'message' => 'You are not the owner of this group Only the owner can delete users from the group.',
            ], 403);
        }
        $groupOfUser = Groupofuser::where('group_id', $groupId)->where('user_id', $userId)->first();

        if (!$groupOfUser) {
            return response()->json([
                'message' => 'The user is not a member of the group.',
            ], 404);
        }

        $groupOfFile = Groupoffile::where('group_id', $groupId)
        ->whereHas('file', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->first();


       if ($groupOfFile) {
           return response()->json([
                'message' => 'The user has received files in the group. Remove the files first before deleting the user',
           ], 403);
      }

        $groupOfUser->delete();

        return response()->json([
            'message' => 'User deleted successfully from the group.',
        ], 200);

}
public function deleteGroupIfNoReservedFiles($group_id)
{
    try {
        $group = Group::findOrFail($group_id);

        $hasReservedFiles = File::whereHas('groupoffiles', function ($query) use ($group_id) {
            $query->where('group_id', $group_id);
        })->where('status', 1)->exists();

        if ($hasReservedFiles) {
            return response()->json([
                'message' => 'Cannot delete the group because there are reserved files in it.',
            ], 403);
        }

        $group->delete();

        return response()->json([
            'message' => 'Group deleted successfully.',
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Error deleting group: ' . $e->getMessage());

        return response()->json([
            'message' => 'An error occurred while deleting the group.',
        ], 500);
    }
}

    /*  public function deletefile_from_group(Request $request)
{
    try {

        $request->validate([
          //  'group_id' => 'required|exists:groups,id',
            'file_name' => 'required|string',
        ]);


        $user = auth()->user();
        $ownerId = $user->id;


      //  $group = Group::findOrFail($request->input('file_name'));


        $file = File::where('name', $request->input('file_name'))->firstOrFail();


        if ($file->owner !== $ownerId) {
            return response()->json([
                'message' => 'You are not the owner of this file. Only the owner can delete it from the group.',
            ], 403);
        }


        $groupOfFile = Groupoffile:://where('group_id', $group->id)
            where('file_id', $file->id)
            ->first();


        if (!$groupOfFile) {
            return response()->json([
                'message' => 'File is not associated with the group',
            ], 404);
        }


        $groupOfFile->delete();

        return response()->json([
            'message' => 'File deleted successfully from the group',
        ], 200);
    } catch (\Exception $e) {

        Log::error('Error deleting file from group: ' . $e->getMessage());


        return response()->json([
            'message' => 'An error occurred while deleting the file from the group',
        ], 500);

    }
}*/
}
