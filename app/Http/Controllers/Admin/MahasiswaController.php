<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\GoogleDriveHelper;
use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\MahasiswaTypeAccessPermission;
use App\Models\Submission;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MahasiswaController extends Controller
{
    private $googleDriveHelper;

    public  function __construct()
    {
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mahasiswas = User::where('role', User::ROLE_MAHASISWA)->orderBy('id', 'desc')->get();
        return view('panel.admin.users.mahasiswa.index', compact('mahasiswas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $types = Type::all();
        $dosens = User::where('role', User::ROLE_DOSEN)->get();
        return view('panel.admin.users.mahasiswa.create', compact('dosens', 'types'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $mahasiswa = User::where('id', $id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();
        $submissions = Submission::where('student_id', $mahasiswa->id)->get();
        return view('panel.admin.users.mahasiswa.show', compact('mahasiswa', 'submissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $types = Type::all();
        $mahasiswa = User::where('id', $id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();
        $dosens = User::where('role', User::ROLE_DOSEN)->get();
        return view('panel.admin.users.mahasiswa.edit', compact('mahasiswa', 'dosens', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $mahasiswa = User::where('id', $id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();

        $request->validate([
            'status' => 'required',
            'status_tandatangan' => 'required'
        ]);

        if (!($request->status == 'active' || $request->status == 'inactive')) {
            return redirect()->back()->with('failed', 'Status tidak valid');
        }

        if (!($request->status_tandatangan == User::SIGNATURE_APPROVED || $request->status_tandatangan == User::SIGNATURE_REJECTED || $request->status_tandatangan == User::SIGNATURE_PENDING)) {
            return redirect()->back()->with('failed', 'Status tanda tangan tidak valid');
        }

        if ($mahasiswa->google_drive_folder_id === null) {
            // create new folder by student nim - name
            $createFolder = $this->googleDriveHelper->createNewFolder("$mahasiswa->unique_id - $mahasiswa->name");

            // update the google drive folder path id to student
            $mahasiswa->google_drive_folder_id = $createFolder['path'];
            $mahasiswa->save();
        }

        $signature_image = $mahasiswa->signature_image;
        $signatre_status = $request->status_tandatangan;
        if ($request->has('tandatangan_image')) {
            $request->validate([
                'tandatangan_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($mahasiswa->signature_image != null) {
                Storage::disk('public')->delete('signatures/' . $mahasiswa->signature_image);
            }
            $signature_image = Str::slug($mahasiswa->name) . '-' . time() . '.' . $request->tandatangan_image->getClientOriginalExtension();
            $request->tandatangan_image->storeAs('signatures', $signature_image, 'public');
        }

        $mahasiswa->update([
            'status' => $request->status,
            'signature_image' => $signature_image,
            'signature_status' => $signatre_status,
        ]);

        if ($request->mahasiswa_type_access_permissions) {
            $mahasiswa->mahasiswa_type_access_permissions()->detach();

            foreach($request->mahasiswa_type_access_permissions as $key => $mahasiswa_type_access_permission) {
                MahasiswaTypeAccessPermission::create([
                    'mahasiswa_id' => $mahasiswa->id,
                    'type_id' => $key
                ]);
            }
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui akun Mahasiswa dengan nilai id: ' . $mahasiswa->id,
        ]);

        return redirect()->back()->with('success', 'Mahasiswa berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $mahasiswa = User::where('id', $id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();

        if ($mahasiswa->profile_image) {
            Storage::disk('public')->delete('profiles/' . $mahasiswa->profile_image);
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus akun Mahasiswa dengan nilai id: ' . $mahasiswa->id,
        ]);

        $mahasiswa->delete();
        return redirect()->back()->with('success', 'Mahasiswa berhasil dihapus');
    }
}
