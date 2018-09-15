<?php
// 生成异常数据的配置信息

// caseConf 总数组
$caseConf = array();

// defaultNum 默认配置
$caseConf['defaultNum'] = array();

// keyNum 默认修改 key 的数量 0 - 暂没有这个策略
$caseConf['defaultNum']['keyNum'] = 0;

// caseNum 默认生成case数
$caseConf['defaultNum']['caseNum'] = 0;

// strRandom 随机string
$caseConf['defaultNum']['strRandom'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.,;!@//$%^&*()_=-[]{}?';


// 策略配置
$caseConf['strategy'] = array();

// number 类型 1 - 代表有这个策略, 0 - 无
$caseConf['strategy']['number'] = array();
// 最小值 (负数)
$caseConf['strategy']['number']['toMin'] = 1;
// 最大值 
$caseConf['strategy']['number']['toMax'] = 1;
// 改 srting 类型
$caseConf['strategy']['number']['toString'] = 1;
// 改 随机数
$caseConf['strategy']['number']['toNumRandom'] = 1;
// 改 Null
$caseConf['strategy']['number']['toNull'] = 1;
// 改 空字符串 ""
$caseConf['strategy']['number']['toNullString'] = 1;
// 改 float 类型
$caseConf['strategy']['number']['toFloat'] = 1;
// 改 key 的值 (其实就是删除这个key)
$caseConf['strategy']['number']['toKey'] = 1;

// string 类型 
$caseConf['strategy']['string'] = array();
// 改 int 类型
$caseConf['strategy']['string']['toInt'] = 1;
// 改 随机字符串
$caseConf['strategy']['string']['toStrRandom'] = 1;
// 改 Null
$caseConf['strategy']['string']['toNull'] = 1;
// 改 空字符串 ""
$caseConf['strategy']['string']['toNullString'] = 1;
// 改 key 的值 (其实就是删除这个key)
$caseConf['strategy']['string']['toKey'] = 1;

// bool 类型 
$caseConf['strategy']['bool'] = array();
// 改 int 类型
$caseConf['strategy']['bool']['toInt'] = 1;
// 改 随机字符串
$caseConf['strategy']['bool']['toStrRandom'] = 1;
// 改 Null
$caseConf['strategy']['bool']['toNull'] = 1;
// 改 空字符串 ""
$caseConf['strategy']['bool']['toNullString'] = 1;
// 改 相反值
$caseConf['strategy']['bool']['toReverse'] = 1;
// 改 key 的值 (其实就是删除这个key)
$caseConf['strategy']['bool']['toKey'] = 1;

// null 类型 
$caseConf['strategy']['null'] = array();
// 改 int 类型
$caseConf['strategy']['null']['toInt'] = 1;
// 改 随机字符串
$caseConf['strategy']['null']['toStrRandom'] = 1;
// 改 空字符串 ""
$caseConf['strategy']['null']['toNullString'] = 1;
// 改 key 的值 (其实就是删除这个key)
$caseConf['strategy']['null']['toKey'] = 1;

// array 类型 
$caseConf['strategy']['array'] = array();
// 改 空数组 []
$caseConf['strategy']['array']['toNullArr'] = 1;
// 改 空对象 {}
$caseConf['strategy']['array']['toNullObj'] = 1;
// 改 空字符串 ""
$caseConf['strategy']['array']['toNullString'] = 1;
// 改 key 的值 (其实就是删除这个key)
$caseConf['strategy']['array']['toKey'] = 0;

// object 类型 
$caseConf['strategy']['object'] = array();
// 改 空数组 []
$caseConf['strategy']['object']['toNullArr'] = 1;
// 改 空对象 {}
$caseConf['strategy']['object']['toNullObj'] = 1;
// 改 空字符串 ""
$caseConf['strategy']['object']['toNullString'] = 1;
// 改 key 的值 (其实就是删除这个key)
$caseConf['strategy']['object']['toKey'] = 0;







