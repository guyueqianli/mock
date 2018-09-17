<?php

// 引入配置文件
require './config/CaseConf.php';

/**
 * @name Mock_Case
 * @desc 生成异常数据
 * @author guyueqianli
 */
class Mock_Case {
    // json_decode 之后得到的 原始的要生成的异常数组
    public $arrJson;

    // json_decode 之后得到的 权重的数组
    public $arrWeight;

    // 最终的结果
    public $arrData = array();

    // conf 配置
    public $conf;

    // 要生成的case 数量
    public $num;

    // 统计 string 类型的数量
    public $strNum;

    // 统计 bool 类型的数量
    public $boolNum;

    // 统计 null 类型的数量
    public $nullNum;

    // 统计 int 类型的数量
    public $intNum;

    // 统计 array 类型的数量
    public $arrNum;

    // 统计 object 类型的数量
    public $objNum;

    /**
     * __construct
     *
     * @param array $caseConf 配置信息
     * @param array $arrJson
     * @param array $arrWeight
     *
     */
    public function __construct($caseConf, $arrJson, $arrWeight = array()) {
        $this->arrJson = $arrJson;
        $this->arrWeight = $arrWeight;

        if(empty($arrWeight)) {
            $this->arrWeight = $this->getArrWeightByJson($arrJson);
        }
        $this->conf = $caseConf;
        $this->num = 0;
        $this->strNum = $this->nullNum = $this->boolNum = $this->intNum = $this->arrNum = $this->objNum = 0;
    }

    /**
     * execute
     *
     * @param int $keyNum 修改的 key 数量
     * @return array mock后的多个value组成的数组
     */
    public function execute($keyNum = 0) {
        if($keyNum == 0) {
            $keyNum = $this->conf['defaultNum']['keyNum'];
        }
        ini_set('memory_limit', '2048M');
        // 得到全部的key  及其weight ==> $this->arrKeys
        $this->getAllKeys($this->arrWeight);
        // 按照weight把key排序
        $arrSortedKeys = $this->sortKeys();
        // 取前N个key
        $arrSliceKeys = $this->sliceKeys($arrSortedKeys, $keyNum);

        // 替换
        $this->mockKeys($arrSliceKeys);
        $retData = array(
            'error'     => 0,
            'errmsg'    => 'success',
            'data'      => array(),
        );
        $retData['data']['total'] = $this->num;
        $retData['data']['intNum'] = $this->intNum;
        $retData['data']['strNum'] = $this->strNum;
        $retData['data']['boolNum'] = $this->boolNum;
        $retData['data']['nullNum'] = $this->nullNum;
        $retData['data']['arrNum'] = $this->arrNum;
        $retData['data']['objNum'] = $this->objNum;
        $retData['data']['list'] = $this->arrData;
        return $retData;
    }

    /**
     * getArrWeightByJson
     *
     * @param array $arrJson
     * @desc 生成arrJson 对应的权重信息
     * @return array
     */
    public function getArrWeightByJson($arrJson) {
        $arrWeight = array();
        // 如果是对象类型的话, 则忽略
        // if(!is_array($arrJson) && !is_object($arrJson)) {
        if(!is_array($arrJson)) {
            return;
        }
        foreach ($arrJson as $k => $v) {
            if(is_array($v) || is_object($v)) {
                $arrWeight[$k] = $this->getArrWeightByJson($v);
            } else {
                $arrWeight[$k] = null;
            }
        }
        return $arrWeight;
    }

    /**
     * getAllKeys
     *
     * @param array $arrWeight
     * @param string $strParentKey 父key
     * @desc 取得全部的key，以及其weight
     * @return
     */
    public function getAllKeys($arrWeight, $strParentKey = '') {
        // strParentKey 不为空时，才放进数组中
        if(!empty($strParentKey)) {
            if(is_array($arrWeight) || is_object($arrWeight)) {
                // 默认先生成
                $this->arrKeys[$strParentKey] = 0;
            } else {
                $this->arrKeys[$strParentKey] = intval($arrWeight);
            }
        }
        if(!empty($arrWeight)) {
            foreach ($arrWeight as $k => $v) {
                $key = trim($strParentKey . "\t" . $k . "\t");
                $this->getAllKeys($v, $key);
            }
        }
    }

