<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use Auth;


class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', ];
    protected $hidden = ['password', 'remember_token', ];


    /**
     * 在模型创建之前生成激活令牌
     */
    public static function boot()
    {
        parent::boot();
        // creating 用于监听模型被创建之前的事件
        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /*一个用户拥有多条微博*/
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }


    /*首页上的动态流*/
    public function feed()
    {
        // 取出用户关注的所有用户的ID,并且转换成数组
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        // 给上一步取出的数组尾部加入当前登陆用户的ID
        array_push($user_ids, Auth::user()->id);
        return Status::whereIn('user_id', $user_ids)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }



    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }



    /*关注操作*/
    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }




    /*取消关注操作*/
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    /*判断某人是否关注了某人*/
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
