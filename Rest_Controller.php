<?PHP
if (!defined('BASEPATH')) { exit('No direct script access allowed');
}
/**
 * 重写restfull控制器 添加验证方式为oauth和http基本请求
 *
 * @package    CodeIgniter
 * @author     indraw
 * @change     guozw
 * @copyright  赛尔互联
 * @license    http://www.eol.cn
 * @link       http://www.eol.cn
 * @since      Version 2.0
 * @filesource
 */

require APPPATH.'vendor/autoload.php';
use OAuth2\Server;
use OAuth2\Storage\Pdo;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Request;
use OAuth2\Response;

class MY_Controller extends CI_Controller
{


    //定义http状态码 主要用来提示和判断用
    protected $stateHttpCode=array();

    //定义常用的获取方法
    protected $_input_method=['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];

    //定义所有的头文件
    protected $_supported_formats = [
        'json' => 'application/json',
        'array' => 'application/json',
        'csv' => 'application/csv',
        'html' => 'text/html',
        'jsonp' => 'application/javascript',
        'php' => 'text/plain',
        'serialized' => 'application/vnd.php.serialized',
        'xml' => 'application/xml'
    ];

    //将所有的请求方法都放在一个数组中
    public $request=[];


    public $_get=[];


    public $_post=[];

    public $_put=[];

    public $_delete=[];

    public $_options=[];

    public $_patch=[];

    public $_head=[];
   
    /**
     * 构造函数，完成框架的权限及CI的初始化
     *
     * @param  int  islogin   [0|1]
     * @return void
     */
    public function __construct()
    {
        
        parent::__construct();

        //获取状态码
        $this->stateHttpCode=$this->getStateHttpCode('stateCode');

        $this->_check_ci_php();

        $this->request = new stdClass(); //定义一个空对象 ，主要是想使用->这种操作方便 基类

       

    }

    
    /**
     * 检查CI版本和PHP版本
     *
     * @return void
     */
    public function _check_ci_php()
    {
        //php版本小于5.6则报错
        if(is_php('5.6')==FALSE) {
            
            throw new Exception('您当前PHP版本为'.PHP_VERSION.', 请使用高于5.5版本的PHP');
        }

        //判断ci版本 更好的支持一些 新方法
        if (!in_array(CI_VERSION,array('3.1.9','3.1.5','4.0.1')))
        {
            throw new Exception('您的CI版本低于3.1.5请使用最高版本');
        }
    }

    /**
     * 检查是否http请求还是https请求
     *
     * @return void
     */
    public function _check_http_https()
    {

    }

    /**
     * 获取请求的方法
     *
     * @return void
     */
    public function getMethod()
    {   

        $method=$this->input->post['_method'];
        //判断是否存在post请求
        if(empty($method))
        {
            //判断是否存在put和delete方法
            $method = $this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE');
            $method=strtolower($method);
            if(empty($method)) {
                
                $method=$this->input->method(FALSE); //获取当前的请求方法
            }
        }

        //恶意提交请求
        if(in_array($method,$this->_input_method))
        {
            $method='get';
        }

        return $method;

    }

    /**
     * 根据方发来获取特定的数组值
     *
     * @return void
     */
    public function getRequestArr($method='get',$xss=TRUE)
    {
       
        $method_arr=['delete', 'put', 'options', 'patch', 'head'];

        if(in_array($method,$method_arr))
        {
            //必须是合适的post数据样式 不然就得使用 php://input
            return $this->input->input_stream(NULL,$xss);
        }

        if(strtolower($method)=='get')
        {
            return $this->input->get(NULL,$xss);  //获取所有的请求参数
        }

        if(strtolower($method)=='post')
        {
            return $this->input->post(NULL,$xss);  //获取所有的请求参数
        }

        return NULL;
    }


    /**
     * 将获取的数组进行转换成普通的键值对形式(可要可不要)
     *
     * @param string $method
     * @return void
     */
    public function to_ArrayParse($data=[],$type='array')
    {
        
        
        $var=[];
        if(is_array($data) && !empty($data)) {

            foreach ($data as $key=>$vals) {

                
                //$var[$key]=empty($data[$key])?'':$vals;
                if(is_object($vals) or  is_array($value)){

                    $var[$key]=$this->to_ArrayParse($value);

                }else{

                    $var[$key]=empty($data[$key])?'':$vals;
                }

            }


            return $var;
        }

        return NULL;
    }


    /**
     * 响应提示消息
     *
     * @return void
     */
    public function responseMessage($message,$code)
    {
        
    }

   

    /**
     * 判断是否Options请求
     */
     public function IsOptions()
     {
        return ($this->input->method(TRUE)=='OPTIONS')?TRUE:FALSE;
     }


     /**
     * 判断是Ajax请求 可以使用 is_ajax_request ci
     */
    public function IsAjax()
    {
        $ajax=$this->input->server('HTTP_X_REQUESTED_WITH');

       return ($ajax=='XMLHttpRequest' && !empty($ajax))?TRUE:FALSE;
    }


    /**
     * 判断是否Pjax请求
     */
    public function IsPjax()
    {
        $ajax=$this->input->server('HTTP_X_PJAX');

        return ($this->IsAjax() && !empty($ajax))?TRUE:FALSE;
    }



    /**
     * 判断是否Flash请求 这个 好多浏览器都不用了 
     */
    public function IsFlash()
    {
       $user_agrent=$this->input->server('HTTP_USER_AGENT');
       return (!empty($user_agrent) && stripos($user_agrent, 'Shockwave')!=FALSE or stripos($user_agrent, 'Flash')!=FALSE)?TRUE:FALSE;
    }


    /**
     * 判断是否delete请求
     */
    public function IsDelete()
    {
       return ($this->input->method(FALSE)=="delete")?TRUE:FALSE;
    }



    /**
     * 判断是否Get请求
     */
    public function IsGet()
    {
       return ($this->input->method(TRUE)=='GET')?TRUE:FALSE;
    }


    /**
     * 判断是否head请求
     */
    public function IsHead()
    {
       return ($this->input->method(TRUE)=='HEAD')?TRUE:FALSE;
    }


    /**
     * 判断是否POST请求
     */
    public function IsPost()
    {
       return ($this->input->method(TRUE)=='POST')?TRUE:FALSE;
    }

    /**
     * 是否一个一个put请求
     */
    public function IsPut(){

        return ($this->input->method(TRUE)=='PUT')?TRUE:FALSE;

    }

    /**
     * 单个定义put方法
     * 
     * @param [type] $key
     * @param boolean $xss
     * @return void
     */
    public function _put($key,$xss=TRUE)
    {

        if($this->input->method() === 'put')
        {
            if(empty($key))
            {
                return $this->input->input_stream(NULL,$xss);
            }

            return $this->input->input_stream($key,$xss);
        }
    }


    /**
     * 定义head方法
     *
     * @return void
     */
     public function _head()
     {
        parse_str(parse_url($this->input->server('REQUEST_URI'), PHP_URL_QUERY), $options);

        return $options;
     }


     /**
      * 定义delete方法
      *
      * @return void
      */
     public function _delete($key,$xss=TRUE)
     {
        if($this->input->method() === 'delete')
        {
            if(empty($key))
            {
                return $this->input->input_stream(NULL,$xss);
            }

            return $this->input->input_stream($key,$xss);
        }
     }


     /**
      * 单个获取get参数
      *
      * @param [type] $key
      * @param boolean $xss
      * @return void
      */
     public function _get($key,$xss=TRUE)
     {
        if($this->input->method() === 'get')
        {
            if(empty($key))
            {
                return $this->input->get(NULL,$xss);
            }

            return $this->input->get($key,$xss);
        }
     }


     /**
      * 获取类型
      *
      * @return void
      */
     public function getContentType()
     {
        $content_type=$this->input->server('CONTENT_TYPE');
        if(isset($content_type))
        {
            return $this->input->server('CONTENT_TYPE');

        }else if (empty($this->input->server('HTTP_CONTENT_TYPE'))){
            
            return $this->input->server('HTTP_CONTENT_TYPE');

        }else{

            return null;
        }

       
     }


     /**
      * 单个获取post参数
      *
      * @param [type] $key
      * @param boolean $xss
      * @return void
      */
     public function _post($key,$xss=TRUE)
     {
        if($this->input->method() === 'post')
        {
            if(empty($key))
            {
                return $this->input->post(NULL,$xss);
            }

            return $this->input->post($key,$xss);
        }
     }


     /**
      * 解析器
      * 当我们根据content-type获取过来的值 有可能位 json  xml  text  html 等
      * 
      * @return void
      */
     public function fromat()
     {

        $content_tpye=$this->getContentType();


     }


     /**
      * json转换
      *
      * @return void
      */
     public function to_JsonParse($data,$type='to')
     {
        try{
            
           if(strtoupper($type).'_json'=="TO_JSON"){

                $json=json_decode($data,TRUE);

                return $json;

           }else{

                return json_encode($data, JSON_UNESCAPED_UNICODE);

           }

        }catch(Exception $e){

            var_dump($e->getMessage());
        }
        
     }

     /**
      * xml转换
      *
      * @param [type] $data
      * @param [type] $structure
      * @param string $type
      * @return void
      */
     public function to_XmlParse($data = NULL,$type='TO',$structure = NULL, $basenode = 'xml')
     {
        try{
            
            if(strtoupper($type).'_XML'=="TO_XML"){
                
                return $data ? (array) simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA) : [];
            }else{
                
                if($data==NULL)
                {
                    echo "出";
                }

                if(empty($structure))
                {
                    $structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
                }

                if(!is_array($data))
                {
                    $data = (array) $data;
                }else{

                    foreach($data as $key => $value){
                        //true/fasle 0/1
                        if (is_bool($value))
                        {
                            $value = (int) $value;
                        }

                        if (is_numeric($key))
                        {
                            
                            $key = (singular($basenode) != $basenode) ? singular($basenode) : 'item';
                        }

                        $key = preg_replace('/[^a-z_\-0-9]/i', '', $key);

                        if ($key === '_attributes' && (is_array($value) || is_object($value)))
                        {
                            $attributes = $value;
                            if (is_object($attributes))
                            {
                                $attributes = get_object_vars($attributes);
                            }
            
                            foreach ($attributes as $attribute_name => $attribute_value)
                            {
                                $structure->addAttribute($attribute_name, $attribute_value);
                            }

                        }elseif (is_array($value) || is_object($value)){

                            $node = $structure->addChild($key);
            
                            
                            $this->to_XmlParse($value,'from',$node, $key);
                        }
                        else
                        {
                            
                            $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
            
                            $structure->addChild($key, $value);
                        }


                    }

                }


                return $structure->asXML();


            }
 
         }catch(Exception $e){
 
             var_dump($e->getMessage());
         }
     }


