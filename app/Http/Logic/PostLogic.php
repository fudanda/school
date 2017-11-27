<?php
/**
 * Created by PhpStorm.
 * User: xuxiaodao
 * Date: 2017/11/13
 * Time: 下午6:16
 */

namespace App\Http\PostLogic;


use App\Http\Logic\CommentLogic;
use App\Http\Logic\PraiseLogic;
use App\Post;
use App\User;

class PostLogic
{
    /**
     * 保存新增的贴子
     *
     * @author yezi
     *
     * @param $user
     * @param $content
     * @param null $imageUrls
     * @param null $location
     * @param null $private
     * @return mixed
     */
    public function save($user,$content,$imageUrls=null,$location=null,$private=null)
    {
        $result = Post::create([
            Post::FIELD_ID_POSTER => $user->{User::FIELD_ID},
            Post::FIELD_ID_COLLEGE => $user->{User::FIELD_ID_COLLEGE},
            Post::FIELD_CONTENT => $content,
            Post::FIELD_ATTACHMENTS => $imageUrls,
            Post::FIELD_PRIVATE => $private
        ]);

        return $result;
    }

    public function getPostList($user,$time=null)
    {
        $posts = Post::with(['poster','praises','comments'])
            ->where(Post::FIELD_ID_COLLEGE,$user->{User::FIELD_ID_COLLEGE})
            ->when($time,function ($query)use($time){
                return $query->where(Post::FIELD_CREATED_AT,'>',$time);
            })
            ->orderBy(Post::FIELD_CREATED_AT,'desc')
            ->get();

        return $posts;

    }

    /**
     * 格式化单挑贴子
     *
     * @author yezi
     *
     * @param $post
     * @param $user
     * @return $this
     */
    public function formatSinglePost($post,$user)
    {
        if(collect($post)->toArray()){
            $poster = $post['poster'];
            $post = collect($post)->forget('poster');
            $post['poster']  = [
                'id'=>$poster->id,
                'nickname'=>$poster->nickname,
                'avatar'=>$poster->avatar,
                'college_id'=>$poster->college_id,
                'created_at'=>$poster->created_at,
            ];

            $post[Post::FIELD_ATTACHMENTS] = collect($post[Post::FIELD_ATTACHMENTS])->map(function($item){
                if(is_null($item) || $item == null){
                    $item = '';
                }

                return $item;
            });

            $post['praises'] = app(PraiseLogic::class)->formatBatchPraise($post['praises']);

            $post['comments'] = app(CommentLogic::class)->formatBatchComments($post['comments'],$user);

            if($post[Post::FIELD_ID_POSTER] == $user->{User::FIELD_ID}){
                $post['can_delete'] = true;
            }else{
                $post['can_delete'] = false;
            }

        }
        return $post;
    }

}