<?php
use \Phalcon\Loader,
    Phalcon\Mvc\Model\Exception,
    \Phalcon\Translate\Adapter\Gettext;

$loader = null;

// try {
    $loader = new Loader();

    $loader->registerNamespaces(array(
        'Api\Models' => __DIR__ . '/Models/',
        'Api\Controllers' => __DIR__ . '/Controllers/',
        'Api\Exceptions' => __DIR__ . '/Exceptions/',
        'Api\Modules' => __DIR__ . '/Modules',
    ))->register();

    // try{
        $di = new \Phalcon\DI\FactoryDefault();


        $di->set('db', function(){
            // Activa el log de las consultas
            /*
            $eventsManager = new \Phalcon\Events\Manager();

            $logger = new \Phalcon\Logger\Adapter\File("tmp/logs/debug.log");

            //Listen all the database events
            $eventsManager->attach('db', function($event, $connection) use ($logger) {
                if ($event->getType() == 'beforeQuery') {
                    $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                }
            });

            $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                "host" => "localhost",
                "username" => "root",
                "password" => "toor",
                "dbname" => "phalcon_apirest"
            ));

            //Assign the eventsManager to the db adapter instance
            $connection->setEventsManager($eventsManager);

            return $connection;*/
            return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                "host" => "localhost",
                "username" => "root",
                "password" => "toor",
                "dbname" => "phalcon_apirest"
            ));
        });

        $app = new \Phalcon\Mvc\Micro($di);

        $di->set('translate', function(){
            return new Gettext(array(
                'locale' => 'es_CO',
                'defaultDomain' => 'messages',
                'directory' => 'Locale'
            ));
        });

        $di->set('collections', function(){
            return include('./config/routes.php');
        });

        foreach($di->get('collections') as $collection){
            $app->mount($collection);
        }

        $app->before(function() use ($app) {
            $origin = $app->request->getHeader("ORIGIN") ? $app->request->getHeader("ORIGIN") : '*';

            // $app->response->setHeader("Access-Control-Allow-Origin", $origin)
            $app->response->setHeader("Access-Control-Allow-Origin", '*')
                ->setHeader("Access-Control-Allow-Methods", 'GET,PUT,POST,DELETE,OPTIONS')
                ->setHeader("Access-Control-Allow-Headers", 'Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type, Access-Token')
                ->setHeader("Access-Control-Allow-Credentials", true);
        });

        $app->options('/{catch:(.*)}', function() use ($app) { 
            $app->response->setStatusCode(200, "OK")->send();
        });

        $app->notFound(function () use ($app) {
            throw new \Api\Exceptions\HTTPException(
                'Not Found.',
                404,
                array(
                    'dev' => 'That route was not found on the server.',
                    'internalCode' => 'NF1000',
                    'more' => 'That route was not found on the server.'
                )
            );
        });

        $app->handle();

//     } catch (\Api\Exceptions\HTTPException $he) {
//         $rsp = array();
//         $rsp['code'] = $he->errorCode;
//         $rsp['type'] = 'error';
//         $rsp['message'] = $he->response . ' - ' . $he->additionalInfo;
//         echo (json_encode($rsp));
//     } catch(Exception $e) {
//         echo json_encode($e);
//     }
// } catch(Exception $e) {
//     echo json_encode($e);
// }
