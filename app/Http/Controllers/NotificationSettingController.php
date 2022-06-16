<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\PusherSetting;
use App\Models\SlackSetting;
use App\Models\SmtpSetting;

class NotificationSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.notificationSettings';
        $this->activeSettingMenu = 'notification_settings';
    }

    public function index()
    {
        $tab = request('tab');

        $this->emailSettings = email_notification_setting();

        switch ($tab) {
        case 'slack-setting':
            $this->slackSettings = SlackSetting::setting();
            $this->view = 'notification-settings.ajax.slack-setting';
                break;
        case 'push-notification-setting':
            $this->pushSettings = push_setting();
            $this->view = 'notification-settings.ajax.push-notification-setting';
                break;
        case 'pusher-setting':
            $this->pusherSettings = PusherSetting::first();
            $this->view = 'notification-settings.ajax.pusher-setting';
                break;
        default:
            $this->smtpSetting = SmtpSetting::first();
            $this->view = 'notification-settings.ajax.email-setting';
                break;
        }

        ($tab == '') ? $this->activeTab = 'email-setting' : $this->activeTab = $tab;

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('notification-settings.index', $this->data);
    }

}
