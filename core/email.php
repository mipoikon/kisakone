<?php
/**
 * Suomen Frisbeegolfliitto Kisakone
 * Copyright 2009-2010 Kisakone projektiryhmä
 * Copyright 2014-2016 Tuomo Tanskanen <tuomo@tanskanen.org>
 *
 * This file includes the Email class and other support functionality
 * used for sending e-mail messages.
 *
 * --
 *
 * This file is part of Kisakone.
 * Kisakone is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Kisakone is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kisakone.  If not, see <http://www.gnu.org/licenses/>.
 * */

require_once 'config.php';
require_once 'data/configs.php';
require_once 'core/textcontent.php';


// E-mail types
define('EMAIL_YOU_ARE_TD', 'email_td');
define('EMAIL_REMEMBER_FEES', 'email_fee');
define('EMAIL_PASSWORD', 'email_password');
define('EMAIL_PROMOTED_FROM_QUEUE', 'email_promoted');
define('EMAIL_VERIFY_EMAIL', 'email_verify');



class Email
{
    // E-mails are quite simply text content, using different processing.
    var $textcontent;
    var $text;
    var $title;

    // Initialization can be done with either e-mail id, or by supplying a
    // TextContent object for the message
    function Email($content)
    {
        if (is_a($content, 'TextContent'))
            $this->textcontent = $content;
        else
            $this->textcontent = GetGlobalTextContent($content);
    }

    /**
     * The contained e-mail message is prepared to be sent; all the tokens
     * within are replaced by the corrersponding values. $user, $player and
     * $event can be null when not relevant, $special should be array with
     * the key 'link' in it; its value, if not empty, should be a link to a
     * suitable place within the system.
     *
     * Once this function has been called, the field text contains the
     * prepared e-mail message.
    */
    function Prepare($user, $player, $event, $special)
    {
        if (!$this->textcontent)
            return;

        $tokens = GetEmailTokens();
        $text = $this->textcontent->content;

        $from = array();
        $to = array();

        foreach ($tokens as $token => $data) {
            $from[] = '{' . $token . '}';
            list($object, $field) = explode('.', $data);

            switch ($object) {
                case 'event':
                    if (!$event)
                        $value = '';
                    else {
                        if (substr($field, - 2) == '/d') {
                            $field = substr($field, 0, - 2);
                            $date = true;
                        }
                        else
                            $date = false;
                        $value = $event->$field;
                        if (!is_object($event))
                            echo print_r($event, true);
                        if ($date)
                            $value = date('d.m.Y', $value);
                    }
                    break;


                case 'player':
                    if (!$player)
                        $value = '';
                    else
                        $value = $player->$field;
                    break;


                case 'user':
                    if (!$user)
                        $value = '';
                    else
                        $value = $user->$field;
                    break;


                case 'special':
                    $value = @$special[$field];
                    break;
            }

            $to[] = $value;
        }

        $this->text = str_replace($from, $to, $text);
        $this->title = str_replace($from, $to, $this->textcontent->title);
    }

    function Send($recipientAddress)
    {
        if (!$this->text)
            return;

        if (!GetConfig(EMAIL_ENABLED))
            return;

        $from = GetConfig(EMAIL_ADDRESS);
        $sender = GetConfig(EMAIL_SENDER);
        $from_header = "$sender <$from>";

        $preferences = ['input-charset' => 'UTF-8', 'output-charset' => 'UTF-8'];
        $encoded_subject = iconv_mime_encode('Subject', $this->title, $preferences);
        $encoded_subject = substr($encoded_subject, strlen('Subject: '));

        return mail($recipientAddress, $encoded_subject, $this->text,
            "From: " . $from_header . "\r\n" . "X-Mailer: Kisakone", "-f$from");
    }
}

/**
 * This function sends e-mail to a specific user of the system. E-mails are
 * specified by their unique ID's. User and player tokens in the email
 * get their values from the recipient. Event and link token values are provided
 * as parameters to this function.
 *
 * Some details of the messages can be adjusted in Config database.
 */
function SendEmail($emailid, $userid, $event, $link = '', $token = '')
{
    $user = GetUserDetails($userid);
    $player = $user->GetPlayer();
    $special = array('link' => $link, 'token' => $token);
    $email = new Email($emailid);
    $email->prepare($user, $player, $event, $special);

    return $email->send($user->email);
}


/**
 * E-mail tokens are bits of text which are converted within an e-mail message
 * with appropriate content.
 */
function GetEmailTokens()
{
    // The key stands for the token itself, the value is instruction for
    // the Email::Prepare function letting it know how to deal with it
    return array(
        'event' => 'event.name',
        'pdga' => 'player.pdga',
        'lastname' => 'user.lastname',
        'firstname' => 'user.firstname',
        'username' => 'user.username',
        //'admin_firstname' => 'user.firstname',
        //'admin_lastname' => 'user.lastname',
        'startdate' => 'event.startdate/d',
        'signup_end' => 'event.signupEnd/d',
        'link' => 'special.link',
        'token' => 'special.token'
    );
}