    /**
     * sortKeys
     *
     * @param
     * @desc 把key按照weight排序
     * @return array
     */
    public function sortKeys() {
        $arrTmp = $this->arrKeys;

        // 保存权重为 0, 1 的临时数组
        $arrTempWeight = $arrTempWeight1 = array();
        foreach($arrTmp as $key => $val) {
            if($val == 0) {
                $arrTempWeight[$key] = $val;
            } else {
                $arrTempWeight1[$key] = $val;
            }
        }
        // 只有值大于 0 的才进行排序
        asort($arrTempWeight1, SORT_NUMERIC);
        // 合并数组, 将没有设置权重的放在后面
        $arrSortedKeys = array_merge($arrTempWeight1, $arrTempWeight);
        return $arrSortedKeys;
    }

    /**
     * sliceKeys
     *
     * @param array arrSortedKeys 排序后的 weight 
     * @param int $keyNum 修改 key 的数量
     * @desc 取出需要被替换的key
     * @return array
     */
    public function sliceKeys($arrSortedKeys, $keyNum) {
        // 如果没有设置要修改的key数量, 则按全部算
        if($keyNum != 0) {
            $arrSliceKeys = array_slice($arrSortedKeys, 0, $keyNum);
        } else {
            $arrSliceKeys = $arrSortedKeys;
        }
        return $arrSliceKeys;
    }

    /**
     * firstMockJosn
     * 
     * @param array $arrAddTestCase 异常case生成入库的参数
     * @desc 整个 json 设为空 {}
     * @return
     */
    public function firstMockJosn($arrAddTestCase = array()) {
        // 首先将整个 json 设为空 {}
        $firstJson = $this->arrJson;
        $key = 'homeJson';
        $type = 'object';
        $caseDesc = array(
            'key' => $key,
            'oldValue' => '',
            'newValue' => '{}',
            'oldType' => $type,
            'desc' => '修改为: {}',
        );
        $typeExplain = sprintf('type[%s] key[%s] mock_type[ -> {} ]', $type, $key);
        $strJson = '{}';

        $arrAddTestCase['caseJson'] = $strJson;
        $arrAddTestCase['md5Case'] = md5($strJson);
        $arrAddTestCase['remark'] = $typeExplain;
        $arrAddTestCase['caseDescribe'] = json_encode($caseDesc);
        $arrAddTestCase['createTime'] = date('Y-m-d H:i:s');

        // 入库 当超过设定的生成 case 数时, 直接返回
        $retCase = $this->buildCase($arrAddTestCase);
        if(!$retCase) {
            return $this->arrData;
        }

        $this->arrData[] = array (
            'new_value' => '{}',
            'mock_type' => $typeExplain,
            'mock_case_desc' => $caseDesc['desc'],
        );
    }


    /**
     * mockKeys
     *
     * @param array arrSliceKeys 截取后的key
     * @desc 替换。　其实替换的是value
     * @return
     */
    public function mockKeys($arrSliceKeys) {
        // arrAddTestCase 这里其实是需要传入库的参数的, 但demo 不入库, 因此忽略
        // 首先将整个 json 设为空 {}
        $this->firstMockJosn();
        // $k 是key
        // $v 是权重
        foreach ($arrSliceKeys as $k => $v) {
            $this->mockOneKey($k);
        }
    }

