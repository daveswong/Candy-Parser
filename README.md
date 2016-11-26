# Quicker

> Quickly PHP Template Engine！ 
> Quicker 是一个精简而快速的原生PHP模板引擎。

## 1. 特性

 1. 精简，一个文件，一个class，不足10kb；
 2. 快速，抛弃 {...} 这种后端不需要，前端学不懂的东西，从 HTML 到 PHP 视图是后端的活儿，所以对你来说，原生的PHP语法一定是你最顺手的，也是运行最快的；
 3. 无限继承，一个模板引擎，其实只要有模板继承的特性就够用了，除此之外真的不应该增加学习成本，何况这么简单的一个模板引擎还是支持无限继承的；
 4. 输出控制，你可以使用 parse(...) 方法或者 fetch(...) 方法控制是直接输出还是仅获取编译后的 HTML 字符串；
 5. 视图片段，用 parse_string(...) 方法不使用模板文件直接输出字符串和变量可以输出视图片段。
 
## 2. 用法
 
### 2.1. 基本用法

``` php
<?php
// 引入模板引擎
require_once('Quicker.php');

// 实例化引擎
$engine = new Quicker();

// 解析模板
$engine->parse('template_name');
```
### 2.2. 加载配置

``` php
// 模板引擎配置
$config = array(
    'tpl_suffix'    => '.html.php', // 模板文件后缀
    'template_dir'  => './template/', // 模板路径
    'compile_dir'   => './compiles/' // 编译文件存储路径
);

$engine->setConfig($config);
```
### 2.3. 赋值

``` php
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

### 2.4. 解析和渲染

#### 2.4.1. 模板

``` php
// 直接渲染模板
$engine->parse('template_name', $data);

// 解析返回字符串而不直接渲染
$engine->fetch('template_name', $data);
```

#### 2.4.2. 字符串

``` php
// 直接输出一个字符串
$engine->parse_string(‘<p>Something! <?php echo date('Y-m-d') ?></p>’);

// 模板字符串，里面可以混写 PHP ，但是要注意在 PHP 当中，如果使用双引号包裹字符串，内部的函数和变量是会解析的
$template_string = ‘<p>Something! <?php echo title ?></p>’;

// 赋值输出字符串模板
$engine->parse_string($template_string, $data);

$engine->fetch_string($template_string, $data);
```

## 3. 模板视图文件

### 3.1. 单独的视图文件

单独的视图文件直接按照 HTML + PHP 混编的方式编写就可以了，除了文件后缀需要与引擎设置一致，别的就不需要了，默认的后缀是 .html.php ，实际上还是 PHP 文件，但是必须写成 template.html.php 的形式，在调用的时候则不需要再写入后缀了。

``` php
// it will find template.html.php from &this->config['template_dir'].'path/'
$engine->parse('path/template', $data);
```

### 3.2. 继承模板

使用继承模板只需要 @extends(...) 就可以了

1. @extends(layout) // 继承 template_dir 中的 layout.html.php 模板，这个必须在模板的第一行，最好是单独一行，模板文件位置索引跟前面讲的 parse(..) 方法中的一样，还是 path/文件名 ，没有后缀；
2. @block(title)@ // 在 layout.html.php 中，创建一个名为 title 的区块；
3. @block(title)...@end // 这是一个在 template.html.php 中的完整的名为 title 的区块，它内部的内容会替换掉 layout 中的 @block(title)@ 。

#### 3.2.1. 视图模板 template.html.php

``` php
@extends(layout)

@block(title)Page Name@end

@block(pagestyle)
	<link rel="stylesheet" type="text/css" href="app-template.css" />
	<style>
		body {
			// ...
		}
	</style>
@end

@block(pageheader)
	Hello World!
@end

@block(pagecontent)
	<p>something else...</p>
	...
@end

@block(pagescript)
	<script>
		// ...
	</script>	
@end
```

#### 3.2.2. 布局模板 layout.html.php

``` php
@extends(base)

@block(style)

	<link rel="stylesheet" type="text/css" href="app-layout.css" />
	@block(pagestyle)@

@end

@block(container)
	<div id="sidebar">
	...
	</div>
	<div id="content">
		<h1>@block(pageheader)@</h1>
		<hr/>
		<div id="pagecontent">
			@block(pagecontent)@
			<hr/>
			<div>
				content info
			</div>
		</div>
	</div>
@end

@block(script)
	<script src="js/lapp-ayout.js"></script>
	<script>
		// layout script
	</script>
	@block(pagescript)@
@end

@block(footer)
	something
