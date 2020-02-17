<?php

namespace App\Console\Commands\Admin;

use App\Jobs\WeChatNotifyJob;
use App\Services\Account\UserContract;
use App\Services\HistoryPlace\HistoryPlaceContract;
use Illuminate\Console\Command;
use App\Services\Place\PlaceRepository;
use App\Services\Place\PlaceContract;
use App\Services\Place\PlaceApplyContract;
use App\Services\WeChatNotify\WeChatNotifyContract;
use Illuminate\Support\Facades\DB;

class ApprovalCommand extends Command
{
    protected $name = 'place:approve';

    protected $signature = 'place:approve {--pid=}';

    private $_placeRepository;

    private $_placeApplyRepository;

    private $_weChatNotifyService;

    private $_historyRepository;

    private $_userRepository;


    public function __construct(
        PlaceContract $placeRepository,
        PlaceApplyContract $placeApplyContract,
        WeChatNotifyContract $weChatNotifyContract,
        HistoryPlaceContract $historyPlaceContract,
        UserContract $userContract
    ) {
        $this->_placeRepository = $placeRepository;
        $this->_placeApplyRepository = $placeApplyContract;
        $this->_weChatNotifyService = $weChatNotifyContract;
        $this->_historyRepository = $historyPlaceContract;
        $this->_userRepository = $userContract;

        parent::__construct();
    }

    public function handle()
    {
        $placeIApplyd = $this->option('pid');
        if (!$placeIApplyd) {
            $this->info('please input a place id!!!');
            return;
        }
        DB::transaction(function () use ($placeIApplyd) {
            $placeApply = $this->_placeApplyRepository->getById($placeIApplyd);
            if ($placeApply->status === 1) {
                $this->info('place has been approved already!');
                return;
            }
            if ($placeApply) {
                $placeApply->status = 1;
                $placeApply->save();
            }
            $newPlace = $this->_placeRepository->getNew([
                'category_id' => $placeApply->category_id,
                'name' => $placeApply->name,
                'lat' => $placeApply->lat,
                'lng' => $placeApply->lng,
                'geo_hash' => $placeApply->geo_hash,
                'desc' => $placeApply->desc,
                'avatar_id' => $placeApply->avatar_id,
                'cover_id' => $placeApply->avatar_id,
                'admin_id' => $placeApply->apply_user_id,
                'is_private' => $placeApply->is_private,
                'fans_count' => $placeApply->fans_count,
                'mp_id' => $placeApply->mp_id
            ]);
            $newPlace->configs = $this->_placeRepository->getPlaceDefaultConfigOptions();
            $this->_placeRepository->save($newPlace);
            // 默认管理员会有浏览记录
            $this->_historyRepository->syncUserHistory($placeApply->apply_user_id, $newPlace->id);
            // 默认管理员关注
            $this->_placeRepository->subscribePlace($placeApply->apply_user_id, $newPlace->id);
            // 发送模板消息
            $openId = $this->_userRepository->getOpenId($placeApply->apply_user_id);
            $this->_weChatNotifyService->deliveryMsg($openId, config('wechat.scenes.approved'), $placeIApplyd, [
                "phrase1" => ["value" => '通过'],
                "thing2" => ["value" => '留言区申请'],
                "date3" => ["value" => date('Y年m月d日 h:i', time() + 8 * 60 * 60)],
                "date4" => ["value" => date('Y年m月d日 h:i', time() + 8 * 60 * 60)],
                "thing5" => ["value" => '点击进入留言区主页']
            ], ['place_id' => $newPlace->external_id]);
            // $this->_weChatNotifyService->deliveryMsg($placeApply->apply_user_id, config('wechat.scenes.approved'), $placeIApplyd, [
            //     "您申请的圈子\"{$placeApply->name}\"已通过审核",
            //     date('Y年m月d日 h:i', time() + 8 * 60 * 60)
            // ], [
            //     'place_id' => $newPlace->external_id
            // ]);

            $this->info('approve succeed!');
        });
    }
}