    /**
     * mockOneKey
     *
     * @param string $key
     * @param array $arrAddTestCase 异常case生成入库的参数
     * @desc 换一个key
     * @return
     */
    public function mockOneKey($key, $arrAddTestCase = array()) {
        // 将 key 分割
        $arrKeys = explode("\t", $key);

        // 注意下面不要覆盖arrJson
        $arrJson = $this->arrJson;

        // print_r($arrKeys);
        $i = 0;
        foreach($arrKeys as $kk => $vv) {
            $i ++;
            if ($i == 1) {
                $value = &$arrJson[$vv];
            } else {
                $value = &$value[$vv];
            }
        }

        $mock_type = '';
        $oldValue = $value;

        if(is_string($value)) {
            $type = 'string';
            $this->strNum ++;
            // $mock_type = sprintf('type[%s] key[%s] value[%s]', $type, $key, $value);
            $mock_type = sprintf('type[%s] key[%s] ', $type, $key);
        }else if(is_null($value)) {
            $type = 'null';
            $this->nullNum ++;
            // $mock_type = sprintf('type[%s] key[%s] value[%s]', $type, $key, 'null');
            $mock_type = sprintf('type[%s] key[%s] ', $type, $key);
        }else if(is_bool($value)) {
            $type = 'bool';
            $this->boolNum ++;
            // $mock_type = sprintf('type[%s] key[%s] value[%s]', $type, $key, ($value ? 'true' : 'false'));
            $mock_type = sprintf('type[%s] key[%s] ', $type, $key);
        }else if(is_numeric($value)) {
            $type = 'number';
            $this->intNum ++;
            // $mock_type = sprintf('type[%s] key[%s] value[%s]', $type, $key, $value);
            $mock_type = sprintf('type[%s] key[%s] ', $type, $key);
        }else if(is_array($value)) {
            $type = 'array';
            $strValue = json_encode($value);
            $oldValue = $this->subString($strValue);
            $this->arrNum ++;
            // $mock_type = sprintf('type[%s] key[%s] value[%s]', $type, $key, $value);
            $mock_type = sprintf('type[%s] key[%s] ', $type, $key);
        }else if(is_object($value)) {
            $type = 'object';
            $strValue = json_encode($value);
            $oldValue = $this->subString($strValue);
            $this->objNum ++;
            // $mock_type = sprintf('type[%s] key[%s] value[%s]', $type, $key, $value);
            $mock_type = sprintf('type[%s] key[%s] ', $type, $key);
        }else {
            throw new exception('unknown valllllllue type : ' . $type);
        }

        if(!array_key_exists($type, $this->conf['strategy'])) {
            return false;
        }
        // 获取 conf 文件里的配置
        $typeConf = $this->conf['strategy'][$type];

        $arrMocks = $this->mockValueKey($value, $typeConf, $key);

        // 异常 case 描述
        $caseDesc = array();

        // todo 删除某个key
        // 当可以删除 key 时
        if($typeConf['toKey'] == 1) {
            // $typeExplain = sprintf('type[%s] key[%s] value[%s] mock_type[ -> del ]', $type, $key, $value);
            $typeExplain = sprintf('type[%s] key[%s] mock_type[ -> del ]', $type, $key);
            $mock_case_desc = '删除该key';
            // 获取原始的Json
            $oldJson = $this->arrJson;
            // print_r($arrKeys);
            $i = 0;
            $parent_value = $delValue = "";
            foreach($arrKeys as $kkk => $vvv) {
                $i ++;
                if ($i == 1) {
                    $delValue = &$oldJson[$vvv];
                } else {
                    $parent_value = &$delValue;
                    $delValue = &$delValue[$vvv];
                }
                $last_key = $vvv;
            }
            if($parent_value != "") {
                unset($parent_value[$last_key]);
            }else {
                unset($oldJson[$last_key]);
            }
            $caseDesc = array(
                'key' => $key,
                'oldValue' => $oldValue,
                'newValue' => '',
                'oldType' => $type,
                'desc' => $mock_case_desc,
            );

            $arrDelData = $arrAddTestCase;
            $arrDelData['caseJson'] = json_encode($oldJson);
            $arrDelData['md5Case'] = md5(json_encode($oldJson));
            $arrDelData['remark'] = $typeExplain;
            $arrDelData['caseDescribe'] = json_encode($caseDesc);
            $arrDelData['createTime'] = date('Y-m-d H:i:s');

            // 入库 当超过设定的生成 case 数时, 直接返回
            $retCase = $this->buildCase($arrDelData);
            if(!$retCase) {
                return $this->arrData;
            }

            $this->arrData[] = array (
                'new_value' => $oldJson,
                'mock_type' => $typeExplain,
                'mock_case_desc' => $mock_case_desc,
            );
        }
        // print '----------------------' . PHP_EOL;
        foreach ($arrMocks['new_value'] as $k => $v) {
            $typeExplain = $mock_type. ' mock_type[' . $arrMocks['mock_type'][$k] .']';
            $mock_case_desc = $arrMocks['mock_case_desc'][$k];

            // 注意这里是引用传值
            // php的引用真难以理解。
            $i = 0;
            foreach ($arrKeys as $kk => $vv) {
                $i ++;
                if ($i == 1) {
                    $value = &$arrJson[$vv];
                } else {
                    $value = &$value[$vv];
                }
            }

            $value  = $v;

            // 生成的值, 如果是数组或对象类型, 做截断处理
            $newValue = $value;

            // $tmp = $arrJson;
            // 这里又用到了arrJson
            $caseDesc = array(
                'key' => $key,
                'oldValue' => $oldValue,
                'newValue' => $newValue,
                'oldType' => $type,
                'desc' => $mock_case_desc,
            );

            $arrChangeData = $arrAddTestCase;
            $arrChangeData['caseJson'] = json_encode($arrJson);
            $arrChangeData['md5Case'] = md5(json_encode($arrJson));
            $arrChangeData['remark'] = $typeExplain;
            $arrChangeData['caseDescribe'] = json_encode($caseDesc);
            $arrChangeData['createTime'] = date('Y-m-d H:i:s');

            // 入库 当超过设定的生成 case 数时, 直接返回
            $retCase = $this->buildCase($arrChangeData);
            if(!$retCase) {
                return $this->arrData;
            }
            $this->arrData[] = array (
                // 'new_value' => $tmp,
                'new_value' => $arrJson,
                'mock_type' => $typeExplain,
                'mock_case_desc' => $mock_case_desc,
            );
            unset($value);
            // unset($tmp);
        }
        // var_dump($this->arrData);
    }

