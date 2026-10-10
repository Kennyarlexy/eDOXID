<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\User;
use App\Models\Verificator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $rules = [
            'unique_id' => 'required',
            'nama_lengkap' => 'required',
            'email' => 'required',
            'no_hp' => 'required',
            'foto' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::where('unique_id', $request->unique_id)->first();
        if ($user === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $user->update([
            'name' => $request->nama_lengkap,
            'email' => $request->email,
            'phone_number' => $request->no_hp,
            'profile_image' => $request->foto,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil perbarui data pengguna',
        ], 200);
    }

    public function index(Request $request)
    {
        $tokenFromEnv = getenv('API_TOKEN');
        $tokenFromRequest = $request->token ? $request->token : '';

        if ($tokenFromEnv != $tokenFromRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token',
            ], 400);
        }

        $role = $request->role;

        if ($role && !($role == 'dosen' || $role == 'mahasiswa')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role tidak valid',
            ], 400);
        }

        $q_users = User::query();

        if (!$role) {
            $q_users->where('role', '!=', 'admin')->where('role', '!=', 'bagian administrasi');
        } else {
            $q_users->where('role', $role);
        }

        $users = $q_users->get();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->id,
                'unique_id' => $user->unique_id,
                'role' => $user->role,
                'nama_lengkap' => $user->name,
                'email' => $user->email,
                'no_hp' => $user->phone_number,
                'ttd' => $user->signature_image ? asset('/storage/signatures/'.$user->signature_image) : '',
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    public function getDataTAByNim($nim)
    {
        $user = User::where('unique_id', $nim)->first();
        if ($user == null) {
            return response()->json([
                'status' => 'failed',
                'msg' => 'Data mahasiswa tidak ditemukan',
            ], 200);
        }

        $idMhs = $user->id;
        $dataSubmission = Submission::where('student_id', $idMhs)->orderBy('id', 'desc')->first();

        if ($dataSubmission == null) {
            return response()->json([
                'status' => 'failed',
                'msg' => 'Data tugas akhir tidak ditemukan',
            ], 200);
        }

        $dataVerifikator = Verificator::leftJoin('users', 'verificators.user_id', '=', 'users.id')->where('submission_id', $dataSubmission->id)->get();

        $listPembimbing = [];
        $listPenguji = [];
        foreach ($dataVerifikator as $key => $val) {
            if ($val->type == 'Pembimbing') {
                array_push($listPembimbing, [
                    'nama' => $val->name,
                    'nip' => $val->unique_id,
                ]);
            }

            if ($val->type == 'Penguji') {
                array_push($listPenguji, [
                    'nama' => $val->name,
                    'nip' => $val->unique_id,
                ]);
            }
        }

        $dataFinal = [
            'judul' => $dataSubmission->title,
            'mahasiswa' => [
                'nim' => $user->unique_id,
                'nama' => $user->name,
            ],
            'pembimbing' => $listPembimbing,
            'penguji' => $listPenguji,
            ];

        return response()->json([
            'status' => 'success',
            'data' => $dataFinal,
        ], 200);
    }
}
