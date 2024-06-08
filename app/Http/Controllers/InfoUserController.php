<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use App\Models\UserPdf;

class InfoUserController extends Controller
{

    public function create()
    {
        return view('laravel-examples/user-profile');
    }

    public function store(Request $request)
    {

        $attributes = request()->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore(Auth::user()->id)],
            'phone'     => ['max:50'],
            'location' => ['max:70'],
            'about_me'    => ['max:150'],
            'job'    => ['max:150'],
            'work_location'    => ['max:150'],
            'examiner_name'    => ['max:150'],
            'age'    => ['max:150'],
        ]);
        if($request->get('email') != Auth::user()->email)
        {
            if(env('IS_DEMO') && Auth::user()->id == 1)
            {
                return redirect()->back()->withErrors(['msg2' => 'You are in a demo version, you can\'t change the email address.']);
                
            }
            
        }
        else{
            $attribute = request()->validate([
                'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore(Auth::user()->id)],
            ]);
        }
        
        
        User::where('id',Auth::user()->id)
        ->update([
            'id' => Auth::user()->id, // 'id' => 'id
            'name'    => $attributes['name'],
            'email' => $attribute['email'],
            'phone'     => $attributes['phone'],
            'location' => $attributes['location'],
            'about_me'    => $attributes["about_me"],
            'job'    => $attributes["job"],
            'work_location'    => $attributes["work_location"],
            'examiner_name'    => $attributes["examiner_name"],
            'age'    => $attributes["age"],
        ]);
        return redirect('/user-profile')->with('success','Profile updated successfully');
    }

    public function index()
    {
        // $users = User::all();
        $users = User::with('pdfs')->paginate(10); // Mengambil data pengguna beserta relasi PDF
        return view('laravel-examples/user-management', compact('users'));
    }

    public function downloadPdf($id)
    {
        $pdf = UserPdf::findOrFail($id);
        $filePath = storage_path('app/public/' . $pdf->file_path);

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }
    }

    public function savePdf(Request $request)
    {
        $user = auth()->user();

        if ($request->hasFile('pdf')) {
            $file = $request->file('pdf');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('pdfs', $fileName);

            UserPdf::create([
                'user_id' => $user->id,
                'file_path' => $filePath,
                'file_name' => $fileName,
            ]);

            return response()->json(['success' => 'PDF berhasil disimpan.']);
        }

        return response()->json(['error' => 'Tidak ada file PDF yang diunggah.'], 400);
    }
}
