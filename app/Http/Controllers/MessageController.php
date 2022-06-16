<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\ChatStoreRequest;
use App\Models\User;
use App\Models\UserChat;

class MessageController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.messages';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('messages', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        session()->forget('message_setting');
        session()->forget('pusher_settings');

        abort_403(message_setting()->allow_client_admin == 'no' && message_setting()->allow_client_employee == 'no' && in_array('client', user_roles()));

        if (request()->ajax() && request()->has('term')) {
            $term = (request('term') != '') ? request('term') : null;
            $userLists = UserChat::userListLatest(user()->id, $term);
            $messageIds = collect($userLists)->pluck('id');

            $this->userLists = UserChat::with('fromUser', 'toUser')->whereIn('id', $messageIds)->orderBy('id', 'desc')->get();
            $userList = view('messages.user_list', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'userList' => $userList]);
        }

        if(request()->clientId) {
            $this->client = User::find(request()->clientId);
        }

        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');

        $this->userLists = UserChat::with('fromUser', 'toUser')->whereIn('id', $messageIds)->orderBy('id', 'desc')->get();
        return view('messages.index', $this->data);
    }

    /**
     * XXXXXXXXXXXx`
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!in_array('client', user_roles())) {
            $this->employees = User::allEmployees($this->user->id);
            $this->clients = User::allClients();
        }

        // This will return true if message button from projects overview button is clicked
        if(request()->clientId) {
            $this->client = User::find(request()->clientId);
        }

        $this->messageSetting = message_setting();

        if ($this->messageSetting->allow_client_employee == 'yes' && in_array('client', user_roles())) {
            $this->employees = User::allEmployees();
        }
        else if ($this->messageSetting->allow_client_admin == 'yes' && in_array('client', user_roles())) {
            $this->employees = User::allAdmins();
        }

        return view('messages.create', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ChatStoreRequest $request)
    {
        if ($request->user_type == 'client') {
            $receiverID = $request->client_id;
        }
        else {
            $receiverID = $request->user_id;
        }

        $message = new UserChat();
        $message->message         = $request->message;
        $message->user_one        = user()->id;
        $message->user_id         = $receiverID;
        $message->from            = user()->id;
        $message->to              = $receiverID;
        $message->save();

        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $this->userLists = UserChat::with('fromUser', 'toUser')->whereIn('id', $messageIds)->orderBy('id', 'desc')->get();
        $userList = view('messages.user_list', $this->data)->render();

        $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
        $messageList = view('messages.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'message_id' => $message->id, 'receiver_id' => $receiverID]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->chatDetails = UserChat::chatDetail($id, user()->id);
        $view = view('messages.message_list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $view]);
    }

    public function destroy($id)
    {
        UserChat::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function fetchUserMessages($receiverID)
    {
        $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
        $messageList = view('messages.message_list', $this->data)->render();

        return Reply::dataOnly(['message_list' => $messageList]);
    }

}
