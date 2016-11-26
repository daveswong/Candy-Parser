<?php
/**
 * Quicker PHP Template Engine
 *
 * @package Package Name
 * @subpackage  Subpackage
 * @category    Category
 * @author  ult-ux@outlook.com
 * @link    http://ultux.com
 */
class Quicker
{
    /**
    * Config for class manipulation
    *
    * @var  array
    */
    private $config = array(
            'tpl_suffix'    => '.html.php',
            'template_dir'  => './template/',
            'compile_dir'   => './compiles/'
    );

    /**
    * Data for class manipulation
    *
    * @var  array
    */
    private $data = array();

    /**
    * 为模板设置默认的配置参数
    *
    * @param    
    * @return  
    */
    public function __construct()
    {
    }

    /**
    * 为模板赋值
    *
    * @param    string|array    $var    变量名或者关联数组
    * @param    string|array    $value  $var 为 string 时变量的值
    * @return  
    */
    public function assign($var, $value = null)
    {
        if (is_array($var)) {
            // 处理数组变量
            $this->data = array_merge($this->data, $var);
        } else {
            // 处理键值对变量
            $this->data[$var] =& $value;
        }
    }

    /**
    * 配置模板参数
    *
    * @param    array   $config 关联数组
    * @return  
    */
    public function setConfig($config)
    {
        if (!is_array($config)) {
            // return;
            // 使用 die() 代替 return ，提供错误信息；
            die('配置参数不正确，应该使用关联数组');
        }
        // 合并配置的数组
        $this->config = array_merge($this->config, $config);
    }

    /**
    * 输出解析后的模板
    * 
    * @param    string  $template_name  模板文件路径文件名，不包括扩展名，相对路径 $config['template_dir']
    * @param    array   $data           关联数组，赋值的数据
    * @return  
    */
    public function parse($template_name, $data = null)
    {
        echo $this->fetch($template_name, $data);
    }

    /**
    * 解析模板
    * 
    * @param    string  $template_name  模板文件路径文件名，不包括扩展名，相对路径 $config['template_dir']
    * @param    array   $data           关联数组，赋值的数据
    * @return  
    */
    public function fetch($template_name, $data = null)
    {
        // 模板赋值
        $this->assign($data);
        // 重构模板
        $extend_template = $this->extendTemplate($template_name);
        // 清理没有被继承的 @block(...)
        $template =  $this->clearTemplate($extend_template);
        // 生成编译文件
        $compile_file = $this->generateCompile($template_name, $template);
        // 渲染编译文件
        return $this->render($compile_file);
    }

    /**
    * 输出解析后的字符串模板
    * 
    * @param    string  $template_string    字符串视图片段
    * @param    array   $data               关联数组，赋值的数据
    * @return  
    */
    public function parse_str($template_string, $data = null)
    {
        echo $this->fetch_str($template_string, $data);
    }

    /**
    * 解析字符串模板，相比较引用模板文件，不需要对模板进行继承重构
    * 
    * @param    string  $template_string    字符串视图片段
    * @param    array   $data               关联数组，赋值的数据
    * @return  
    */
    public function fetch_str($template_string, $data = null)
    {
        // 模板赋值
        $this->assign($data);
        // 生成编译文件
        $compile_file = $this->generateCompile('template_string', $template_string);
        // 渲染编译文件
        return $this->render($compile_file);
    }
    
    /**
    * 生成编译文件并返回编译文件路径
    * 
    * @param    string   $compile_name  文件路径名
    * @param    string   $template      模板内容字符串
    * @return   string
    */
    private function generateCompile($compile_name, $template)
    {
        // 检查 compile_dir 是否存在，如果不存在则创新路径
        if(!is_dir($this->config['compile_dir']))
            mkdir($this->config['compile_dir']);
        // 处理文件名
        $compile_name = md5($compile_name);
        // 编译文件路径
        $compile_file = $this->config['compile_dir'].$compile_name.'.php';
        // 写入编译文件
        file_put_contents($compile_file, $template);
        // 返回编译文件路径
        return $compile_file;
    }

