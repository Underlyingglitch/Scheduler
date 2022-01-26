<?php

if ($_SERVER['HTTP_HOST'] != "scheduler.rickokkersen.ga") {
    header("Location: https://scheduler.rickokkersen.ga".str_replace("/scheduler", "", $_SERVER['REQUEST_URI']));
}

class Scheduler extends Functions {
    public $users = [];

    public $user;

    private $codes = [];
    private $timings = [];

    function __construct($_user) {
        $this->users = json_decode(file_get_contents('config/users.json'), true);
        $this->timings = json_decode(file_get_contents('config/timings.json'), true);
        foreach($this->users as $u){$this->codes[]=$u['code'];}
        $this->user = $_user;
    }

    function get_user() {
        return in_array($this->user, $this->codes);
    }

    private function generate_string($n) {
        $c = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $r = '';
        for ($i = 0; $i < $n; $i++) {
            $r .= $c[rand(0, strlen($c) - 1)];
        }
        return $r;
    }

    function new_code() {
        while (true) {
            $c = $this->generate_string(20);
            if (!in_array($c, $this->codes)) {
                return $c;
            }
        }
    }

    function get_events($hs) {
        $d=json_decode(file_get_contents('events/'.$this->user.'.json'),true);
        $e=[];
        $e=array_fill(0,9,array_fill(0,5,0));
        foreach ($d as $ev){
            $date=explode(" ",$ev['start'])[0];
            if (Functions::date_in_week($date)) {
                if ($ev[($hs)?'dertig':'vijftig']!=0) {
                    $e[$ev[($hs)?'dertig':'vijftig']-1][Functions::day_of_week($date)]=$ev;
                }
            }
        }
        return $e;
    }

    function create_user($c, $n, $u) {
        $f = json_decode(file_get_contents('config/users.json'), true);
        $f[] = ["url" => str_replace("webcal", "https", $u), "name" => $n, "code" => $c];
        if (file_put_contents('config/users.json', json_encode($f))) {
            return true;
        }
        return;
    }

    function valid_url($u) {
        if (strpos($u, 'webcal://lvo.itslearning.com/Calendar/CalendarFeed.ashx') !== false) {
            return true;
        }
        return;
    }

    function get_name($x) {
        foreach ($this->users as $u) {
            if ($u['code'] == $x) {
                return $u['name'];
            }
        }
    }

    function get_homework($x) {
        $a = explode(":", $x);
        if (count($a) > 1 && strpos($a[0], '(komt van') == false) {
            unset($a[0]);
            return str_replace(
                [
                    ' De les is verplaatst',
                    ' Les vervalt'
                ],
                "",
                preg_replace(
                    [
                        '/(\([A-Za-z]{3}[0-9]{1,2}\))/',
                        '/\\\\r/',
                        '/.(\(komt van).*?\./'
                    ],
                    "",
                    implode(":", $a)
                )
            );
        }
        return;
    }

    function get_schedule() {
        $d=json_decode(file_get_contents('events/'.$this->user.'.json'),true);
        foreach ($d as $e) {
            if (Functions::date_in_week(explode(" ",$e['start'])[0]) && $e['vijftig'] == 0) {
                return true;
            }
        }
        return false;
    }
}

class Functions {

    function date_in_week($d) {
        return (date("Y-m-d", strtotime($d)) > date("Y-m-d", strtotime('sunday last week')) && date("Y-m-d", strtotime($d)) < date("Y-m-d", strtotime('sunday this week')));
    }

    function day_of_week($d) {
        return date('w', strtotime($d))-1;
    }

    function delete_all_between($beginning, $end, $string) {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }
      
        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
      
        return $this->delete_all_between($beginning, $end, str_replace($textToDelete, '', $string)); // recursion to ensure all occurrences are replaced
    }
}

?>