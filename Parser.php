<?php
namespace Candy\library;

/**
 * 模板解析引擎
 *
 * @package     Candy 这是一个 PHP 框架组件库
 * @subpackage  library
 * @category    library
 * @author      ult-ux@outlook.com
 * @link        http://ultux.com
 */
class Parser
{
    /**
     * Config for class manipulation
     *
     * @var array
     */
    private $config = array(
                'template_dir' => 'template'.DIRECTORY_SEPARATOR, // 模板文件夹
                'compile_dir' => 'compile'.DIRECTORY_SEPARATOR, // 编译文件生成文件夹
                'tpl_suffix' => '.tpl', // 模板文件后缀
                'enable_cache' => 600 // 是否开启静态缓存，0 代表不开启，正整数代表缓存刷新时间，如果你正在 Debug 阶段建议关闭缓存
            );

    /**
     * Data for class manipulation
     *
     * @var array
     */
    private $data = array();

    /**
     * The regular expression for handling blocks
     *
     * @var string
     */
    private $patterns = '/<block (?<name>[a-zA-Z0-9]+)>(?<content>([\s\S]*?(<block[^>]*>((?1)|[\s\S])*<\/block>)*[\s\S]*?)*)<\/block>/ims';

    /**
     * Blocks taken from the template
     *
     * @var array
     */
    private $blocks = array();

    /**
     * Set the class configuration parameters
     * 设置解析器配置参数
     *
     * @param   array   $config Associative array
     * @return  object
     */
    public function set($config = array())
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Assign variables to the template
     * 赋值模板
     *
     * @param   mixed   $var    The variable name
     * @param   string  $value
     * @return  object
     */
    public function assign($var, $value = null)
    {
        if (is_array($var)) {
            foreach ($var as $key=>$value) {
                return $this->assign($key, $value);
            }
        } elseif (is_string($var)) {
            $this->data[$var] = $value;
        }
        return $this;
    }

    /**
     * Output the rendered template to the browser
     * 将渲染后的模板输出到浏览器
     *
     * @param   string  $template   template name
     * @param   array   $data       Associative array
     * @return  string
     */
    public function display($template, $data = array())
    {
        echo $this->fetch($template, $data);
    }

    /**
     * Get the rendered template
     * 获取渲染后的模板
     *
     * @param   string  $template   template name
     * @param   array   $data       Associative array
     * @return  string
     */
    public function fetch($template, $data = array())
    {
        $this->assign($data);
        if (!is_dir($this->config['compile_dir'])) {
            mkdir($this->config['compile_dir']);
        }
        $compile_file = $this->config['compile_dir'].md5($template).$this->config['tpl_suffix'];
        if ((!is_file($compile_file)) or (time() - filemtime($compile_file)) > $this->config['enable_cache']) {
            file_put_contents($compile_file, $this->parseTemplate($template));
        }
        ob_start();
        foreach ($this->data as $key=>$value) {
            ${$key} = $value;
        }
        include $compile_file;
        $contents = ob_get_contents();
        ob_end_clean();
        // 未开启缓存时自动删除编译文件
        if (!$this->config['enable_cache']) {
            unlink($compile_file);
        }
        return $contents;
    }

    /**
     * Get the parsed template
     * 获取解析后的模板
     *
     * @param   string  $template   template name
     * @return  string
     */
    private function parseTemplate($template)
    {
        $template_str = $this->getTemplateString($template);
        if (!preg_match('/^<!extends (?<parent>[^ <>]+)>/ism', $template_str, $matches)) {
            return $this->extendBlocks($template_str);
        }
        $this->setBlocks($template_str);
        return $this->parseTemplate($matches['parent']);
    }

    /**
     * Get the string from the template file
     * 从模板文件中获取字符串
     *
     * @param   string  $template   template name
     * @return  string
     */
    private function getTemplateString($template)
    {
        if (is_file($file = 'template/'.$template.'.html')) {
            return file_get_contents($file);
        }
        return $template;
    }

    /**
     * Obtain the blocks from the template and cache it to the class variable
     * 从模板中获取块并缓存到类变量
     *
     * @param   string  $template_str   template string
     * @return  string
     */
    private function setBlocks($template_str)
    {
        if (preg_match_all($this->patterns, $template_str, $matches)) {
            foreach ($matches['content'] as $key=>$value) {
                $matches['content'][$key] = $this->extendBlocks($value);
            }
            $this->blocks = array_merge(array_combine($matches['name'], $matches['content']), $this->blocks);
        }
    }
    
    /**
     * Replace the block in the string with the block in the class variable
     * 用类变量中的 block 替换字符串中的块
     *
     * @param   string  $template_str   template string
     * @return  string
     */
    private function extendBlocks($template_str)
    {
        return preg_replace_callback($this->patterns, function ($matches) {
            if (isset($this->blocks[$matches['name']])) {
                return $this->blocks[$matches['name']];
            } else {
                return $matches['content'];
            }
        }, $template_str);
    }
}