    /**
    * 从编译文件赋值解析渲染视图，返回 html
    * 该方法请参照 PHP手册 流程控制 include 章节 http://php.net/manual/zh/function.include.php 
    * Example #6 使用输出缓冲来将 PHP 文件包含入一个字符串
    * 
    * @param    string   $compile_file 编译文件路径
    * @return   string
    */
    private function render($compile_file)
    {
        if (is_file($compile_file)) {
            ob_start();
            // 从赋值的数据中释放变量
            foreach ($this->data as $key=>$val) {
                ${$key} = $val;
            }
            include $compile_file;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return false;
    }

    /**
    * 获取模板文件的内容
    * 
    * @param    string   $template_name 模板文件路径文件名，不包括扩展名，相对路径 $config['template_dir']
    * @return   string
    */
    private function getTemplate($template_name)
    {
        $template_file = $this->config['template_dir'].$template_name.$this->config['tpl_suffix'];
        if (!file_exists($template_file)) {
            die('找不到模板文件：'.$template_file);
        }
        $template = file_get_contents($template_file);
        return $template;
    }

    /**
    * 根据模板继承重构模板
    * 
    * @param    string   $template_name 模板文件路径文件名，不包括扩展名，相对路径 $config['template_dir']
    * @return   string
    */
    private function extendTemplate($template_name)
    {
        // 加载模板内容
        $template = $this->getTemplate($template_name);
        // 检查模板是否有继承模板
        $pattern = '/^@extends\(.*?\){1}/sm';
        preg_match($pattern, $template, $matches);
        // 重构模板方法是循环调用的，如果没有继承模板或者最后一次调用将直接输出模板的字符串内容
        if (!$matches) {
            return $template;
        }
        // 获取继承名称
        $extends_name = substr($matches[0], 9, -1);
        // 根据 @extends 模板名称查找 @extends 继承文件
        $extends_file = $this->config['template_dir'].$extends_name.$this->config['tpl_suffix'];
        // 如果找不到继承文件则抛出错误
        if (!file_exists($extends_file)) {
            die('找不到模板继承文件：'.$extends_file);
        }
        // 循环
        $extends = $this->extendTemplate($extends_name);
        // 替换 Blocks 以重构模板
        $extend_template = $this->extendBlocks($extends, $template);
        return $extend_template;
    }

    /**
    * 替换继承文件的 Blocks
    * 
    * @param    string   $extends   继承模板的内容字符串
    * @param    string   $template  模板的内容字符串
    * @return   string
    */
    private function extendBlocks($extends, $template)
    {
        $blocks = $this->getTemplateBlocks($template);
        $pattern = '/@block\(.*?\){1}/sm';
        // 创建一个回调函数，涉及到调用函数外部变量 参照 PHP手册 匿名函数部分
        $callback = function ($matches) use ($blocks) {
            $key = substr($matches[0], 7, -1);
            if (array_key_exists($key, $blocks)) {
                $replacement = $blocks[$key];
                return $replacement;
            } else {
                return $matches[0];
            }
        };
        $result = preg_replace_callback($pattern, $callback, $extends);
        return $result;
    }

    /**
    * 从模板中提取 Blocks 到一个数组
    * 
    * @param    string   $template  模板的内容字符串
    * @return   array
    */
    private function getTemplateBlocks($template)
    {
        $pattern = '/^@block\(.*?\){1}[\s\S]*?@end/sm';
        if (preg_match_all($pattern, $template, $matches)) {
            foreach ($matches[0] as $val) {
                if (preg_match('/^@block\(.*?\){1}/m', $val, $tag)) {
                    $key = substr($tag[0], 7, -1);
                    $content = substr($val, strlen($key) + 8, -4);
                    $blocks[$key] = $content;
                }
            }
            return $blocks;
        }
    }

    /**
    * 清理没有被继承的 @block(...)
    * 
    * @param    string   $template  模板的内容字符串
    * @return   array
    */
    public function clearTemplate($template)
    {
        $pattern = '/@block\(.*?\){1}/sm';
        $result = preg_replace($pattern, '', $template);
        return $result;
    }

    // debug 检查 Quicker 内部变量
    public function checkVar($var_name)
    {
        # code...
        echo '<pre>';
        var_dump($this->{$var_name});
        echo '</pre>';
    }
}
