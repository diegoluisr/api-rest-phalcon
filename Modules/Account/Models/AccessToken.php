<?php
namespace Api\Modules\Account\Models;

use Api\Models\AppModel,
    Api\Modules\Account\Models\User,
    DateTime,
    DateInterval;

class AccessToken extends AppModel {

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id = 0;

    /**
     * @Related(type="belongsTo", model="Api\Modules\Account\Models\User")
     * @Column(type="integer", nullable=false)
     */
    public $user_id = 0;

    /**
     * @Unique
     * @Column(type="string", nullable=false)
     */
    public $token = '';

    /**
     * @Column(type="string", nullable=false)
     */
    public $ip = '';

    /**
     * @Column(type="string", nullable=false, max=255)
     */
    public $user_agent = '';

    /**
     * @Column(type="datetime", nullable=false)
     */
    public $expires = '0000-00-00 00:00:00';


    public function initialize() {
        return;
    }

    public function getSource() {
        return "access_tokens";
    }

    public static function getUser($token, $user_agent, $ip) {
        $token = self::query()
            ->where('token = :token:')
            ->andWhere('user_agent = :user_agent:')
            ->andWhere('expires > :expires:')
            ->andWhere('ip = :ip:')
            ->bind(
                array(
                    'token' => $token,
                    'user_agent' => $user_agent,
                    'ip' => $ip,
                    'expires' => date('Y-m-d H:i:s')
                ))
            ->order('id')
            ->limit(1)
            ->execute(); 

        if(isset($token[0])){
            $token = $token[0];

            $time = new DateTime(date('Y-m-d H:i:s'));
            $time->add(new DateInterval('PT10M'));
            $stamp = $time->format('Y-m-d H:i:s');
            
            $token->expires = $stamp;
            if($token->update() == false){
                // 
            }
        } else {
            $token = null;
        }


        if($token != null) {
            return User::getValidUser($token->user_id);
        }
        return null;
    }

}