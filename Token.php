<?php 

defined('BASEPATH') or exit('No direct script access allowed');


require APPPATH.'vendor/autoload.php';
use OAuth2\Server;
use OAuth2\Storage\Pdo;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Request;
use OAuth2\Response;

class Token extends MY_Controller{

    public function __construct(){

        parent::__construct();

    }

    /**
     * 获取token
     * 
     *
     * @return void
     */
    public function access_token()
    {

        // $request=OAuth2\Request::createFromGlobals();

        // if(empty($request))
        // {
        //     echo json_encode(array('msg'=>'post数据为空'));
        //     exit;
        // }

        $server=parent::_server();

        $server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
    }

   

}