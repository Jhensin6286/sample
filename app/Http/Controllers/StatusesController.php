<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Status;
use Auth;

class StatusesController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function store (Request $request){
        $this->validate($request,[
            'content' => 'required|max:140'
        ]);

        Auth::user()->statues()->create([
            'content' => $request['content']
        ]);
        return redirect()->back();
    }


    public function destroy(Status $status){
        /*使用的是隐性路由模型绑定功能，Laravel 会自动查找并注入对应 ID 的实例对象 $status，如果找不到就会抛出异常。*/

        // 删除授权检测，不过报403
        $this->authorize('destroy', $status);
        // 调用Eloquent删除方法
        $status->delete();
        session()->flash('success', '微博已被成功删除！');
         return redirect()->back();
    }


}
