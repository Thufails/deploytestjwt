<?php


namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //Mencari seluruh admin
    public function showAllAdmin()
    {
        $admin = Admin::all();
        return response()->json([
            'success' => true,
            'message' => 'Profile has been Showed',
            'data'=> $admin,
        ], 200);
    }



    public function showAdminById($id)
    {
    $admin = Admin::find($id);
    if (!$admin) {
        return response()->json([
            'success' => false,
            'message' => 'Admin not found',
        ], 404);
    }
    return response()->json([
        'success' => true,
        'message' => 'Admin profile has been retrieved successfully',
        'data' => $admin,
    ], 200);
    }


    public function addAdmin(Request $request)
    {
        $validated = $this->validate($request,[
            'title' => 'required|max:255',
            'description' => 'required|max:255'
        ]);

        $admin = new Admin();
        $admin->title = $validated['title'];
        $admin->description = $validated['description'];
        $admin->user_id = Auth::user()->id;
        $admin->save();

        if ($admin) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully Filled',
                'data' => $admin
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Dashboard Filled Failed',
                'data' => ''
            ], 400);
        }
    }

    public function updateAdmin(Request $request, $id)
    {
        $admin = Admin::where('id', $id)->where('user_id', Auth::user()->id)->first();
        if (empty($admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Admin does not exist',
            ], 404);
        }
        $validated = $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required',
        ]);

        $admin->title = $validated['title'];
        $admin->description = $validated['description'];
        $admin->save();
        if ($admin) {
            return response()->json([
                'success' => true,
                'message' => 'Admin successfully updated',
                'data' => $admin
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin',
                'data' => null
            ], 500);
        }
    }

    public function deleteAdmin(Request $request, $id)
    {
        {
            $admin = Admin::where('id', $id)->where('user_id', Auth::user()->id)->first();
            if (empty($admin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin does not exist',
                ], 404);
            }
            $admin->delete();
            if ($admin) {
                return response()->json([
                    'success' => true,
                    'message' => 'Admin Deleted',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to Delete Admin',
                ], 500);
            }
        }
    }

}
