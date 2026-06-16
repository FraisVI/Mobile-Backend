<?php

namespace App\Http\Controllers\Admin;
use App\Models\Rating;

class RatingController extends AdminController
{
    public function Show() {
        $title = 'Оценки пользователей';
        $ratings = Rating::where('filled', 1)->orderBy('updated_at')->get();

        return view('admin.ratings', compact('title', 'ratings'));
    }
}