    /**
     * mockValueKey
     *
     * @param string value
     * @param array $typeConf
     * @param string $key
     * @desc 更改 key 的value
     * @return array
     */
    public function mockValueKey($value, $typeConf, $key) {
        $arrRet = array (
            'new_value' => array (),
            'mock_type' => array (),
            'mock_case_desc' => array (),
        );

        // 获取 conf 配置里开启策略的配置 为 1
        $typeConfByOn = array();
        foreach($typeConf as $k => $v) {
            if($v == 1) {
                $typeConfByOn[] = $k;
            }
        }

        // 策略执行
        // 取最小值
        if(in_array('toMin', $typeConfByOn)) {
            $arrRet['new_value'][] = -PHP_INT_MAX;
            $arrRet['mock_type'][] = ' -> toMin ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 最小值 ';
        }
        // 取最大值
        if(in_array('toMax', $typeConfByOn)) {
            $arrRet['new_value'][] = PHP_INT_MAX;
            $arrRet['mock_type'][] = ' -> toMax ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 最大值 ';
        }
        // 改 srting 类型
        if(in_array('toString', $typeConfByOn)) {
            $arrRet['new_value'][] = strval($value);
            $arrRet['mock_type'][] = ' -> toString ';
            $arrRet['mock_case_desc'][] = ' 修改为 : string类型 ';
        }
        // 改 随机数
        if(in_array('toNumRandom', $typeConfByOn)) {
            $arrRet['new_value'][] = rand();
            $arrRet['mock_type'][] = ' -> toNumRandom ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 随机数 ';
        }
        // 改 Null 类型
        if(in_array('toNull', $typeConfByOn)) {
            $arrRet['new_value'][] = null;
            $arrRet['mock_type'][] = ' -> toNull ';
            $arrRet['mock_case_desc'][] = ' 修改为 : null类型 ';
        }
        // 改 空字符串 ""
        if(in_array('toNullString', $typeConfByOn)) {
            $arrRet['new_value'][] = "";
            $arrRet['mock_type'][] = ' -> toNullString ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 空字符串 ';
        }
        // 改 float 类型
        if(in_array('toFloat', $typeConfByOn)) {
            $arrRet['new_value'][] = $this->randomFloat($value);
            $arrRet['mock_type'][] = ' -> toFloat ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 浮点型 ';
        }
        // 改 int 类型
        if(in_array('toInt', $typeConfByOn)) {
            $arrRet['new_value'][] = intval($value);
            $arrRet['mock_type'][] = ' -> toInt ';
            $arrRet['mock_case_desc'][] = ' 修改为 : int类型 ';
        }
        // 改 随机字符串
        if(in_array('toStrRandom', $typeConfByOn)) {
            $arrRet['new_value'][] = $this->randomString();
            $arrRet['mock_type'][] = ' -> toStrRandom ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 随机字符串 ';
        }
        // 改 相反值
        if(in_array('toReverse', $typeConfByOn)) {
            $arrRet['new_value'][] = !$value;
            $arrRet['mock_type'][] = ' -> toReverse ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 相反值 ';
        }
        // 改 空数组 []
        if(in_array('toNullArr', $typeConfByOn)) {
            $arrRet['new_value'][] = array();
            $arrRet['mock_type'][] = ' -> toNullArr ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 空数组 ';
        }
        // 改 空对象 {}
        if(in_array('toNullObj', $typeConfByOn)) {
            $strNullObj = '{}';
            $arrRet['new_value'][] = json_decode($strNullObj);
            $arrRet['mock_type'][] = ' -> toNullObj ';
            $arrRet['mock_case_desc'][] = ' 修改为 : 空对象 ';
        }
        return $arrRet;
    }

