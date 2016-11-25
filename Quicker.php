<?php
/**
 * Super Class
 *
 * @package Package Name
 * @subpackage  Subpackage
 * @category    Category
 * @author  Author Name
 * @link    http://example.com
 */
class Quicker
{
    /**
    * Data for class manipulation
    *
    * @var array
    */
    private $config = array();

    private $data = array();

    /**
    * Encodes string for use in XML
    *
    * @param   string  $str    Input string
    * @return  string
    */
    public function __construct()
    {
        // 默认设置
        $default_config = array(
            'tpl_suffix'    => '.html.php',
            'template_dir'  => './template/',
            'compile_dir'   => './compiles/'
        );
        $this->config =& $default_config;
    }
   
    // 赋值
    public function assign($var, $value = null)
    {
        if (is_array($var)) {
            $this->data =& $var;
        } else {
            $this->data[$var] =& $value;
        }
    }

    // 设置
    public function setConfig($config)
    {
        if (!is_array($config)) {
            die('配置参数不正确，应该是带有键名的多维数组');
        }
        $this->config =& $config;
    }

    public function parse($template_name, $data = null)
    {
        echo $this->fetch($template_name, $data);
    }

    public function fetch($template_name, $data = null)
    {
        //
        $this->assign($data);
        $template = $this->extendTemplate($template_name);
        $template = $this->clearTemplate($template);
        $compile_file = $this->generateCompile($template_name, $template);
        return $this->_render($compile_file);
    }

    // 渲染字符串模板
    public function parse_string($template_string, $data = null)
    {
        echo $this->fetch_string($template_string, $data);
    }

    // 解析字符串模板
    public function fetch_string($template_string, $data = null)
    {
        $this->assign($data);
        $compile_file = $this->generateCompile('template_string', $template_string);
        return $this->_render($compile_file);
    }

    // 从模板文件获取内容
    private function getTemplate($template_name)
    {
        $template_file = $this->config['template_dir'].$template_name.$this->config['tpl_suffix'];
        if (!file_exists($template_file)) {
            die('找不到模板文件：'.$template_file);
        }
        $template = file_get_contents($template_file);
        return $template;
    }
    // 赋值并渲染模板
    private function _render($compile_file)
    {
        if (is_file($compile_file)) {
            ob_start();

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

    // 生成编译文件
    private function generateCompile($compile_name, $template)
    {
        $compile_file = $this->config['compile_dir'];
        $compile_file .= md5($compile_name);
        $compile_file .= '.php';
        file_put_contents($compile_file, $template);
        return $compile_file;
    }

    // 扩展模板
    private function extendTemplate($template_name)
    {
        // 加载模板内容
        $template = $this->getTemplate($template_name);

        // 检查模板是否有 @extends
        $pattern = '/^@extends\(.*?\){1}/sm';
        preg_match($pattern, $template, $matches);
        // 模板没有 @extends 时直接输出模板内容
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
        // 找到的话就读取继承文件
        $extends = $this->extendTemplate($extends_name);
        // 替换 Block
        $extend_template = $this->extendBlocks($extends, $template);
        return $extend_template;
    }
     
    // 替换继承文件的 Blocks
    private function extendBlocks($extends, $template)
    {
        $blocks = $this->getTemplateBlocks($template);
        $pattern = '/@block\(.*?\){1}/sm';
        $callback = function ($matches) use ($blocks) {
            $key = substr($matches[0], 7, -1);
            if (array_key_exists($key, $blocks)) {
                $replacement = $blocks[$key];
                return $replacement;
            } else {
                return $matches[0];
            }
        };
        $fetch = preg_replace_callback($pattern, $callback, $extends);
        return $fetch;
    }

    // 获取模板继承 Blocks
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

    // 清理无效 @block(...)
    public function clearTemplate($template)
    {
        $pattern = '/@block\(.*?\){1}/sm';
        $fetch = preg_replace($pattern, '', $template);
        return $fetch;
    }
}
