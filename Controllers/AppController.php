<?php
namespace Api\Controllers;

use \Api\Modules\Account\Models\AccessToken,
    \Api\Modules\Account\Models\User,
    \Api\Modules\Account\Models\Group,
    \Phalcon\DI\Injectable,
    \Phalcon\Acl\Adapter\Memory as Acl,
    \Phalcon\Acl\Role,
    \Phalcon\Acl\Resource,
    \Phalcon\Translate\Adapter\Gettext,
    \Phalcon\Mvc\Model\Query\Builder;

class AppController extends Injectable{

    protected $limit = 20;
    protected $page = 1;
    protected $output = array('message' => '', 'code' => '', 'data' => '');
    protected $name = '';
    protected $translate = null;

    protected $user = null;

    public function __construct(){
        $di = \Phalcon\DI::getDefault();
        $this->setDI($di);

        $this->parseRequest();

        $this->translate = $this->getDI()->get('translate');

        $access_token = $this->request->getHeader('ACCESS_TOKEN');
        $user_agent = $this->request->getUserAgent();
        $ip = $this->request->getClientAddress();

        $this->user = AccessToken::getUser($access_token, $user_agent, $ip);
    }

    protected function isAuthorized($action = null) {
        $acl = new Acl();

        $acl->setDefaultAction(\Phalcon\Acl::DENY);

        if (!file_exists(dirname(dirname(__FILE__)).'/tmp/data/acl.data')) {
            $superadmin = new Role("superadmin");
            $guest = new Role("guest");

            $acl->addRole($superadmin);
            $acl->addRole($guest);

            $resource = new Resource("/");

            $acl->addResource($resource, "*");
            $acl->allow("superadmin", "/", "*");

            file_put_contents(dirname(dirname(__FILE__)).'/tmp/data/acl.data', serialize($acl));
        } else {
            $acl = unserialize(file_get_contents(dirname(dirname(__FILE__)).'/tmp/data/acl.data'));
        }

        if(!isset($this->user) && !$acl->isAllowed($this->user['group']['name'], "/", "*") && !$acl->isAllowed($this->user['group']['name'], "/" . $this->name, $action)){
            throw new \Api\Exceptions\HTTPException(
                'Unauthorized',
                401,
                array(
                    'dev' => $this->translate->_('This resource requires authorized access'),
                    'internalCode' => 'AUTH401',
                    'more' => 'Check route for mispellings'
                )
            );
        }
    }

    protected function parseRequest(){
        $request = $this->getDI()->get('request');

        $this->limit = ($request->get('limit', null, null)) ?: $this->limit;
        if($this->limit > 100){
            $this->limit = 100;
        }

        $this->page = ($request->get('page', null, null)) ?: $this->page;
        if($this->page < 1){
            $this->page = 1;
        }
    }

    protected function prepareSearch($fields){
        $request = $this->getDI()->get('request');
        $query = ($request->get('q', null, null)) ?: '';

        $prepared = array();
        $conditions = '';
        $parameters = array();
        if($query != '' && !empty($fields)) {
            $words = array_unique(explode(' ', $query));
            foreach ($words as $key => $word) {
                if(strlen($word) >= 3) {
                    foreach ($fields as $field) {
                        if($conditions != '') {
                            $conditions .= ' OR ';
                        }
                        $conditions .= $field. " LIKE :word" . $key . ":";
                    }
                }
                $parameters['word'.$key] = '%'.$word.'%';   
            }
            if($conditions != '' && count($parameters) > 0) {
                $prepared['conditions'] = $conditions;
                $prepared['bind'] = $parameters;
            }
        }
        return $prepared;
    }
}