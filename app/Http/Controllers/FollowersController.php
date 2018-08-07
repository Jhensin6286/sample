<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FollowersController extends Controller
{
   public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(User $user)
    {
        // 用户对本身不含有辞功能
        if (Auth::user()->id === $user->id) {
            return redirect('/');
        }

        // 判断是否关注了该用户
        if (!Auth::user()->isFollowing($user->id)) {
            Auth::user()->follow($user->id);
        }

        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {
        // 用户对本身不含有辞功能
        if (Auth::user()->id === $user->id) {
            return redirect('/');
        }

        // 判断是否关注了该用户
        if (Auth::user()->isFollowing($user->id)) {
            Auth::user()->unfollow($user->id);
        }

        return redirect()->route('users.show', $user->id);
    }
}