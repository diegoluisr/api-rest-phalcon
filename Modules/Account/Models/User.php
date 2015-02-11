<?php
namespace Api\Modules\Account\Models;

use Api\Models\AppModel;

class User extends AppModel {

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id = 0;

    /**
     * @Related(type="belongsTo", model="Api\Modules\Account\Models\Group")
     * @Column(type="integer", nullable=false)
     */
    public $group_id = 0;

    /**
     * @Unique
     * @Column(type="string", nullable=false)
     */
    public $username = '';

    /**
     * @Column(type="string", nullable=false, max=100)
     */
    public $password = '';

    /**
     * @Unique
     * @Column(type="email", nullable=false, max=100)
     */
    public $email = '';

    /**
     * @Column(type="datetime", nullable=false)
     */
    public $created = '0000-00-00 00:00:00';

    /**
     * @Column(type="datetime", nullable=false)
     */
    public $modified = '0000-00-00 00:00:00';

    /**
     * @Column(type="datetime", nullable=false)
     */
    public $activated = '0000-00-00 00:00:00';

    /**
     * @Column(type="datetime", nullable=false)
     */
    public $banned = '0000-00-00 00:00:00';

    /**
     * @Column(type="datetime", nullable=false)
     */
    public $deleted = '0000-00-00 00:00:00';


    public function initialize() {
        $this->belongsTo('group_id', '\Api\Modules\Account\Models\Group', 'id', array(
            'alias' => 'Group'
        ));
        $this->useDynamicUpdate(true);
        return;
    }

    public function getSource() {
        return "users";
    }

    public function beforeCreate() {
        $this->created = date('Y-m-d H:i:s');
        $this->modified = date('Y-m-d H:i:s');
        $this->activated = '0000-00-00 00:00:00';
        $this->banned = '0000-00-00 00:00:00';
        $this->deleted = '0000-00-00 00:00:00';
    }

    public function beforeUpdate() {
        $this->modified = date('Y-m-d H:i:s');
    }

    public function validation() {
        parent::validation();
        return $this->validationHasFailed() != true;
    }

    public static function getValidUser($id) {
        $user = self::findFirst(
            array(
                'id = :user_id: AND activated > :activated: AND banned = :banned: AND deleted = :deleted:',
                'bind' => array(
                    'user_id' => $id,
                    'activated' => '0000-00-00 00:00:00',
                    'banned' => '0000-00-00 00:00:00',
                    'deleted' => '0000-00-00 00:00:00',
                )
            )
        );

        if(is_object($user)){
            $usr = array();
            $usr['id'] = $user->id;
            $usr['username'] = $user->username;
            $usr['email'] = $user->email;
            $usr['created'] = $user->created;
            $usr['activated'] = $user->activated;
            $usr['group']['id'] = $user->group_id;
            $usr['group']['name'] = $user->Group->name;

            return $usr;
        }
        return null;
    }

}