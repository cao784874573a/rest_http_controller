# 第1节：oauth代码

##Client代码


* 向服务器请求token，并获取服务器响应的token

```php

public function indexAction()
    {
        $header = ['Content-Type'=>'application/json'];
         $uri   = 'http://interface.paper.test/token/access_token';
        // $code  = substr($uri, strpos($uri, 'code=')+5, 40);
        // $state = substr($uri, strpos($uri, 'state=')+6);

        $data['form_params']=[
            'client_id'=>'testclient',
            'client_secret'=>'testpass',
            'grant_type'=>'client_credentials'
        ];

        $client=new client();

        $response=$client->request('POST',$uri ,$data,$header);

        $stringbody=$response->getBody();

        $string=(string)$stringbody;

        $json_token=json_decode($string,TRUE);

        //var_dump($string);

        return $json_token['access_token'];



    }



```


* 测试服务器验证部分token

```php

public function testToken()
    {
         $uri   = 'http://interface.paper.test/';
        // $code  = substr($uri, strpos($uri, 'code=')+5, 40);
        // $state = substr($uri, strpos($uri, 'state=')+6);

        $data['form_params']=[

            //'access_token'=>$this->indexAction(),
            'access_token'=>$this->indexAction(),
        ];

        $client=new client();

        $response=$client->request('POST',$uri ,$data);

        $stringbody=$response->getBody();
        $string=(string)$stringbody;

    }

```


##Server代码

* token生成部分

```php
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

```


* 服务器token入库以及初始化

```php

 public function _server()
    {

        ini_set('display_errors',1);error_reporting(E_ALL);

        $dbParams = array(
            'dsn'      => 'mysql:host=127.0.0.1;port=3306;dbname=oauth;charset=utf8;',
            'username' => 'root',
            'password' => '123456',
        );


        
        $storage = new Pdo($dbParams);

        $server = new Server($storage);

        $server->addGrantType(new ClientCredentials($storage));  //增加客户端凭证

        $server->addGrantType(new AuthorizationCode($storage));  //增加授权码模式

        return $server;


    }


```

####Rest_Controller控制器部分(自定义封装的,还在完善中)

URL:https://github.com/cao784874573a/CI_Rest_Api_Controller