@end
```

#### 3.2.3. 基础模板 base.html.php

``` html
<!DOCTYPE html>

<html lang="en">

	<head>
		<meta charset="utf-8">
		<title>@block(title)@ | Site Name</title>
		<link rel="stylesheet" type="text/css" href="app.css" />
		@block(style)@
	</head>
	
	<body>
		<div id="navbar">
		</div>
		<div id="container">
			@block(container)@
		</div>
		<footer>
			@block(footer)@
		</footer>
		
		<script src="js/jquery-1.11.2.min.js?v=1.11.0"></script>
		<script src="js/app.js"></script>
		@block(script)@
	</body>
	
</html>
```

#### 3.2.4. 生成的编译文件

``` html
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Page Name | Site Name</title>
	<link rel="stylesheet" type="text/css" href="app.css" />
	<link rel="stylesheet" type="text/css" href="app-layout.css" />
	<link rel="stylesheet" type="text/css" href="app-template.css" />
	<style>
		body {
			// ...
		}
	</style>
</head>

<body>
	<div id="navbar">
	</div>
	<div id="container">
		<div id="sidebar">
			...
		</div>
		<div id="content">
			<h1>
				Hello World!
			</h1>
			<hr/>
			<div id="pagecontent">
				<p>something else...</p>
				...
				<hr/>
				<div>
					content info
				</div>
			</div>
		</div>
	</div>
	<footer>
		something
	</footer>
	<script src="js/jquery-1.11.2.min.js?v=1.11.0"></script>
	<script src="js/app.js"></script>
	<script src="js/lapp-ayout.js"></script>
	<script>
		// layout script
	</script>
	<script>
		// ...
	</script>
</body>

</html>
```

## 4. 在 CodeIgniter 中使用

### 4.1. 引用和配置

你可以在任意一个控制器中加载一个 Quicker 引擎，然后在控制器内部创建一个变量，并实例化 Quicker 类到这个变量中，并且进行配置和全局赋值。

下面是一个前端的公共控制器，我把它放在了 APPPATH/common/ 目录中，这个目录在前端不显示

``` php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FrontController extends CI_Controller {

	// 新建一个模板引擎的变量，然后你就可以在这个类或者继承的类中通过 $this->quick 使用它
	protected $quick;

	// 在构造函数中引入 Quicker 并进行实例化和配置，也可以在这里进行全局赋值
	public function __construct()
	{
		parent::__construct();

		// 引入 Quicker.php
		require_once(APPPATH.'third_party/Quicker/Quicker.php');
		// 实例化
		$this->quick = new Quicker();
		// 设置 Quicker
		$this->quick->setConfig(array(
			'template_dir'	=> VIEWPATH,
			'compile_dir'	=> APPPATH.'cache/compiles/'
		));
		// 全局赋值
		$this->quick->assign('site_option', array(
			'site_name'	=> 'UltUX.COM',
			'site_seo_keywords'	=> 'php ci template and so on'
		));

		// 加载 CI 资源
		$this->load->helper(array('url'));
	}
}
```

### 4.2. 控制器中赋值和渲染视图
这是在 APPPATH/controller/ 目录中的控制器，你的每一个控制器都可以从 common 目录中引入一个公共控制器来控制某个模块的行为。

``` php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// 引用前端模块公共控制器，并且继承它
require_once(APPPATH.'common/FrontController.php');
class Welcome extends FrontController {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	
	// 构造函数，必须继承上一级控制器的构造函数
	public function __construct()
	{
		// 这是必须的！
		parent::__construct();
	}
	public function index()
	{
		// 在这里给一个测试的赋值
		$this->quick->assign('test', 'Test is ok!');

// 用 Nowdoc 结构创建字符串模板，而不是去加载一个视图文件
// 你也可以直接写单行字符串，但是要注意，如果你使用 "..." 包裹字符串，里面的原生 php 代码会被解析
$str = <<<'EOD'
The quick is loarded!<br>
<hr><?php echo site_url('blog'); ?>
<hr>
<pre>
<?php echo $test; ?>
<?php var_dump($site_option); ?>
</pre>
EOD;
		// 渲染视图
		$this->quick->parse_str($str);
	}
}
```

### 4.3. 目录结构的建议

``` stylus
project
	|- application
		|- cache
		|	|- compiles //compile_dir
		|	|- ...
		|- common //公共控制器目录
		|- controller
		|- ...
		|- third_party
		|	|- Quicker
		|	|	|- Quicker.php
		|	|- ...
		|- views //template_dir
		|- ...
```

## 5. 代码参考

源代码中有详尽注释，如有待商榷之处可以邮件联络。
