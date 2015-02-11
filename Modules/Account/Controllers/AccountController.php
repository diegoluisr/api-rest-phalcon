<?php
namespace Api\Modules\Account\Controllers;

use \DateTime,
    \DateInterval,
    \Api\Controllers\AppController,
    \Api\Modules\Account\Models\User,
    \Api\Modules\Account\Models\AccessToken,
    \Phalcon\Http\Response;

class AccountController extends AppController{

    protected $name = 'Account';

    public function __construct(){
        parent::__construct();
    }

    public function login(){
        $username = ($this->request->get('username', null, null)) ?: '';
        $password = ($this->request->get('password', null, null)) ?: '';

        $user = User::findFirst(
            array(
                "username='$username'",
                "activated > '0000-00-00 00:00:00'",
                "banned='0000-00-00 00:00:00'",
                "deleted='0000-00-00 00:00:00'"
            )
        );

        if(!empty($user)){
            if($this->security->checkHash($password, $user->password)){

                $time = new DateTime(date('Y-m-d H:i:s'));
                $time->add(new DateInterval('PT10M'));
                $stamp = $time->format('Y-m-d H:i:s');

                $accessToken = new AccessToken();
                $accessToken->user_agent = $this->request->getUserAgent();
                $accessToken->ip = $this->request->getClientAddress();
                $accessToken->expires = $stamp;
                $accessToken->user_id = $user->id;
                $accessToken->token = bin2hex(openssl_random_pseudo_bytes(16));

                if ($accessToken->save() == false) {
                    $this->output['message'] = 'error';
                    
                    foreach ($accessToken->getMessages() as $message) {
                        $messages[] = $this->translate->_($message->getMessage(), array('field' => $message->getField()));
                    }
                    $this->output['message'] = $messages;
                } else {
                    $this->output['data'] = $accessToken->token;
                }
            }
        }

        $this->response->setContent(json_encode($this->output));
        return $this->response;
    }

    public function me(){
        $this->isAuthorized('me');
        
        $this->output['data'] = $this->user;

        $this->response->setContent(json_encode($this->output));
        return $this->response;
    }
    
    public function logout(){
        $this->isAuthorized('logout');

        $access_token = $this->request->getHeader('ACCESS_TOKEN');

        $token = AccessToken::findFirst(
            array(
                "token='$access_token'"
            )
        );

        if(!empty($token)){
            if ($token->delete() == false) {
                $this->output['message'] = $this->translate->_("Sorry, we can't delete the token right now");
            } else {
                $this->output['message'] = $this->translate->_("The token was deleted successfully!");
            }
        }

        $this->response->setContent(json_encode($this->output));
        return $this->response;
    }
}