<?php

namespace App\Traits;

use ErrorException;
use Auth;
use Exception;

trait ClientInformation
{
    public function getClientIP()
    {
        try {
            $wanIp = file_get_contents('http://bot.whatismyipaddress.com');
        } catch (ErrorException $e) {
            $wanIp = 'UNKNOWN';
        }

        $ipaddress = $wanIp . '+';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress .= $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress .= $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress .= $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipaddress .= $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress .= $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress .= $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress .= $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress .= 'UNKNOWN';
        }

        return $ipaddress;
    }

    public function processAnswererInformation($data, $survey, $privacy)
    {
        $clientInfo['client_ip'] = '';
        $answerRequiredSetting = $survey->settings()->where('key', config('settings.setting_type.answer_required.key'))->first()->value;

        // check answer_required setting
        switch ($answerRequiredSetting) {
            case config('settings.survey_setting.answer_required.none'):
                // if user is login
                if (Auth::check()) {
                    $this->updateInviteWithLogin($survey, Auth::user(), $privacy);
                    $clientInfo['user_id'] = Auth::user()->id;

                    break;
                }

                // if survey is private then not allow incognito
                if ($privacy == config('settings.survey_setting.privacy.private')) {
                    throw new Exception("Not permitted", 1);
                }

                // if user is incognito - just be available with survey public
                $clientInfo['client_ip'] = $this->getClientIP();
                $this->updateInviteWithIncognito($survey, $clientInfo['client_ip']);

                break;

            case config('settings.survey_setting.answer_required.login'):
                $userId = $data->get('user_id');

                if ($userId != Auth::user()->id) {
                    throw new Exception("Error Processing Request", 1);
                }

                $this->updateInviteWithLogin($survey, Auth::user(), $privacy);
                $clientInfo['user_id'] = $userId;

                break;

            case config('settings.survey_setting.answer_required.login_with_wsm'):
                $userId = $data->get('user_id');

                if ($userId != Auth::user()->id || !Auth::user()->checkLoginWsm()) {
                    throw new Exception("Error Processing Request", 1);
                }

                $this->updateInviteWithLogin($survey, Auth::user(), $privacy);
                $clientInfo['user_id'] = $userId;

                break;
            default:
                throw new Exception("Error Processing Request", 1);
                return;        
        }

        return $clientInfo;
    }

    /*  update invite survey with login
            + invite empty
                + privacy private -> error
                + privaty public -> create invite
            + invite not empty
                + user in invite list -> update invite
                + user not in invite list
                    + privacy privare -> error
                    + privacy public -> update invite
    */
    public function updateInviteWithLogin($survey, $user, $privacy)
    {
        $inviter = $survey->invite;
        $userMail = $user->email;

        // if has list invites
        if (!empty($inviter)) {
            $inviteMails = collect($inviter->invite_mails_array);
            $answerMails = $inviter->answer_mails_array;
            
            // if user in invite list
            if ($inviteMails->contains($userMail)) {
                $inviteMails = $inviteMails->reject(function ($mail) use ($userMail) {
                    return $mail == $userMail;
                });

                array_push($answerMails, $userMail);

                $updateData = [
                    'invite_mails' => join('/', $inviteMails->all()) . '/',
                    'answer_mails' => join('/', $answerMails) . '/',
                    'number_answer' => ++ $inviter->number_answer,
                ];
            } else {
                // if user has answered -- current just process permit 1 user answer 1 times
                if (collect($answerMails)->contains($userMail)) {
                    throw new Exception("Has answered", 1);
                }

                // if user not in invite list and survey is private
                if ($privacy == config('settings.survey_setting.privacy.private')) {
                    throw new Exception("Not permitted", 1);
                }

                array_push($answerMails, $userMail);

                $updateData = [
                    'answer_mails' => join('/', $answerMails) . '/',
                    'number_invite' => ++ $inviter->number_invite,
                    'number_answer' => ++ $inviter->number_answer,
                ];
            }

            $survey->invite()->update($updateData);

            return;
        }

        // if has not invite list and survey is private
        if ($privacy == config('settings.survey_setting.privacy.private')) {
            throw new Exception("Not permitted", 1);
        }

        // if has not invite list and survey is public
        $survey->invite()->create([
            'invite_mails' => '',
            'answer_mails' => $userMail . '/',
            'subject' => $survey->title,
            'message' => '',
            'status' => config('settings.survey.invite_status.not_finish'),
            'number_invite' => 1,
            'number_answer' => 1,
        ]);
    }

    public function updateInviteWithIncognito($survey, $clientIp) 
    {
        // if user has answered -- current just process permit 1 user answer 1 times
        if ($survey->results->where('client_ip', $clientIp)->count()) {
            throw new Exception("Has answered", 1);
        }

        $inviter = $survey->invite;

        if (!empty($inviter)) {
            $survey->invite()->update([
                'number_answer' => ++ $inviter->number_answer,
                'number_invite' => ++ $inviter->number_invite,
            ]);

            return;
        }
        
        $survey->invite()->create([
            'invite_mails' => '',
            'answer_mails' => '',
            'subject' => $survey->title,
            'message' => '',
            'status' => config('settings.survey.invite_status.not_finish'),
            'number_answer' => 1,
            'number_invite' => 1,
        ]);
    }
}
