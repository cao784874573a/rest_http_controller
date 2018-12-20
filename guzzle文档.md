guzzle使用实列:

使用方法:

```php

$client = new GuzzleHttp\Client();  //实例化客户端

$header = ['Content-Type'=>'application/json'];  //content-type 方式  提交数据的时候 我们用什么类型的数据最好传什么数据 因为 在控制器里面 要对变量解析

uri   = 'http://interface.paper.test/token/access_token';
    
//我们传输的数据格式  这里面的 数组 key值必须为form_params （post类型）
$data['form_params']=[
            'client_id'=>'testclient',
            'client_secret'=>'testpass',
            'grant_type'=>'client_credentials'
];

//我们传输 上传数据的时候 使用 数组 为 multipart 类型的 (file类型)

'multipart' => [
       
        [
            'name'     => 'baz',
            'contents' => fopen('文件名', 'r')
        ],
      
    ]
//传输get类型 使用 query数组

['query' => ['foo' => 'bar']]



form_params和multipart 类型不能同时使用

//get 方式 是获取数据
//posy数据 增加数据
//put 数据更新数据(一般这里 用的是 php的input流获取 但是现在 大部分获取的方式 是以post数组形式传值)
//delete 删除数据

$response=$client->request('GET',$uri ,$data,$header);  //第一个参数 是 请求方式  第二个参数是 请求的url地址  第三个是 请求的数据  第四个是 请求的数据方式




        $stringbody=$response->getBody();  //获取响应的主体内容

        $string=(string)$stringbody;  //数据类型转换

        $json_token=json_decode($string,TRUE);  /

        return $json_token['access_token'];
        
 ```