    /**
     * randomFloat
     *
     * @param int val
     * @desc 生成 float  数
     * @return float
     */
    public function randomFloat($val) {
        $randfolat = 0.0001;
        $randfolat += $val;
        return $randfolat;
    }

    /**
     * subString
     *
     * @param string strValue
     * @desc 字符串截断
     * @return string
     */
    public function subString($strValue) {
        $retStr= $strValue;
        $strSize = mb_strlen($strValue);
        // 截断默认长度
        $defaultSize = 100;
        // 当 长度 >= defaultSize 时, 进行截断
        if($strSize >= $defaultSize) {
            $retStr = substr($strValue, 0, $defaultSize) . ' ...';
        }
        return $retStr;
    }


    /**
     * randomstring
     *
     * @param int 长度
     * @desc 生成随机字符串
     * @return string
     */
    public function randomString($size = 20) {
        $strRandom = $this->conf['defaultNum']['strRandom'];
        $strRand = '';
        $strRand .= '你';
        for ($i = 0; $i < $size; $i++) {
            $strRand .= $strRandom[rand(0, strlen($strRandom) - 1)];
        }
        return $strRand;
    }

    /**
     * buildCase
     *
     * @param array $arrAddTestCase 异常case生成入库的参数
     * @desc 生成case 入库 当超过设定的生成 case 数时，不再入库
     * @return string
     */
    public function buildCase($arrAddTestCase) {
        // 以下是入库相关的处理, 相关代码不一样, 不多再处理，仅供参考
        // 默认的生成 case 数量
        $defalueCaseNum = $this->conf['defaultNum']['caseNum'];

        // 生成固定case数 当超过固定数时, 返回false
        if(intval($defalueCaseNum) !== 0 && $this->num >= intval($defalueCaseNum)) {
            return false;
        }
        // 生成 异常Case 链接文件
        // 获取生成异常文件的链接
        // 入库封装 - 默认入库成功, 入库相关代码暂不考虑
        $ret = true;
        // 入库成功与失败的处理
        if (!$ret) {
            // 记录日志
            print("生成异常case 错误");
        } else {
            usleep(50);
            $this->num ++;
        }
        // 默认入库成功
        return true;
    }




}

class A {
    public $age = 13;
    public $name = 'guyueqianli';
}
class B {}
$objA = new A();
$objB = new B();

$arrJson = array(
    'errno' => 0,
    'errmsg' => 'success',
    'data' => array(
        array(
            'id' => 11,
            'name' => 'guyueqianli',
            'studuent' => true,
            'interest' => null,
            'objA' => $objA,
        ),
        array(
            'id' => 12,
            'name' => 'dyna',
            'studuent' => false,
            'interest' => array(
                'sleep',
                'eat',
            ),
            'objB' => $objB,
        ),
    ),
);
$arrWeight = array();

$a = new Mock_Case($caseConf, $arrJson, $arrWeight);
$b = $a->execute();
echo json_encode($b);

