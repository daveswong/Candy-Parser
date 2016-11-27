<?php
// 引入 Quicker.php 文件
require_once('./../Quicker.php');
// 实例化引擎
$engine = new Quicker();

// 渲染视图
$engine->parse('template');
