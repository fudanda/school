<?php
/**
 * Created by PhpStorm.
 * User: xuxiaodao
 * Date: 2017/11/17
 * Time: 下午4:37
 */

namespace App\Http\Logic;


use App\Praise;
use App\User;

class PraiseLogic
{
    /**
     * 点赞
     *
     * @author yezi
     *
     * @param $ownerId
     * @param $objId
     * @param $objType
     * @param null $collegeId
     * @return mixed
     */
    public function createPraise($ownerId, $objId, $objType, $collegeId = null)
    {
        $praise = Praise::create([
            Praise::FIELD_ID_OWNER => $ownerId,
            Praise::FIELD_ID_OBJ => $objId,
            Praise::FIELD_OBJ_TYPE => $objType,
            Praise::FIELD_ID_COLLEGE => $collegeId
        ]);

        return $praise;
    }

    /**
     * 获取点赞
     *
     * @author yezi
     *
     * @param $objId
     * @param $objType
     * @return mixed
     */
    public function praise($objId,$objType)
    {
        $praise = Praise::where(Praise::FIELD_ID_OBJ,$objId)
            ->where(Praise::FIELD_OBJ_TYPE,$objType)
            ->get();

        return $praise;
    }

    public function formatBatchPraise($praises)
    {
        return collect($praises)->map(function ($item){

            return $this->formatSinglePraise($item);

        });
    }

    /**
     * 格式化点赞返回的格式
     *
     * @author yeiz
     *
     * @param $praise
     * @return array
     */
    public function formatSinglePraise($praise)
    {
        $praiseUser = User::find($praise['owner_id']);
        return [
            'id'=>$praise['id'],
            'owner_id'=>$praise[Praise::FIELD_ID_OWNER],
            'obj_type'=>$praise[Praise::FIELD_OBJ_TYPE],
            'college_id'=>$praise[Praise::FIELD_ID_COLLEGE],
            'user_id'=>$praiseUser->id,
            'nickname'=>$praiseUser->{User::FIELD_NICKNAME},
            'avatar'=>$praiseUser->{User::FIELD_AVATAR}
        ];
    }

}