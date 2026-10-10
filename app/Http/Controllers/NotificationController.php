<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index() {
        $notifications = Notification::where('to_user_id', auth()->user()->id)->orderBy('id', 'desc')->get();
        return view('panel.notifikasi', compact('notifications'));
    }
}
