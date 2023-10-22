<?php
function  HIDE(){
     if (!isset($_SESSION['logged'])){
       session_start();
     }
     if ($_SESSION['logged'] != 'Administrator'){
           echo 'ТИМЧАСОВО НЕ ДОСТУПНО... СТОРІНКА В РОЗРОБЦІ!';
           exit;
     }
}

function phpAlert($msg, $location = '')
{
    if ($location == '') {
        echo '<script type="text/javascript">alert("' . $msg . '")</script>';
    } else {
        echo '<script language="javascript">alert("' . $msg . '");</script>';
        echo "<script>document.location = '$location'</script>";
    }
}

function console($msg)
{
        echo '<script type="text/javascript">console.log("' . $msg . '")</script>';
}

class MyColor
{
    public $ID;
    public $NAME;
    public $CSS_ANALOG;
    private $PARAM;

    function __construct($_id, $_name, $_param = "[oth]", $_css = '')
    {
        $this->ID = $_id;
        $this->NAME = $_name;
        $this->PARAM = $_param;
        $this->CSS_ANALOG = $_css;
    }

    function AppleTo($arr, $servId, $type = 1): bool
    {
        $uniq = false;

        $find = ($type == 1) ? ("[" . $servId . "]") : ("[" . $servId ."." .$type. "]");

        foreach ($arr as $c) {
            if (strpos($c->PARAM, $find) > -1) {
                $uniq = true;
                break;
            }
        }

        if ($uniq) {
            return strpos($this->PARAM, $find) > -1;
        } else {
            return strpos($this->PARAM, "[oth]") > -1;
        }

    }

    function Universal():bool{
        return strpos($this->PARAM, "[oth]") > -1;
    }
}

class ZDATA {
    public $ID = 0;
    public $SHOLOM_NUM = null;
    public $SOLD_NUM = null;
    public $DATE_IN;
    public $DATE_MAX;
    public $DATE_OUT;
    public $PHONE_OUT = '';
    public $PIP = '';
    public $REQ_OUT = '';
    public $TTN_IN = '';
    public $TTN_OUT = '';
    public $COMM = '';
    public $WORKER = '';
    public $KOMPLECT = array();

    function __construct($IN = null)
    {
        if(is_array($IN))
            $this->SET($IN);
        if (is_int($IN))
            $this->SET('ID', $IN);
    }

    public function SET($arr_or_servid, $color = null){
           if (is_null($color) && is_array($arr_or_servid)){
               $this->set_arr($arr_or_servid);
           }
           else if(!is_null($color)){
               $this->set_arr(array($arr_or_servid => $color));
           }
    }

    private function set_arr(array $in){
        if (isset($in['ID']))
            $this->ID = $in['ID'];
        if (isset($in['sholom_num']))
            $this->SHOLOM_NUM = $in['sholom_num'];
        if (isset($in['sold_number']))
            $this->SOLD_NUM = $in['sold_number'];
        if (isset($in['date_in']))
            $this->DATE_IN = $in['date_in'];
        if (isset($in['date_max']))
            $this->DATE_MAX = $in['date_max'];
        if (isset($in['date_out']))
            $this->DATE_OUT = $in['date_out'];
        if (isset($in['phone']))
            $this->PHONE_OUT = $in['phone'];
        if (isset($in['client_name']))
            $this->PIP = $in['client_name'];
        if (isset($in['reqv']))
            $this->REQ_OUT = $in['reqv'];
        if (isset($in['TTN_IN']))
            $this->TTN_IN = $in['TTN_IN'];
        if (isset($in['TTN_OUT']))
            $this->TTN_OUT = $in['TTN_OUT'];
        if (isset($in['comm']))
            $this->COMM = $in['comm'];
        if (isset($in['worker']))
            $this->WORKER = $in['worker'];

        //var_dump($in);

        if (isset($in['serv'])) {
            foreach ($in['serv'] as $id=>$tp) {
                foreach ($tp as $t=>$row) {
                    $this->KOMPLECT[$id][$t]['color'] = isset($row['color']) ? $row['color'] : -1;
                    $this->KOMPLECT[$id][$t]['count'] = isset($row['count']) ? $row['count'] : 1;
                    $this->KOMPLECT[$id][$t]['cost'] = isset($row['cost']) ? $row['cost'] : 0;
                }
            }
        }
    }


