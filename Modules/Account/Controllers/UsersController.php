<?php
namespace Api\Modules\Account\Controllers;

use \Api\Controllers\AppController,
    \Api\Modules\Account\Models\User,
    \Phalcon\Http\Response;

class UsersController extends AppController{

    protected $name = 'Users';

    public function __construct(){
        parent::__construct();
    }
    
    public function index(){
        $this->isAuthorized('index');

        // $phql = 'SELECT * FROM Api\Models\User ORDER BY id';
        // $users = $this->getDI()->get('modelsManager')->executeQuery($phql);

        $options = $this->prepareSearch(array('Api\Modules\Account\Models\User.username', 'Api\Modules\Account\Models\User.email'));

        $count = User::count($options);

        $total = ceil($count / $this->limit);

        $offset = $this->limit * ($this->page - 1);

        $options['limit'] = $this->limit;
        $options['offset'] = $offset;
        
        $users = User::find($options);

        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                'id' => base_convert($user->id, 10, 32),
                'username' => $user->username,
                'email' => $user->email,
                'created' => $user->created,
                'activated' => $user->activated,
            );
        }

        $this->output['data'] = $data;

        $this->output['pagination'] = array(
            'limit' => $this->limit,
            'page' => $this->page,
            'total' => $total,
        );

        $this->response->setContent(json_encode($this->output));
        return $this->response;
    }

    public function add() {
        $this->isAuthorized('add');

        $request = $this->getDI()->get('request');

        $user = new User();
        $user->username = ($request->get('username', null, null)) ?: null;
        $user->password = ($request->get('password', null, null)) ?: null;
        $user->email = ($request->get('email', null, null)) ?: null;
        $user->group_id = ($request->get('group_id', null, null)) ?: null;

        $user->password = $this->security->hash($user->password);

        if($user->create() == false){
            $messages = array();
            foreach ($user->getMessages() as $message) {
                $messages[] = $this->translate->_($message->getMessage(), array('field' => $message->getField()));
            }
            $this->output['message'] = $messages;
        }

        $this->response->setContent(json_encode($this->output));
        return $this->response;
    }

    public function edit($id = null) {
        // TODO
        // Restringir cambio de grupo para los usuarios no administradores
        // Si el username o password va a cambiar se debe enviar el password anterior

        $this->isAuthorized('edit');

        if($id != null) {
            $request = $this->getDI()->get('request');

            $user = User::findFirst(base_convert($id, 32, 10));

            $user->username = ($request->getPut('username', null, null)) ?: $user->username;
            $user->password = ($this->security->hash($request->getPut('password', null, null))) ?: $user->password;
            $user->email = ($request->getPut('email', null, null)) ?: $user->email;
            $user->group_id = ($request->getPut('group_id', null, null)) ?: $user->group_id;

            if($user->update() == false) {
                $messages = array();
                foreach ($user->getMessages() as $message) {
                    $messages[] = $this->translate->_($message->getMessage(), array('field' => $message->getField()));
                }
                $this->output['message'] = $messages;
            }
        }

        $this->response->setContent(json_encode($this->output));
        return $this->response;
    }

    public function view($id = null) {
        $this->isAuthorized('view');

        if($id != null) {
            /*$id = base_convert($id, 32, 10);

            $phql = $this->modelsManager->createQuery('SELECT * FROM Api\Modules\Account\Models\User WHERE id = :id:');
            $users = $phql->execute(array(
                'id' => $id
            ));

            $data = array();
            foreach ($users as $user) {
                $data[] = array(
                    'id' => base_convert($user->id, 10, 32),
                    'username' => $user->username,
                );
            }*/

            $user = User::findFirst(base_convert($id, 32, 10));

            $data[] = array(
                'id' => base_convert($user->id, 10, 32),
                'username' => $user->username,
            );

            $this->output['data'] = $data;

        }
        $this->response->setContent(json_encode($this->output));
        return $this->response;
    }
}