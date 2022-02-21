<?php

if ($_SERVER['HTTP_HOST'] != "scheduler.rickokkersen.ga") {
    header("Location: https://scheduler.rickokkersen.ga".str_replace("/scheduler", "", $_SERVER['REQUEST_URI']));
}

class Scheduler extends Functions {
    public $users = [];

    private $user;
    private $userdata;
    private $userevents;

    private $codes = [];
    private $timings = [];

    function __construct($_user, $_next) {
        $this->users = json_decode(file_get_contents('../config/users.json'), true);
        $this->timings = json_decode(file_get_contents('../config/timings.json'), true);
        $this->userevents=json_decode(file_get_contents('../events/'.$_user.'.json'),true);
        foreach($this->users as $u){
            $this->codes[]=$u['code'];
            if ($u['code'] == $_user) {
                $this->userdata = $u;
            }
        }
        $this->user = $_user;
        Functions::__construct($_next);
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
        $e=[];
        $e=array_fill(0,9,array_fill(0,5,0));
        foreach ($this->userevents as $ev){
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
        $f = json_decode(file_get_contents('../config/users.json'), true);
        $f[] = ["url" => str_replace("webcal", "https", $u), "name" => $n, "code" => $c];
        if (file_put_contents('../config/users.json', json_encode($f))) {
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
        return $this->userdata['name'];
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
        foreach ($this->userevents as $e) {
            if (Functions::date_in_week(explode(" ",$e['start'])[0]) && $e['vijftig'] == 0) {
                return true;
            }
        }
        return false;
    }

    function get_last_update() {
        return $this->userdata['last_updated'];
    }
}

class Functions {

    public $next;

    function __construct($_next) {
        $this->next = $_next;
    }

    function date_in_week($d) {
        if ($this->next) {
            return (date("Y-m-d", strtotime($d)) > date("Y-m-d", strtotime('sunday this week')) && date("Y-m-d", strtotime($d)) < date("Y-m-d", strtotime('sunday next week')));
        }
        return (date("Y-m-d", strtotime($d)) > date("Y-m-d", strtotime('sunday last week')) && date("Y-m-d", strtotime($d)) < date("Y-m-d", strtotime('sunday this week')));
    }

    function day_of_week($d) {
        return date('w', strtotime($d))-1;
    }
}

?>