    public function GET_KOMPLECT($id = null, $name = 'cost', $type = 1):string{//ДЛЯ AJAX

        if (!is_null($id)) {
            if (isset($this->KOMPLECT[$id][$type][$name])) {
                return $this->KOMPLECT[$id][$type][$name];
            } else
                return '';
        }

        $out = '';

        if (count($this->KOMPLECT) > 0){
            foreach ($this->KOMPLECT as $i=>$iarr) {
                foreach ($iarr as $t => $row) {
                    if ($row['color'] != -1)
                        $out .= "&color_" . $i . "_" . $t . "=" . $row['color'];
                    $out .= "&count_" . $i . "_" . $t . "=" . $row['count'];
                    $out .= "&cost_" . $i . "_" . $t . "=" . $row['cost'];
                }
            }
        }

        return $out;
    }

}

function dateToNorm($in, $short = false):string{
    if (is_null($in))
        return '';
    $myDateTime = DateTime::createFromFormat('Y-m-d', $in);
    if ($short){
        return $myDateTime->format('d.m.y');
    }else{
        return $myDateTime->format('d.m.Y');
    }

}

function sumArray($in): float{
    $out = 0;

    foreach ($in as $i){
       $out += !is_array($i) ? $i : sumArray($i);
    }

    return $out;
}

function countArraysKey($in, array $ignoreKeys = null):int{

    if (!is_null($ignoreKeys))
        $ignoreKeys = array();

    $out = 0;

    foreach($in as $k=>$v){
        if (!is_array($v)){
            if (!in_array($k, $ignoreKeys)){
                $out += 1;
            }
        } else {
            $out += countArraysKey($v, $ignoreKeys);
        }
    }

    return $out;
}

function CostOut($in): string
{
    //Валідація сум
    $out = str_replace(',', '.', $in);
    $out = str_replace(' ', '', $out);

    if (is_numeric($out)) {

        $com = strpos($out, '.');

        if ($com > -1) {
            switch (strlen($out) - $com) {
                case 1:
                    return str_pad($out, strlen($out) + 2, '0', STR_PAD_RIGHT);
                case 2:
                    return str_pad($out, strlen($out) + 1, '0', STR_PAD_RIGHT);
                case 3:
                    return $out;
                default:
                    return substr($out, 0, $com + 3);
            }
        } else {
            return $out . ".00";
        }
    }

    return '0.00';
}

function inclAttr($atr, $in):bool{

    if ($in == 0 || $atr == $in)
        return true;
    else if ($in < 0)
        return false;

    $arr = array();
    $ost = $in % 2;
    $step = 1;

    for($i=$in; $step <= $in;$i = ($i - $ost) / 2){
        $ost = $i % 2;

        $arr[$step] = $ost;

        $step *= 2;
    }

    return isset($arr[$atr]) ? $arr[$atr] == 1:false;
}

class HTEL {

    private $include_arr = array();

    public $LEVEL = 0;

    public $TEXT = '';

    private $element_type = '';

    private $element_args = array();

    public $VARS = array();

    private $IS_EMPTY = false;

    function __construct(string $input = '', $variables_incl = null){

        if (trim($input) == ''){
                $this->IS_EMPTY = true;
                return;
        }

        if (!is_null($variables_incl)) {
            if (is_array($variables_incl)) {
                foreach ($variables_incl as $k => $vars) {
                    if (is_array($vars)) {
                        foreach ($vars as $kk => $in_arr) {
                            $this->VARS[$kk] = $in_arr;
                        }
                    } else if ($vars instanceof HTEL) {
                        $this->_include($vars);
                    } else {
                        $this->VARS[$k] = $vars;
                    }
                }
            } else {
                if (!is_null($variables_incl) && $variables_incl instanceof HTEL) {
                    $this->_include($variables_incl);
                } else {
                    $this->VARS[0] = $variables_incl;
                }
            }
        }
        //else {
        //    $this->VARS[0] = null;
        //}

        $text_split = explode('/', $input);

        if  (count($text_split) > 1){
            $input = $text_split[0];
            $this->TEXT = $text_split[1];
            for ($i=2; $i < count($text_split); $i++)
                $this->TEXT .= '/' . $text_split[$i];
        }

        $abbr = array();
        $abbr['.'] = 'class';
        $abbr['!'] = 'id';
        $abbr['@'] = 'href';
        $abbr['~'] = 'url';
        $abbr['?'] = 'name';
        $abbr['#'] = 'value';
        $abbr['*'] = 'type';
        $abbr['&'] = 'style';
        $abbr['$'] = 'placeholder';

        while (strpos($input, '  ') != false) {
            $input = str_replace('  ', ' ', $input);
        }

        $input = trim($input);

        $input = str_replace(array(' = ', '= ', ' ='), '=', $input);
        $input = str_replace(array(' + ', '+ ', ' +'), '+', $input);
        $input = str_replace('[ ', '[', $input);
        $input = str_replace(' ]', ']', $input);

        $input = str_replace('[r]', 'required', $input);
        $input = str_replace('[s]', 'selected', $input);
        $input = str_replace('[c]', 'checked', $input);
        $input = str_replace('[ro]', 'readonly', $input);
        $input = str_replace('[d]', 'disabled', $input);
        //placeholder

        //---------------------------------------

        $input = str_replace('==', '~', $input);

        $out = explode(' ', $input);

        $this->element_type = $out[0];

        for ($i = 1; $i < count($out); $i++) {//

            $val = explode('=', $out[$i]);

            $changed = false;

            $val[1] = $val[1] ?? '';
            $val[1] = str_replace('+', ' ', $val[1]);
            $val[1] = str_replace('~', '=', $val[1]);

            foreach ($abbr as $k => $v) {
                if ($val[0] == $k) {
                    $this->element_args[$v] = $val[1];

                    $changed = true;
                    break;
                }
            }

            if (!$changed && !empty($val[0])){
                $this->element_args[$val[0]] = $val[1];
            }
        }

        $this->IS_EMPTY = false;
    }