     /**
      * Oauth协议
      *
      * @return void
      */
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


     /**
      * 转换csv格式
      */
     public function to_CsvParse($data,$type='CSV',$delimiter = ',', $enclosure = '"')
     {
        try{
            
            if(strtoupper($type)=="CSV"){

                $delimiter=empty($delimiter)?',':$delimiter;
                $enclosure=empty($enclosure)?'"':$enclosure;

                return str_getcsv($data, $delimiter, $enclosure);
                
            }
 
         }catch(Exception $e){
 
             var_dump($e->getMessage());
         }
     }


     /**
      * 序列化格式
      *
      * @param [type] $data
      * @param string $type
      * @return void
      */
     public function to_SerializedParse($data,$type='serialized')
     {
        try{
            
            if(strtoupper($type)=="SERIALIZED"){

                return unserialize(trim($data));
            }
 
         }catch(Exception $e){
 
             var_dump($e->getMessage());
         }
     }






    

    


    
    

  

    /**
     * 获取状态码配置
     *
     * @param string $name  配置文件名
     * @return void
     */
    public function getStateHttpCode($name = '')
    {
        $info = [];
        if ($name != '') {
            // 从配置文件获取
            if (is_file(APPPATH.'app/config/'.ENVIRONMENT.DIRECTORY_SEPARATOR.$name.'php')) {
                $info = include APPPATH.'app/config/'.ENVIRONMENT.DIRECTORY_SEPARATOR.$name.'php';
            }
        }
        return $info;
    }
    
}

