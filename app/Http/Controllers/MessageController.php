<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Message;
use App\Events\MessageSent;
use App\Events\PrivateMessageSent;

use Auth;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function fetchMessages()
    {
        return Message::with('user')->whereReceiverId(null)->get();
    }
    public function sendMessage(Request $request)
    {
        // $message=auth()->user()->messages()->create(['message'=>$request->message]);
        // broadcast(new MessageSent(auth()->user(),$message->load('user')))->toOthers();
        // return response(['status'=>'Message sent successfully','message'=>$message]);
        // 
         if(request()->has('file')){

            $file = $request->file('file');
            $filename = time() . $file->getClientOriginalName();
            $file->move('images', $filename);
            

            // $filename = request('file')->store('chat');
            $message=Message::create([
                'user_id' => request()->user()->id,
                'image' => $filename,
            ]);
        }else{
            $message = auth()->user()->messages()->create(['message' => $request->message]);

        }


        broadcast(new MessageSent(auth()->user(),$message->load('user')))->toOthers();
        
        return response(['status'=>'Message sent successfully','message'=>$message]);

    }


    //read private message
    public function privateMessages(User $user)
    {
        $privateCommunication= Message::with('user')
        ->where(['user_id'=> auth()->id(), 'receiver_id'=> $user->id])
        ->orWhere(function($query) use($user){
            $query->where(['user_id' => $user->id, 'receiver_id' => auth()->id()]);
        })
        ->get();

        return $privateCommunication;
    }



    public function sendPrivateMessage(Request $request,User $user)
    {   

        $input = $request->all();
        $input['receiver_id'] = $user->id;

        $message=auth()->user()->messages()->create($input);

        broadcast(new PrivateMessageSent($message->load('user')))->toOthers(); 
       
        return response(['status'=>'Private message sent successfully','message'=>$message]);

    }
   
}