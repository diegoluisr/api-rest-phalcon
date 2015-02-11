<?php
namespace Api\Modules\Account\Models;

use Api\Models\AppModel;

class Group extends AppModel {

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id = 0;

    /**
     * @Unique
     * @Column(type="string", nullable=false)
     */
    public $name = '';

    public function initialize() {
        return;
    }

    public function getSource() {
        return "groups";
    }

    public function validation() {
        parent::validation();
        return $this->validationHasFailed() != true;
    }
}