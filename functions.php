<?php

if ($_SERVER['HTTP_HOST'] != "scheduler.rickokkersen.ga") {
    header("Location: https://scheduler.rickokkersen.ga".str_replace("/scheduler", "", $_SERVER['REQUEST_URI']));
}

class Scheduler extends Functions {
    public $users = [];

    private $codes = [];
    private $timings = [];

    function __construct() {
        $this->users = json_decode(file_get_contents('config/users.json'), true);
        $this->timings = json_decode(file_get_contents('config/timings.json'), true);
        foreach($this->users as $u){$this->codes[]=$u['code'];}
    }

    function get_user($u) {
        return in_array($u, $this->codes);
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

    function get_events($x) {
        $d=json_decode(file_get_contents('events/'.$x.'.json'),true);
        $e_dertig=$e_vijftig=[];
        $e_dertig=$e_vijftig=array_fill(0,9,array_fill(0,5,0));
        foreach ($d as $ev){
            $date=explode(" ",$ev['start'])[0];
            if (Functions::date_in_week($date)) {
                if($ev['vijftig']!=0){$e_vijftig[$ev['vijftig']-1][Functions::day_of_week($date)]=$ev;}
                if($ev['dertig']!=0){$e_dertig[$ev['dertig']-1][Functions::day_of_week($date)]=$ev;}
            }
        }
        return $e_dertig;
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