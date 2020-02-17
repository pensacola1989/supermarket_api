<?php

namespace App\Console\Commands\Admin;

use App\Jobs\WeChatNotifyJob;
use App\Services\HistoryPlace\HistoryPlaceContract;
use Illuminate\Console\Command;
use App\Services\Place\PlaceRepository;
use App\Services\Place\PlaceContract;
use App\Services\Place\PlaceApplyContract;
use App\Services\SystemNotify\SystemNotifyContract;
use App\Services\WeChatNotify\WeChatNotifyContract;
use Illuminate\Support\Facades\DB;

class SystemNotifyCommand extends Command
{
    protected $name = 'system:notify';

    protected $signature = 'system:notify {--nid=}';

    protected $systemNotifyRepository;

    public function __construct(SystemNotifyContract $systemNotifyContract)
    {
        parent::__construct();
        $this->systemNotifyRepository = $systemNotifyContract;
    }

    public function handle()
    {
        $systemNotifyId = $this->option('nid');
        $systemNotify = $this->systemNotifyRepository->requireById($systemNotifyId);
        $systemNotify->created_at = $systemNotify->freshTimestamp();
        // $systemNotify->touch();

        $systemNotify->save();

        $this->info('time refresh to utc');
    }
}
