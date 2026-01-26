<?php

use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $users = User::all(); // или ->take(10) и т.п.
    $projects = Project::all(); // или ->take(10) и т.п.

    return view('welcome2', [
        'users' => $users,
        'projects' => $projects,
    ]);
});

