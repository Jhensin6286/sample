<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Mail;
class UsersController extends Controller
{
    public function __construct(){
        $this->middleware('auth', [
            'except' => ['show' , 'create' , 'store','index']
        ]);


        $this->middleware('guset',[
            'only' => ['create']
        ]);
    }
    public function create(){
        return view('users.create');
    }

    public function show(User $user){
        return view('users.show',compact('user'));
    }

    public function store(Request $request)
    {
        // 获取表单参数
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        // 把资料写进数据库
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
    }


    /*
    编辑
    */
    public function edit(User $user){
        $this->authorize('update', $user);
        return view('users.edit',compact('user'));
    }

    /*
    更新
    */
    public function update(User $user, Request $request){

        $this->validate($request, [
            'name' => 'required|max:50',
            'password' =>'nullable|confirmed|min:6']);
        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        session()->flash('success','个人资料更新成功!');

        return redirect()->route('users.show', $user->id);
    }

    /*
    用户列表
    */
    public function index(){
        $users = User::paginate(10);
        return view('users.index',compact('users'));
    }

    /*删除*/
    public function destroy(User $user){
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success','成功删除用户！');
        return back();
    }


    protected function sendEmailConfirmationTo($user){
        $view = 'emails.confirm';
        $data = compact('user');


        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view,$data,function ($message) use  ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

}
