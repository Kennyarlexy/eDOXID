<?php

namespace App\Helpers;

use App\Models\SubmissionRequiredFile;
use App\Models\User;
use App\Models\Verificator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsappHelper
{
    // private $url = "https://wa.gofly.id/api/send-message.php";
    //private $url = 'http://203.24.50.140:8000';
    private $url = 'http://203.24.50.52:43400';
    private $wa_api_key = null;
    private $sender_phone_number = null;
    private $functionHelper = null;

    public function __construct($wa_api_key, $sender_phone_number)
    {
        $this->wa_api_key = $wa_api_key;
        $this->sender_phone_number = $sender_phone_number;
        $this->functionHelper = new FunctionHelper();
    }

    public function sendMessage($receiver_phone_number, $message)
    {
        $base_url = $this->url;

        if (
            $receiver_phone_number == null ||
            $receiver_phone_number == '' ||
            (
                strlen($receiver_phone_number) < 10 ||
                strlen($receiver_phone_number) > 13
            )
        ) {
            // return response()->json([
            //     'status' => false,
            //     'message' => 'Invalid phone number',
            // ]);

            return json_encode([
                'success' => false,
                'message' => 'Invalid phone number',
            ]);
        }

        if ($receiver_phone_number[0] == '0') {
            $receiver_phone_number = '62'.substr($receiver_phone_number, 1);
        }

        $data = [
            'sender' => $this->sender_phone_number,
            'number' => $receiver_phone_number,
            'message' => $message,
        ];

        // $data = [
        //     'receiver' => $receiver_phone_number,
        //     'message' => $message,
        // ];
        //$receiver_phone_number = '6282154036511';

        // $data = [
        //     'jid' => "$receiver_phone_number@s.whatsapp.net",
        //     'type' => 'number',
        //     'message' => [
        //       'text' => $message,
        //     ],
        // ];

        //Log::debug('JSON WhatsappHelper sendMessage: '.json_encode($data));
        try {
            $client = new Client();
            //$req = $client->request('POST', $base_url.'/'.$this->sender_phone_number.'/messages/send', [
            $req = $client->request('POST', $base_url.'/send-message', [
                    'form_params' => $data,
                //'json' => $data,
            ]);

            $response = $req->getBody()->getContents();
            $arrResponse = json_decode($response, true);
            $newResponse = [
                'success' => true,
                'message' => 'Nomor Tidak Dikenali',
            ];

            if (isset($arrResponse['status'])) {
                if ($arrResponse['status'] == true) {
                    $newResponse = [
                        'success' => true,
                        'message' => 'Pesan Berhasil Dikirim',
                    ];
                }
            }

            Log::debug('WhatsappHelper sendMessage: '.$response);

            return json_encode($newResponse);
        } catch (\Exception $e) {
            // Log::debug('WhatsappHelper sendMessage error: '.$e);

            return json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function bulkSchedule($submission)
    {
        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);
        $submission_schedule = $submission->schedule;
        $submission_day = $this->functionHelper->translateDayToIndonesian(Carbon::parse($submission_schedule)->format('w'));
        $submission_date = Carbon::parse($submission_schedule)->format('d');
        $submission_month = $this->functionHelper->translateMonthToIndonesian(Carbon::parse($submission_schedule)->format('n'));
        $submission_year = Carbon::parse($submission_schedule)->format('Y');
        $submission_time = Carbon::parse($submission_schedule)->format('H:i');
        $type = trim($submission->type->name);
        $room = $submission->room;
        $code = null;

        $is_daring = false;
        $text_by = 'LURING';
        if (str_contains($submission->room, 'http')) {
            $is_daring = true;
            $code = explode('/', $submission->room)[3];
            $text_by = 'DARING';
        }

        $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
        $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();

        if ($type == 'Proposal') {
            $type = 'Seminar Proposal';
        } elseif ($type == 'Sidang Hasil') {
            $type = 'Seminar Hasil';
        }

        // message text
        $message = '*UNDANGAN '.strtoupper($type)." SECARA $text_by*";
        $message .= "\nJURUSAN INFORMATIKA";

        if ($type == 'Seminar Hasil') {
            $message .= "\n\nMengundang Bapak dan Ibu dosen tim pembimbing dan penguji untuk hadir pada seminar hasil mahasiswa yang dilaksanakan secara ".ucfirst(strtolower($text_by)).".\n\n";
        } else {
            $message .= "\n\nMengundang Bapak/Ibu/Saudara/i dan Mahasiswa untuk menghadiri $type yang dilaksanakan secara ".ucfirst(strtolower($text_by)).".\n\n";
        }

        $message .= "👨‍🎓 *$student_name / $student_nim*\n\n";
        $message .= "📖 $submission_title\n\n";
        $message .= "📅 $submission_day,  $submission_date $submission_month $submission_year\n\n";
        $message .= "🕑 $submission_time WIB\n\n";

        foreach ($pembimbings as $i => $pembimbing) {
            $index = $i + 1;
            $message .= '📕Pembimbing. '.$index.' : '.$pembimbing->user->name."\n";
        }

        foreach ($pengujis as $i => $penguji) {
            $index = $i + 1;
            $message .= '📒Penguji '.$index.' : '.$penguji->user->name."\n";
        }

        $message .= "\n";

        if ($is_daring) {
            $message .= "💻 Meeting URL: \n";
            $message .= "$room\n\n";
            $message .= "Jika link tidak valid maka gunakan kode: $code\n\n";
        } else {
            $message .= "Tempat: \n";
            $message .= "$room\n\n";
        }

        $message_to_verificator = $message;

        // berikut daftar menu dan link tujuan
        $message_to_verificator .= "Berikut beberapa daftar menu dan link tujuan:\n";
        $message_to_verificator .= '1.Detail: '.route('panel.dosen.pengajuan.show', ['id' => $submission->id, 'type_slug' => $submission->type->slug]);
        $message_to_verificator .= "\n2.Berkas: ".route('panel.dosen.pengajuan.required_files.show', ['id' => $submission->id, 'type_slug' => $submission->type->slug]);
        $message_to_verificator .= "\n3.Penilaian: ".route('panel.dosen.pengajuan.evaluation.show', ['id' => $submission->id, 'type_slug' => $submission->type->slug]);

        $message_to_student = $message;

        $message_thank_you = "\n\nTerimakasih.\n";
        $message_thank_you .= "ttd\n";
        $message_thank_you .= "*Kajur Informatika*\n\n";

        $message_thank_you .= "Powered by\n";
        $message_thank_you .= 'eDOXID INFORMATIKA';

        $message_to_verificator .= $message_thank_you;
        $message_to_student .= $message_thank_you;

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            // send to all verificator
            $verificators = Verificator::where('submission_id', $submission->id)->get();
            foreach ($verificators as $verificator) {
                $reicever_phone_number = $verificator->user->phone_number;
                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
                $response = json_decode($waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                    break;
                }
            }

            // send to student
            if ($is_success == true) {
                $reicever_phone_number = $submission->student->phone_number;
                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
                $response = json_decode($waSendMessage);
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }
        }

        return $is_success;
    }

    public function requestApproval($submission, $to_verificator = null)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        if ($submission->ready_at === null) {
            $submission->ready_at = Carbon::now();
            $submission->save();
        }

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);
        $submission_day = $this->functionHelper->translateDayToIndonesian(Carbon::parse($submission->ready_at)->format('w'));
        $submission_date = Carbon::parse($submission->ready_at)->format('d');
        $submission_month = $this->functionHelper->translateMonthToIndonesian(Carbon::parse($submission->ready_at)->format('n'));
        $submission_year = Carbon::parse($submission->ready_at)->format('Y');
        $submission_time = Carbon::parse($submission->ready_at)->format('H:i');

        $type = trim($submission->type->name);
        $url = route('panel.dosen.pengajuan.show', [$type_slug, $submission_id]);

        $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
        $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();

        // message
        $message = '*PERMOHONAN '.strtoupper($type)."*\n\n";
        $message .= "Kepada Yth.\n";

        foreach ($pembimbings as $i => $pembimbing) {
            $index = $i + 1;
            $message .= '*'.trim($pembimbing->user->name)."* (Pembimbing $index)\n";
        }

        foreach ($pengujis as $i => $penguji) {
            $index = $i + 1;
            $message .= '*'.trim($penguji->user->name)."* (Penguji $index)\n";
        }

        $message .= "\nMahasiswa bimbingan Anda an. *$student_name ($student_nim)* Judul: $submission_title\n";
        $message .= "telah mengajukan *$type* pada $submission_day, $submission_date $submission_month $submission_year ($submission_time) pada Aplikasi Pemberkasan Seminar/Sidang (eDOXID), mohon dapat memberikan PERSETUJUAN pada aplikasi melalui link berikut: $url (login dengan akun SPOTA).\n\n";
        $message .= "Terimakasih.\n\n";
        $message .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            if ($to_verificator === null) {
                $verificators = Verificator::where('submission_id', $submission->id)->get();
                foreach ($verificators as $verificator) {
                    $reicever_phone_number = $verificator->user->phone_number;
                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            } else {
                $reicever_phone_number = $to_verificator->user->phone_number;

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                $response = json_decode($waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                }
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }
        }

        return $is_success;
    }

    public function requestApprovalRevision($submission, $to_verificator = null)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);
        $submission_day = $this->functionHelper->translateDayToIndonesian(Carbon::parse($submission->updated_submit_result_at)->format('w'));
        $submission_date = Carbon::parse($submission->updated_submit_result_at)->format('d');
        $submission_month = $this->functionHelper->translateMonthToIndonesian(Carbon::parse($submission->updated_submit_result_at)->format('n'));
        $submission_year = Carbon::parse($submission->updated_submit_result_at)->format('Y');
        $submission_time = Carbon::parse($submission->updated_submit_result_at)->format('H:i');

        $type = trim($submission->type->name);
        $url = route('panel.dosen.pengajuan.revision.show', [$type_slug, $submission_id]);

        $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
        $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();

        // message
        $message = '*PERMOHONAN PERSETUJUAN FINAL REVISI SEMINAR '.strtoupper($type)."*\n\n";
        $message .= "Kepada Yth.\n";

        foreach ($pembimbings as $i => $pembimbing) {
            $index = $i + 1;
            $message .= '*'.trim($pembimbing->user->name)."* (Pembimbing $index)\n";
        }

        foreach ($pengujis as $i => $penguji) {
            $index = $i + 1;
            $message .= '*'.trim($penguji->user->name)."* (Penguji $index)\n";
        }

        $message .= "\nMahasiswa bimbingan Anda an. *$student_name ($student_nim)* Judul: $submission_title\n";
        $message .= "telah melakukan upload file revisi seminar pada $submission_day, $submission_date $submission_month $submission_year ($submission_time) pada Aplikasi Pemberkasan Seminar/Sidang (eDOXID), mohon dapat memberikan PERSETUJUAN BERKAS FINAL REVISI SEMINAR pada aplikasi melalui link berikut: $url (login dengan akun SPOTA).\n\n";
        $message .= "Terimakasih.\n\n";
        $message .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            if ($to_verificator === null) {
                $verificators = Verificator::where('submission_id', $submission->id)->where('final_status', Verificator::FINAL_STATUS_PENDING)->get();
                foreach ($verificators as $verificator) {
                    $reicever_phone_number = $verificator->user->phone_number;
                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            } else {
                $reicever_phone_number = $to_verificator->user->phone_number;

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                $response = json_decode($waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                }
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }
        }

        return $is_success;
    }

    public function submissionStatusChangeByVerifikator($submission, $verificator, $status)
    {
        $today = Carbon::now();
        $this_day = $this->functionHelper->translateDayToIndonesian($today->format('w'));
        $this_date = $today->format('d');
        $this_month = $this->functionHelper->translateMonthToIndonesian($today->format('n'));
        $this_year = $today->format('Y');
        $this_time = $today->format('H:i');

        $type = trim($submission->type->name);
        $verificator_name = trim($verificator->user->name);
        $verificator_type = trim($verificator->type);
        $verificator_index = 1;

        if ($verificator_type == 'Pembimbing') {
            $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
            foreach ($pembimbings as $i => $pembimbing) {
                if ($pembimbing->user->id == $verificator->user->id) {
                    $verificator_index = $i + 1;
                    break;
                }
            }
        } else {
            $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();
            foreach ($pengujis as $i => $penguji) {
                if ($penguji->user->id == $verificator->user->id) {
                    $verificator_index = $i + 1;
                    break;
                }
            }
        }

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $status_text = '';
        if ($status === 'approved') {
            $status_text = 'menyetujui';
        } else {
            $status_text = 'menolak';
        }

        // message for verificator
        $message_to_verificator = '*PERMOHONAN '.strtoupper($type)."*\n\n";
        $message_to_verificator .= 'Bapak/ibu *'.$verificator_name."* ($verificator_type $verificator_index)\n";
        $message_to_verificator .= "Anda telah $status_text permohonan *$type* an. *$student_name ($student_nim)* Judul: $submission_title\n\n";
        $message_to_verificator .= "Terimakasih.\n";
        $message_to_verificator .= 'eDOXID INFORMATIKA';

        $message_to_student = '';

        // message for student
        if ($verificator->status == Verificator::STATUS_REJECTED) {
            $message_to_student .= '*PERMOHONAN '.strtoupper($type)."*\n\n";
            $message_to_student .= 'Bapak/ibu *'.$verificator_name."* ($verificator_type $verificator_index)\n";
            $message_to_student .= "telah $status_text permohonan *$type* Anda, pada $this_day, $this_date $this_month $this_year (pkl. $this_time).\n\n";

            $message_to_student .= "Harap untuk menghubungi tim untuk menanyakan alasan ditolaknya seminar/sidang, dan setelah itu lakukan upload ulang berkas yang ditolak agar status tim yang menolak berubah menjadi pending. \n\n";

            $message_to_student .= "Terimakasih.\n";
            $message_to_student .= 'eDOXID INFORMATIKA';
        } else {
            $message_to_student .= '*PERMOHONAN '.strtoupper($type)."*\n\n";
            $message_to_student .= 'Bapak/ibu *'.$verificator_name."* ($verificator_type $verificator_index)\n";
            $message_to_student .= "telah $status_text permohonan *$type* Anda, pada $this_day, $this_date $this_month $this_year (pkl. $this_time). Jadwal pelaksanaan $type akan diberitahukan kemudian. Mohon mempersiapkan diri.\n\n";
            $message_to_student .= "Terimakasih.\n";
            $message_to_student .= 'eDOXID INFORMATIKA';
        }

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            // send to verificator
            if ($verificator->user->phone_number) {
                $reicever_phone_number = $verificator->user->phone_number;

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
                $response = json_decode($waSendMessage);
            }

            // send to student
            if ($submission->student->phone_number) {
                $reicever_phone_number = $submission->student->phone_number;
                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
                $response = json_decode($waSendMessage);
            }
        }

        return $is_success;
    }

    public function submissionRevisiStatusChangeByVerifikator($submission, $verificator, $status)
    {
        $today = Carbon::now();
        $this_day = $this->functionHelper->translateDayToIndonesian($today->format('w'));
        $this_date = $today->format('d');
        $this_month = $this->functionHelper->translateMonthToIndonesian($today->format('n'));
        $this_year = $today->format('Y');
        $this_time = $today->format('H:i');

        $type = trim($submission->type->name);
        $verificator_name = trim($verificator->user->name);
        $verificator_type = trim($verificator->type);
        $verificator_index = 1;

        if ($verificator_type == 'Pembimbing') {
            $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
            foreach ($pembimbings as $i => $pembimbing) {
                if ($pembimbing->user->id == $verificator->user->id) {
                    $verificator_index = $i + 1;
                    break;
                }
            }
        } else {
            $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();
            foreach ($pengujis as $i => $penguji) {
                if ($penguji->user->id == $verificator->user->id) {
                    $verificator_index = $i + 1;
                    break;
                }
            }
        }

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $status_text = '';
        if ($status === 'approved') {
            $status_text = 'menyetujui';
        } else {
            $status_text = 'menolak';
        }

        // message for verificator
        $message_to_verificator = '*PERMOHONAN BERKAS REVISI '.strtoupper($type)."*\n\n";
        $message_to_verificator .= 'Bapak/ibu *'.$verificator_name."* ($verificator_type $verificator_index)\n";
        $message_to_verificator .= "Anda telah $status_text permohonan revisi *$type* an. *$student_name ($student_nim)* Judul: $submission_title\n\n";
        $message_to_verificator .= "Terimakasih.\n";
        $message_to_verificator .= 'eDOXID INFORMATIKA';

        // message for student
        $message_to_student = '*PERMOHONAN BERKAS REVISI '.strtoupper($type)."*\n\n";
        $message_to_student .= 'Bapak/ibu *'.$verificator_name."* ($verificator_type $verificator_index)\n";
        $message_to_student .= "telah $status_text permohonan berkas revisi *$type* Anda, pada $this_day, $this_date $this_month $this_year (pkl. $this_time).\n\n";
        $message_to_student .= "Terimakasih.\n";
        $message_to_student .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            // send to verificator
            if ($verificator->user->phone_number) {
                $reicever_phone_number = $verificator->user->phone_number;

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
                $response = json_decode($waSendMessage);
            }

            // send to student
            if ($submission->student->phone_number) {
                $reicever_phone_number = $submission->student->phone_number;
                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
                $response = json_decode($waSendMessage);
            }
        }

        return $is_success;
    }

    public function requestVerificator($submission, $verificator_type)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;
        $ketua_jurusans = User::where('role', User::ROLE_DOSEN)->where('position', 'Ketua Jurusan')->get();

        if ($submission->ready_at === null) {
            $submission->ready_at = Carbon::now();
            $submission->save();
        }

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);
        $submission_day = $this->functionHelper->translateDayToIndonesian(Carbon::parse($submission->ready_at)->format('w'));
        $submission_date = Carbon::parse($submission->ready_at)->format('d');
        $submission_month = $this->functionHelper->translateMonthToIndonesian(Carbon::parse($submission->ready_at)->format('n'));
        $submission_year = Carbon::parse($submission->ready_at)->format('Y');
        $submission_time = Carbon::parse($submission->ready_at)->format('H:i');
        $type = trim($submission->type->name);
        $url = route('panel.dosen.pengajuan.verifikators.show', [$type_slug, $submission_id]);

        $request_verificator_type = null;
        if ($verificator_type == 'Pembimbing') {
            $request_verificator_type = 'Pembimbing';
        } else {
            $request_verificator_type = 'Penguji';
        }

        $message = '*PERMOHONAN '.strtoupper($request_verificator_type)."*\n\n";
        $message .= "Kepada Yth.\n";
        $message .= "Ketua Jurusan\n";

        $message .= "\nMahasiswa an. *$student_name ($student_nim)*\n";
        $message .= "Judul: $submission_title\n\n";
        $message .= "telah mengajukan *$type* pada $submission_day, $submission_date $submission_month $submission_year ($submission_time) pada Aplikasi Pemberkasan Seminar/Sidang (eDOXID), mohon dapat menentukan ".strtoupper($request_verificator_type)." pada aplikasi melalui link berikut: $url (login dengan akun SPOTA).\n\n";

        $message .= "Terimakasih.\n";
        $message .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            // send to ketua jurusan
            foreach ($ketua_jurusans as $ketua_jurusan) {
                $reicever_phone_number = $ketua_jurusan->phone_number;

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                $response = json_decode($waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                    break;
                }
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }
        }

        return $is_success;
    }

    // reminder
    public function result_status_not_changed($submission, $to_verificator = null)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $submission_day = $this->functionHelper->translateDayToIndonesian(Carbon::parse($submission->schedule)->format('w'));
        $submission_date = Carbon::parse($submission->schedule)->format('d');
        $submission_month = $this->functionHelper->translateMonthToIndonesian(Carbon::parse($submission->schedule)->format('n'));
        $submission_year = Carbon::parse($submission->schedule)->format('Y');
        $submission_time = Carbon::parse($submission->schedule)->format('H:i');

        $type = trim($submission->type->name);
        $url = route('panel.dosen.pengajuan.make-result.show', [$type_slug, $submission_id]);

        $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
        $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();

        // message
        $message = '*PERMOHONAN HASIL KEPUTUSAN SEMINAR '.strtoupper($type)."*\n\n";
        $message .= "Kepada Yth.\n";

        foreach ($pembimbings as $i => $pembimbing) {
            $index = $i + 1;
            $message .= '*'.trim($pembimbing->user->name)."* (Pembimbing $index)\n";
        }

        foreach ($pengujis as $i => $penguji) {
            $index = $i + 1;
            $message .= '*'.trim($penguji->user->name)."* (Penguji $index)\n";
        }

        $message .= "\nMahasiswa bimbingan Anda an. *$student_name ($student_nim)* Judul: $submission_title\n";
        $message .= "telah selesai melaksanakan seminar pada $submission_day, $submission_date $submission_month $submission_year ($submission_time) pada Aplikasi Pemberkasan Seminar/Sidang (eDOXID), mohon dapat memberikan HASIL KEPUTUSAN SEMINAR pada aplikasi melalui link berikut: $url (login dengan akun SPOTA).\n\n";
        $message .= "Hal ini diperlukan agar mahasiswa bimbingan Anda dapat melanjutkan ke tahap selanjutnya.\n\n";
        $message .= "Terimakasih.\n\n";
        $message .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            if ($to_verificator === null) {
                $verificators = Verificator::where('submission_id', $submission->id)->where('final_status', Verificator::FINAL_STATUS_PENDING)->get();
                foreach ($verificators as $verificator) {
                    $reicever_phone_number = $verificator->user->phone_number;
                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            } else {
                $reicever_phone_number = $to_verificator->user->phone_number;

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                $response = json_decode($waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                }
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }
        }

        return $is_success;
    }

    public function verificator_final_status_not_changed($submission, $to_verificator = null)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);
        $submission_day = $this->functionHelper->translateDayToIndonesian(Carbon::parse($submission->updated_submit_result_at)->format('w'));
        $submission_date = Carbon::parse($submission->updated_submit_result_at)->format('d');
        $submission_month = $this->functionHelper->translateMonthToIndonesian(Carbon::parse($submission->updated_submit_result_at)->format('n'));
        $submission_year = Carbon::parse($submission->updated_submit_result_at)->format('Y');
        $submission_time = Carbon::parse($submission->updated_submit_result_at)->format('H:i');

        $type = trim($submission->type->name);
        $url = route('panel.dosen.pengajuan.revision.show', [$type_slug, $submission_id]);

        $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
        $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();

        // message
        $message = '*PERMOHONAN PERSETUJUAN FINAL REVISI SEMINAR '.strtoupper($type)."*\n\n";
        $message .= "Kepada Yth.\n";

        foreach ($pembimbings as $i => $pembimbing) {
            $index = $i + 1;
            $message .= '*'.trim($pembimbing->user->name)."* (Pembimbing $index)\n";
        }

        foreach ($pengujis as $i => $penguji) {
            $index = $i + 1;
            $message .= '*'.trim($penguji->user->name)."* (Penguji $index)\n";
        }

        $message .= "\nMahasiswa bimbingan Anda an. *$student_name ($student_nim)* Judul: $submission_title\n";
        $message .= "telah melakukan upload file revisi seminar pada $submission_day, $submission_date $submission_month $submission_year ($submission_time) pada Aplikasi Pemberkasan Seminar/Sidang (eDOXID), mohon dapat memberikan PERSETUJUAN BERKAS FINAL REVISI SEMINAR pada aplikasi melalui link berikut: $url (login dengan akun SPOTA).\n\n";
        $message .= "Hal ini diperlukan agar mahasiswa bimbingan Anda dapat melanjutkan ke tahap selanjutnya.\n\n";
        $message .= "Terimakasih.\n\n";
        $message .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            if ($to_verificator === null) {
                $verificators = Verificator::where('submission_id', $submission->id)->where('final_status', Verificator::FINAL_STATUS_PENDING)->get();
                foreach ($verificators as $verificator) {
                    $reicever_phone_number = $verificator->user->phone_number;
                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            } else {
                $reicever_phone_number = $to_verificator->user->phone_number;

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                $response = json_decode($waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                }
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }
        }

        return $is_success;
    }

    public function submission_files_status_changed_by_staff_administrator($submission, $rejected_note)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $type = trim($submission->type->name);
        $url = route('panel.mahasiswa.pengajuan.required_files.show', [$type_slug, $submission_id]);

        // message
        $message = '*INFORMASI PROSES VALIDASI BERKAS SYARAT SEMINAR '.strtoupper($type)."*\n\n";
        $message .= "\nMahasiswa an. *$student_name ($student_nim)* Judul: $submission_title\n";

        $message .= "berikut adalah status validasi berkas seminar Anda yang telah ditolak: \n\n";

        $submission_files = SubmissionRequiredFile::where('submission_id', $submission->id)->where('status', 'rejected')->get();

        $index = 1;
        foreach ($submission_files as $submission_file) {
            $message .= '*'.$index.'. '.$submission_file->required_file->name."*\n";
            ++$index;
        }

        if ($submission->student->signature_status === 'rejected') {
            $message .= '*'.$index.". Tandatangan digital.*\n";
            ++$index;
        }

        $message .= "\n";
        $message .= "Harap lakukan perbaikan berkas dan upload kembali berkas Anda pada Aplikasi Pemberkasan Sidang (eDOXID) eDOXID melalui link berikut: $url .\n";

        $url = route('panel.mahasiswa.pengajuan.discussions.show', [$type_slug, $submission_id]);
        $message .= "\nBerikut catatan yang diberikan oleh bagian administrasi untuk anda: *$rejected_note*";
        $message .= "\n\nCatatan ini dapat anda lihat detailnya melalui link berikut: $url";

        $message .= "\n\nTerimakasih.\n";
        $message .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }

            if ($is_success == true) {
                $reicever_phone_number = $submission->student->phone_number;

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                $response = json_decode($waSendMessage);

                Log::debug('test123: '.$waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                }
            }
        }

        return $is_success;
    }

    public function submission_files_pending_by_student($submission)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $type = trim($submission->type->name);
        $url = route('panel.bagian-administrasi.pengajuan.required_files.show', [$type_slug, $submission_id]);

        // message
        $message = '*INFORMASI PROSES VALIDASI BERKAS SYARAT SEMINAR '.strtoupper($type)."*\n\n";
        $message .= "\nMahasiswa an. *$student_name ($student_nim)* Judul: $submission_title\n";

        $message .= "berikut adalah status validasi berkas seminar mahasiswa yang saat ini masih berstatus pending atau menunggu proses validasi: \n\n";

        $submission_files = SubmissionRequiredFile::where('submission_id', $submission->id)->where('status', 'pending')->where('url', '!=', null)->get();

        $index = 1;
        foreach ($submission_files as $submission_file) {
            $message .= '*'.$index.'. '.$submission_file->required_file->name."*\n";
            ++$index;
        }

        $message .= "\n\n";
        $message .= "Harap lakukan pengecekan terhadap berkas mahasiswa pada Aplikasi Pemberkasan Sidang (eDOXID) eDOXID melalui link berikut: $url .\n\n";
        $message .= "Terimakasih.\n\n";
        $message .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }
        }

        return $is_success;
    }

    public function submission_scheduler_reminder($submission)
    {
        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);
        $submission_schedule = $submission->schedule;
        $submission_day = $this->functionHelper->translateDayToIndonesian(Carbon::parse($submission_schedule)->format('w'));
        $submission_date = Carbon::parse($submission_schedule)->format('d');
        $submission_month = $this->functionHelper->translateMonthToIndonesian(Carbon::parse($submission_schedule)->format('n'));
        $submission_year = Carbon::parse($submission_schedule)->format('Y');
        $submission_time = Carbon::parse($submission_schedule)->format('H:i');
        $type = trim($submission->type->name);
        $room = $submission->room;
        $code = null;

        $is_daring = false;
        $text_by = 'LURING';
        if (str_contains($submission->room, 'http')) {
            $is_daring = true;
            $code = explode('/', $submission->room)[3];
            $text_by = 'DARING';
        }

        $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
        $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();

        if ($type == 'Proposal') {
            $type = 'Seminar Proposal';
        } elseif ($type == 'Sidang Hasil') {
            $type = 'Seminar Hasil';
        }

        // bandingin jam schedule submission dengan jam sekarang
        $now = Carbon::now();

        $different_time = $now->diffInMinutes(Carbon::parse($submission_schedule));
        $different_time_text = "$different_time MENIT";

        if ($different_time > 1440) {
            $different_time = $now->diffInDays(Carbon::parse($submission_schedule));
            $different_time_text = "$different_time HARI";
        } elseif ($different_time > 60 && $different_time < 1440) {
            $different_time = $now->diffInHours(Carbon::parse($submission_schedule));
            $different_time_text = "$different_time JAM";
        }

        $time_period_message = '*INFORMASI PENGINGAT JADWAL SEMINAR '.$different_time_text." SEBELUM PELAKSANAAN*\n\n";

        // message text
        $message = $time_period_message;
        $message .= '*UNDANGAN '.strtoupper($type)." SECARA $text_by*";
        $message .= "\nJURUSAN INFORMATIKA";

        if ($type == 'Seminar Hasil') {
            $message .= "\n\nMengundang Bapak dan Ibu dosen tim pembimbing dan penguji untuk hadir pada seminar hasil mahasiswa yang dilaksanakan secara ".ucfirst(strtolower($text_by)).".\n\n";
        } else {
            $message .= "\n\nMengundang Bapak/Ibu/Saudara/i dan Mahasiswa untuk menghadiri $type yang dilaksanakan secara ".ucfirst(strtolower($text_by)).".\n\n";
        }

        $message .= "👨‍🎓 *$student_name / $student_nim*\n\n";
        $message .= "📖 $submission_title\n\n";
        $message .= "📅 $submission_day,  $submission_date $submission_month $submission_year\n\n";
        $message .= "🕑 $submission_time WIB\n\n";

        foreach ($pembimbings as $i => $pembimbing) {
            $index = $i + 1;
            $message .= '📕Pembimbing. '.$index.' : '.$pembimbing->user->name."\n";
        }

        foreach ($pengujis as $i => $penguji) {
            $index = $i + 1;
            $message .= '📒Penguji '.$index.' : '.$penguji->user->name."\n";
        }

        $message .= "\n";

        if ($is_daring) {
            $message .= "💻 Meeting URL: \n";
            $message .= "$room\n\n";
            $message .= "Jika link tidak valid maka gunakan kode: $code\n\n";
        } else {
            $message .= "Tempat: \n";
            $message .= "$room\n\n";
        }

        $message_to_verificator = $message;

        // berikut daftar menu dan link tujuan
        $message_to_verificator .= "Berikut beberapa daftar menu dan link tujuan:\n";
        $message_to_verificator .= '1.Detail: '.route('panel.dosen.pengajuan.show', ['id' => $submission->id, 'type_slug' => $submission->type->slug]);
        $message_to_verificator .= "\n2.Berkas: ".route('panel.dosen.pengajuan.required_files.show', ['id' => $submission->id, 'type_slug' => $submission->type->slug]);
        $message_to_verificator .= "\n3.Penilaian: ".route('panel.dosen.pengajuan.evaluation.show', ['id' => $submission->id, 'type_slug' => $submission->type->slug]);

        $message_to_student = $message;

        $message_thank_you = "\n\nTerimakasih.\n";
        $message_thank_you .= "ttd\n";
        $message_thank_you .= "*Kajur Informatika*\n\n";

        $message_thank_you .= "Powered by\n";
        $message_thank_you .= 'eDOXID INFORMATIKA';

        $message_to_verificator .= $message_thank_you;
        $message_to_student .= $message_thank_you;

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            // send to all verificator
            $verificators = Verificator::where('submission_id', $submission->id)->get();
            foreach ($verificators as $verificator) {
                $reicever_phone_number = $verificator->user->phone_number;
                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
                $response = json_decode($waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                    break;
                }
            }

            // send to student
            if ($is_success == true) {
                $reicever_phone_number = $submission->student->phone_number;
                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
                $response = json_decode($waSendMessage);
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            if ($is_success == true) {
                foreach ($bagian_administrasis as $bagian_administrasi) {
                    $reicever_phone_number = $bagian_administrasi->phone_number;

                    if ($reicever_phone_number == null) {
                        continue;
                    }

                    $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
                    $response = json_decode($waSendMessage);

                    if ($response->success == false) {
                        $is_success = false;
                        break;
                    }
                }
            }
        }
    }

    public function notificationToRejectedVerificatorThatFileHasBeenReuploaded($submission)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $type = trim($submission->type->name);
        $url = route('panel.dosen.pengajuan.required_files.show', ['id' => $submission_id, 'type_slug' => $type_slug]);

        // message
        $message = '*INFORMASI PROSES VALIDASI BERKAS SYARAT SEMINAR '.strtoupper($type)."*\n\n";
        $message .= "\nMahasiswa an. *$student_name ($student_nim)* Judul: $submission_title\n";

        $message .= "berikut adalah status validasi berkas seminar mahasiswa ditolak oleh anda telah direupload dan telah berstatus pending atau menunggu persetujuan anda: \n\n";

        $submission_files = SubmissionRequiredFile::where('submission_id', $submission->id)->where('status', 'pending')->where('url', '!=', null)->whereHas('required_file', function ($q) {
            $q->where('is_verificator_can_edit', 1);
        })->get();

        $index = 1;
        foreach ($submission_files as $submission_file) {
            $message .= '*'.$index.'. '.$submission_file->required_file->name."*\n";
            ++$index;
        }

        $message .= "\n\n";
        $message .= "Harap lakukan pengecekan terhadap berkas mahasiswa pada Aplikasi Pemberkasan Sidang (eDOXID) eDOXID melalui link berikut: $url .\n\n";
        $message .= "Terimakasih.\n\n";
        $message .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
            $response = json_decode($waSendMessage);

            if ($response->success == false) {
                $is_success = false;
            }
        } else {
            $verificator_whose_rejecteds = Verificator::where('submission_id', $submission->id)->where('status', Verificator::STATUS_REJECTED)->get();

            foreach ($verificator_whose_rejecteds as $verificator_whose_rejected) {
                $reicever_phone_number = $verificator_whose_rejected->user->phone_number;

                if ($reicever_phone_number == null) {
                    continue;
                }

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message);
                $response = json_decode($waSendMessage);

                if ($response->success == false) {
                    $is_success = false;
                    break;
                }
            }
        }

        return $is_success;
    }

    // send message to verificator that has new discussions
    public function student_add_new_discussions($submission, $created_at)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $type = trim($submission->type->name);
        $url = route('panel.dosen.pengajuan.discussions.show', ['id' => $submission_id, 'type_slug' => $type_slug]);

        // message for verificator
        $message_to_verificator = "Anda telah menerima diskusi baru pada: $created_at untuk permohonan *$type* an. *$student_name ($student_nim)* Judul: $submission_title\n\n";
        $message_to_verificator .= "Detail diskusi dapat anda lihat pada Aplikasi Pemberkasan Sidang (eDOXID) eDOXID melalui link berikut: $url\n\n";
        $message_to_verificator .= "Terimakasih.\n";
        $message_to_verificator .= 'eDOXID INFORMATIKA';

        $url = route('panel.mahasiswa.pengajuan.discussions.show', ['id' => $submission_id, 'type_slug' => $type_slug]);

        // message to student
        $message_to_student = "Anda telah melakukan diskusi baru pada permohonan *$type* dengan judul: $submission_title\n";
        $message_to_student .= "Diskusi yang baru saja anda lakukan pada: $created_at akan otomatis terkirim ke tim dosen anda.\n\n";
        $message_to_student .= "Terimakasih.\n";
        $message_to_student .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
        } else {
            $verificators = Verificator::where('submission_id', $submission->id)->get();

            foreach ($verificators as $verificator) {
                $reicever_phone_number = $verificator->user->phone_number;

                if ($reicever_phone_number == null) {
                    continue;
                }

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            foreach ($bagian_administrasis as $bagian_administrasi) {
                $reicever_phone_number = $bagian_administrasi->phone_number;

                if ($reicever_phone_number == null) {
                    continue;
                }

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            }

            // message to student
            $reicever_phone_number = $submission->student->phone_number;
            if ($reicever_phone_number == null) {
                return true;
            }

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
        }

        return $is_success;
    }

    // send message to student and other verificator that has new discussions
    public function verificator_add_new_discussions($submission, $verificator_user_id, $created_at)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $verificator = Verificator::where('submission_id', $submission->id)->where('user_id', $verificator_user_id)->first();

        $type = trim($submission->type->name);

        $url = route('panel.mahasiswa.pengajuan.discussions.show', ['id' => $submission_id, 'type_slug' => $type_slug]);

        $message_to_student = 'Halo, '.$student_name.' ('.$student_nim.")\n";
        $message_to_student .= "Anda telah menerima diskusi baru pada: $created_at untuk permohonan *$type* dengan Judul: $submission_title\n\n";
        $message_to_student .= "Anda dapat melihat diskusi tersebut pada Aplikasi Pemberkasan Sidang (eDOXID) eDOXID melalui link berikut: $url\n\n";
        $message_to_student .= "Terimakasih.\n";
        $message_to_student .= 'eDOXID INFORMATIKA';

        $url = route('panel.dosen.pengajuan.discussions.show', ['id' => $submission_id, 'type_slug' => $type_slug]);

        $verificator_name = trim($verificator->user->name);
        $verificator_type = trim($verificator->type);
        $verificator_index = 1;

        if ($verificator_type == 'Pembimbing') {
            $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
            foreach ($pembimbings as $i => $pembimbing) {
                if ($pembimbing->user->id == $verificator->user->id) {
                    $verificator_index = $i + 1;
                    break;
                }
            }
        } else {
            $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();
            foreach ($pengujis as $i => $penguji) {
                if ($penguji->user->id == $verificator->user->id) {
                    $verificator_index = $i + 1;
                    break;
                }
            }
        }

        $message_to_verificator = 'Bapak/ibu *'.$verificator_name."* ($verificator_type $verificator_index)\n";
        $message_to_verificator .= "Anda telah mengirimkan diskusi pada: $created_at untuk permohonan *$type* an. *$student_name ($student_nim)* Judul: $submission_title\n\n";
        $message_to_verificator .= "diskusi yang anda baru saja lakukan akan otomatis terkirim kepada mahasiswa dan tim dosen lainnya yang bersangkutan.\n\n";
        $message_to_verificator .= "Terimakasih.\n";
        $message_to_verificator .= 'eDOXID INFORMATIKA';

        // message for other_verificator
        $message_to_other_verificator = "Anda telah menerima diskusi baru pada: $created_at untuk permohonan *$type* an. *$student_name ($student_nim)* Judul: $submission_title\n\n";
        $message_to_other_verificator .= "Untuk detail diskusi dapat anda lihat pada Aplikasi Pemberkasan Sidang (eDOXID) eDOXID melalui link berikut: $url\n\n";
        $message_to_other_verificator .= "Terimakasih.\n";
        $message_to_other_verificator .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_other_verificator);
            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
        } else {
            // message to verificator
            $reicever_phone_number = $verificator->user->phone_number;
            if ($reicever_phone_number !== null) {
                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            }

            // message to other verificator
            $other_verificators = Verificator::where('submission_id', $submission->id)->where('user_id', '!=', $verificator_user_id)->get();
            foreach ($other_verificators as $other_verificator) {
                $reicever_phone_number = $other_verificator->user->phone_number;

                if ($reicever_phone_number == null) {
                    continue;
                }

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_other_verificator);
            }

            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            foreach ($bagian_administrasis as $bagian_administrasi) {
                $reicever_phone_number = $bagian_administrasi->phone_number;

                if ($reicever_phone_number == null) {
                    continue;
                }

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_other_verificator);
            }

            // message to student
            $reicever_phone_number = $submission->student->phone_number;
            if ($reicever_phone_number !== null) {
                $this->sendMessage($reicever_phone_number, $message_to_student);
            }
        }

        return $is_success;
    }

    // send message to student and verificator that has new discussions from bagian administrasi
    public function bagian_administrasi_add_new_discussions($submission, $created_at)
    {
        $type_slug = $submission->type->slug;
        $submission_id = $submission->id;

        $student_name = trim($submission->student->name);
        $student_nim = trim($submission->student->unique_id);
        $submission_title = trim($submission->title);

        $type = trim($submission->type->name);

        $url = route('panel.mahasiswa.pengajuan.discussions.show', ['id' => $submission_id, 'type_slug' => $type_slug]);

        $message_to_bagian_administrasi = "Anda telah melakukan diskusi baru pada permohonan *$type* dengan judul: $submission_title\n";
        $message_to_bagian_administrasi .= "Diskusi yang baru saja anda lakukan pada: $created_at akan otomatis terkirim ke mahasiswa dan tim dosennya.\n\n";
        $message_to_bagian_administrasi .= "Terimakasih.\n";
        $message_to_bagian_administrasi .= 'eDOXID INFORMATIKA';

        $message_to_student = 'Halo, '.$student_name.' ('.$student_nim.")\n";
        $message_to_student .= "Anda telah menerima diskusi baru pada: $created_at untuk permohonan *$type* dengan Judul: $submission_title\n\n";
        $message_to_student .= "Anda dapat melihat diskusi tersebut pada Aplikasi Pemberkasan Sidang (eDOXID) eDOXID melalui link berikut: $url\n\n";
        $message_to_student .= "Terimakasih.\n";
        $message_to_student .= 'eDOXID INFORMATIKA';

        $url = route('panel.dosen.pengajuan.discussions.show', ['id' => $submission_id, 'type_slug' => $type_slug]);

        // message to student and verificator
        $message_to_verificator = "Anda telah menerima diskusi baru pada: $created_at untuk permohonan *$type* an. *$student_name ($student_nim)* Judul: $submission_title\n\n";
        $message_to_verificator .= "Untuk detail diskusi dapat anda lihat pada Aplikasi Pemberkasan Sidang (eDOXID) eDOXID melalui link berikut: $url\n\n";
        $message_to_verificator .= "Terimakasih.\n";
        $message_to_verificator .= 'eDOXID INFORMATIKA';

        // send message
        $is_success = true;
        if (getenv('APP_ENV') !== 'production') {
            $reicever_phone_number = '6283149149457';

            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_bagian_administrasi);
            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_student);
        } else {
            // send to bagian administrasi
            $bagian_administrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->get();
            foreach ($bagian_administrasis as $bagian_administrasi) {
                $reicever_phone_number = $bagian_administrasi->phone_number;

                if ($reicever_phone_number == null) {
                    continue;
                }

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_bagian_administrasi);
            }

            // message to verificator
            $verificators = Verificator::where('submission_id', $submission->id)->get();
            foreach ($verificators as $other_verificator) {
                $reicever_phone_number = $other_verificator->user->phone_number;

                if ($reicever_phone_number == null) {
                    continue;
                }

                $waSendMessage = $this->sendMessage($reicever_phone_number, $message_to_verificator);
            }

            // message to student
            $reicever_phone_number = $submission->student->phone_number;
            if ($reicever_phone_number !== null) {
                $this->sendMessage($reicever_phone_number, $message_to_student);
            }
        }

        return $is_success;
    }
}
