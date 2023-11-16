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
}
