# Quicker

> Quickly PHP Template Engine！ 
> Quicker 是一个精简而快速的原生PHP模板引擎。

## 特性

 1. 精简，一个文件，一个class，不足10kb；
 2. 快速，抛弃 {...} 这种后端不需要，前端学不懂的东西，从 HTML 到 PHP 视图是后端的活儿，所以对你来说，原生的PHP语法一定是你最顺手的，也是运行最快的；
 3. 无限继承，一个模板引擎，其实只要有模板继承的特性就够用了，除此之外真的不应该增加学习成本，何况这么简单的一个模板引擎还是支持无限继承的；
 4. 输出控制，你可以使用 parse(...) 方法或者 fetch(...) 方法控制是直接输出还是仅获取编译后的 HTML 字符串；
 5. 视图片段，用 parse_string(...) 方法不使用模板文件直接输出字符串和变量可以输出视图片段。
 
## 用法
 
### 基本用法

``` stylus
<?php
// 引入模板引擎
require_once('Quicker.php');

// 实例化引擎
$engine = new Quicker();

// 解析模板
$engine->parse('template_name');
```
### 加载配置

``` stylus
// 模板引擎配置
$config = array(
    'tpl_suffix'    => '.html.php', // 模板文件后缀
    'template_dir'  => './template/', // 模板路径
    'compile_dir'   => './compiles/' // 编译文件存储路径
);

$engine->setConfig($config);
```
### 赋值

``` stylus
$data = array(
	'title'		=> 'Quicker',
	'content'	=> 'Quickly PHP Template Engine',
	'array_var'	=> array('one', 'two', 'three')
);

// 使用 assign() 方法赋值关联数组
$engine->assign($data);

// 使用 assign() 方法赋值键值对，如果重复赋值一个键名，后面的就会覆盖前面的
$engine->assign(‘title’, 'the title has changed');

// 直接在 parse(...) 方法中使用第二个参数
$engine->parse('template_name', $data);
/*
 * 1. $data 必须是关联数组；
 * 2. 如果上面的赋值操作同时存在，经过两次重新赋值，那么最终 $title = 'Quicker' 
 */
```

### 解析和渲染

#### 模板

``` stylus
// 直接渲染模板
$engine->parse('template_name', $data);

// 解析返回字符串而不直接渲染
$engine->fetch('template_name', $data);
```
#### 字符串

``` stylus
// 直接输出一个字符串
$engine->parse_string(‘<p>Something! <?php echo date('Y-m-d') ?></p>’);

// 模板字符串，里面可以混写 PHP
$template_string = ‘<p>Something! <?php echo title ?></p>’;

// 赋值输出字符串模板
$engine->parse_string($template_string, $data);

$engine->fetch_string($template_string, $data);
```

## 代码参考

源代码中有详尽注释，如有待商榷之处可以邮件联络。
