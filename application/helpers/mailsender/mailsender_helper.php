<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//if(!function_exists('send_error_report')) {
    /*
    function send_error_report($projectId, $pdfPath = '', $subject, $body) {
        $CI =& get_instance();
        $CI->load->model('Projects_model');

        $customers = $CI->Projects_model->getProjectCustomers($projectId);
        
        if (count($customers) == 0) return false;
        
        $CI->config->load('smtp', true);
        $smtp_settings = $CI->config->item('smtp');
        
        //it's for send copy error report
        $contactPerson = $CI->Projects_model->getContactPerson($projectId);

        if (count($contactPerson)) {
            $users = array_merge($customers, $contactPerson);
        }

        foreach ($users as $user) {
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->SMTPDebug  = $smtp_settings['debug'];
                $mail->Host       = $smtp_settings['host'];
                $mail->SMTPAuth   = $smtp_settings['smtp_auth'];
                $mail->Username   = $smtp_settings['user_name'];
                $mail->Password   = $smtp_settings['password']; 
                $mail->SMTPSecure = $smtp_settings['secure'];
                $mail->Port       = $smtp_settings['port'];

                // Set email format to HTML
                $mail->isHTML(true);
                //Recipients
                $mail->setFrom($smtp_settings['from']);
                $mail->addAddress($user['user_email']);
                // Content
                $mail->Subject = $subject;
                $mail->Body    = $body;
                // Attachments
                if ($pdfPath) {
                    $pdfPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $pdfPath;
                    $mail->addAttachment($pdfPath, 'daily_report', 'base64', 'application/pdf');
                }

                $mail->send();
                log_message('debug', 'Message for project ' . $projectId . ' has been sent');
            } catch (Exception $e) {
                log_message('debug', "Message for project " . $projectId . " could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }
    }
    */
//}

//if(!function_exists('parse_error_logs')) {
/*    function parse_error_logs($projectId, $subject, $body) {
        $errors_dictionary = [
            'Could not export',
            'Could not create',
            'Could not update',
            'Error',
            'Errors',
            'Failed',
        ];

        $count = 0;

        foreach ($errors_dictionary as $error) {
            if (strripos($body, $error) === false) continue;
            $count++;
        }

        if ($count == 0) return false;

        send_error_report($projectId, '', $subject, $body);
    }*/
//}

if(!function_exists('send_email_to_user')) {
    function send_email_to_user($user, $subject, $body) {

        $CI =& get_instance();
        
        $CI->config->load('smtp', true);
        $smtp_settings = $CI->config->item('smtp');

        $mail = new PHPMailer(true);
        
        // DEBUG OVERWRITE
        $user['user_email'] = 'leon@web-company.com';
        
        try {
            //Server settings
            $mail->isSMTP();
            // $mail->SMTPDebug  = $smtp_settings['debug'];
            $mail->Host       = $smtp_settings['host'];
            $mail->SMTPAuth   = $smtp_settings['smtp_auth'];
            $mail->Username   = $smtp_settings['user_name'];
            $mail->Password   = $smtp_settings['password']; 
            $mail->SMTPSecure = $smtp_settings['secure'];
            $mail->Port       = $smtp_settings['port'];

            // Set email format to HTML
            $mail->isHTML(true);
            //Recipients
            $mail->setFrom($smtp_settings['from']);
            $mail->addAddress($user['user_email']);
            // Content
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();

            log_message('debug', 'Message for ' . $user['user_name'] . ' has been sent');

        } catch (Exception $e) {
            log_message('debug', "Message for " . $user['user_name'] . " could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}

if(!function_exists('remind_unread_message')) {
    function remind_unread_message() {

        $CI =& get_instance();
        $CI->load->model('Projects_model');

        $users = $CI->Projects_model->getCustomers();
        
        if (count($users) == 0) return false;

        $subject = 'You have unread messages.';

        foreach ($users as $key => $user) {

            $unread_messages_count = $CI->Projects_model->getUnreadMessagesCount($user['user_id']);
            $suffix = 'messages';

            if ($unread_messages_count == 1) {
                $suffix = 'message';
            }
            
            if ($user['remind_unread_message'] && $unread_messages_count) {

                if ($user['interval_time']) {
                    $interval_sec = $user['interval_time'] * 3600;
                    $current_timestamp = time();
                    $last_remind_timestamp = strtotime($user['last_remind_time']);
                    $diff_sec = $current_timestamp - $last_remind_timestamp;
                    
                    if (abs($diff_sec) >= $interval_sec) {

                        $body = '';
                        $body .= '<div class="container">';
                        $body .= '<div style="padding: 30px; border: 1px solid #444; border-radius: 5px; background-color: ddd;">';
                        $body .= '<div style="padding: 125px 140px;">';
                        $body .= '<p> Hello. <br/> This is <span style="color:#e84040;">APIcenter</span> reminder.</p><br/>';
                        $body .= "<h1>There are " . $unread_messages_count . " unread " . $suffix . "</h1>";
                        $body .= "<p style='font-size: 20px;'>If you want to check unread messages, click <a href='" . site_url('/message-center') . "' style='font-weight:bold;'> this link </a>.</p>";
                        $body .= '</div>';
                        $body .= '</div>';
                        $body .= '</div>';

                        send_email_to_user($user, $subject, $body);

                        $CI->Projects_model->updateLastRemindTimstamp($user['user_id']);
                    }                    
                }
            }
        }
    }
}

if(!function_exists('alarm_logs_message')) {
    function alarm_logs_message() {

        $CI =& get_instance();
        $CI->load->model('Projects_model');

        $users = $CI->Projects_model->getCustomers();
        
        if (count($users) == 0) return false;

        $subject = '';

        foreach ($users as $key => $user) {

            $project_ids = $CI->Projects_model->getProjectIds($user['user_id']);

            $generated_error_count = $CI->Projects_model->getErrorLogCount($project_ids);

            if ($generated_error_count) {

                $subject = 'You have errors in logs.';

                $body = '';
                $body .= '<div class="container">';
                $body .= '<div style="padding: 30px; border: 1px solid #444; border-radius: 5px; background-color: ddd;">';
                $body .= '<div style="padding: 125px 140px;">';
                $body .= '<p> Hello. <br/> This is <span style="color:#e84040;">APIcenter</span> reminder.</p><br/>';
                $body .= "<h3>There are " . $generated_error_count . " errors in Logs. </h3>";
                $body .= '</div>';
                $body .= '</div>';
                $body .= '</div>';

                send_email_to_user($user, $subject, $body);
            }

            $recent_log_count = $CI->Projects_model->getRecentLogCount($project_ids);

            if (!$recent_log_count) {

                $subject = 'There is a problem in logs.';

                $body = '';
                $body .= '<div class="container">';
                $body .= '<div style="padding: 30px; border: 1px solid #444; border-radius: 5px; background-color: ddd;">';
                $body .= '<div style="padding: 125px 140px;">';
                $body .= '<p> Hello. <br/> This is <span style="color:#e84040;">APIcenter</span> reminder.</p><br/>';
                $body .= "<h3>There is something wrong in Logs. </h3>";
                $body .= "<p style='font-size: 20px;'>There are no generated Logs for about 2 hours.</p>";
                $body .= '</div>';
                $body .= '</div>';
                $body .= '</div>';

                send_email_to_user($user, $subject, $body);
            }
        }
    }
}


