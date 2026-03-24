<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        
        return view('admin.admin-staff-list', compact('users'));
    }

    public function update(Request $request)
    {
        
    }
}