    function setAtr($atr_name, $val, $append = false){

        if ($append) {
            if (isset($this->element_args[$atr_name])) {
                $this->element_args[$atr_name] .= $val;
            } else {
                $this->element_args[$atr_name] = $val;
            }
        } else {
            $this->element_args[$atr_name] = $val;
        }
    }

    private function _sendGlobVars($vars){
         if (is_array($vars)){
              foreach ($vars as $k=>$v){
                   if (!isset($this->VARS[$k]) || is_null($this->VARS[$k])){
                       //
                       $this->VARS[$k] = $v;
                   }
              }
         }
         else{
            $this->VARS[0] = $vars;
         }
    }

    private function _include($input){
        if (!is_array($input)){
            $this->include_arr[] = $input;
        }
        else{
            foreach($input as $in){
                $this->include_arr[] = $in;
            }
        }
    }

    function __invoke($include):string{
         if (!$this->IS_EMPTY){
            $this->_include($include);
         }
         else if (is_string($include)){
            $this->__construct($include);
         }

        return $this->__toString();
    }

    function _tab($val=0):string{
        $tab = "\t\t";

        for ($i = 0; $i < ($this->LEVEL + $val); $i++) {
            $tab .= "\t\t";
        }

        return $tab;
    }

    function GetChildren():array{
        return $this->include_arr;
    }

    function childCount():int{
        return count($this->include_arr);
    }

    function __toString(){

        if ($this->IS_EMPTY)
            return '';

        $closer = array('<', '>', '</', ' />');

        $this->_setVars();

        $out = $this->_tab() . $closer[0] . $this->element_type;

        $TEXT = $this->clearEmptyVars($this->TEXT);

        foreach ($this->element_args as $arg=>$val){
            $out .= ' ' . $arg . '="' . $val . '"';
        }

        switch($this->element_type){
            case 'input':
                $out .= $closer[3] . $TEXT;
                break;
            default:
                $out .= $closer[1];
                   if ($TEXT != ''){
                       $out .= PHP_EOL . $this->_tab(1) . $TEXT;
                   }

                   foreach ($this->include_arr as $el) {
                        $el->LEVEL = $this->LEVEL + 1;
                        $out .= PHP_EOL . $el;
                   }
                $out .=  PHP_EOL . $this->_tab() . $closer[2] . $this->element_type . $closer[1];
                break;
        }

        return PHP_EOL . $out;
    }

    private function _setVars(){
        if (!is_null($this->VARS))
            foreach ($this->VARS as $id => $chn) {

                if (isset($this->element_args['[' . $id . ']'])) {
                    if ($chn != '') {
                        $this->element_args[$chn] = $this->element_args['[' . $id . ']'];
                    }
                    unset($this->element_args['[' . $id . ']']);
                }

                $this->TEXT = $this->TEXT != '' ? str_replace('[' . $id . ']', $chn, $this->TEXT) : '';

                foreach ($this->element_args as $a => $arg) {
                    $this->element_args[$a] = str_replace('[' . $id . ']', $chn, $arg);
                }

                for ($i = 0; $i < count($this->include_arr); $i++) {
                    $this->include_arr[$i]->_sendGlobVars($this->VARS);
                }
            }
    }

    function clearEmptyVars($in):string{//[0] ~ [..]
        $out = $in;

        for ($i=0; $i < 10; $i++){
            $out = str_replace('[' . $i . ']', '', $out);
        }

        return $out;
    }

}

?>