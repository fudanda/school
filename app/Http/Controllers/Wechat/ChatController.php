<?php

namespace App\Http\Wechat;


use App\ChatMessage;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Logic\ChatLogic;
use App\Http\Logic\FriendLogic;
use Carbon\Carbon;
use League\Flysystem\Exception;

class ChatController extends Controller
{
    protected $chat;
    protected $friend;

    public function __construct(ChatLogic $chatLogic,FriendLogic $friendLogic)
    {
        $this->chat = $chatLogic;
        $this->friend = $friendLogic;
    }

    /**
     * 发送消息
     *
     * @author yezi
     *
     * @param $friendId
     * @return mixed
     * @throws ApiException
     */
    public function sendMessage($friendId)
    {
        $user = request()->input('user');
        $content = request()->input('content');
        $attachments = request()->input('attachments');
        $type = ChatMessage::ENUM_STATUS_RED;
        $userId = $user->id;
        $postAt = Carbon::now();

        try{
            \DB::beginTransaction();

            $friend = $this->friend->checkFriendUnique($userId,$friendId);
            if(!$friend){
                $this->friend->createFriend($userId,$friendId);
                $this->friend->createFriend($friendId,$userId);
            }

            $result = $this->chat->sendMessage($userId,$friendId,$content,$attachments,$type,$postAt);

            $result = $this->chat->format($result);

            \DB::commit();

        }catch (Exception $exception){
            \DB::rollBack();
            throw new ApiException($exception);
        }

        return $result;
    }

    public function chatList($friendId)
    {
        $user = request()->input('user');

        return $this->chat->chatList($user->id,$friendId);
    }


}