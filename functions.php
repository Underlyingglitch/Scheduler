<?php

class Scheduler {
    public $users = [];

    private $codes=[];

    function __construct() {
        foreach($this->users as $u){$this->codes[]=$u['code'];}
        $this->users = json_decode(file_get_contents('config/users.json'), true);
    }

    function date_in_week($d) {
        if(date("Y-m-d", strtotime($d)) > date("Y-m-d", strtotime('sunday last week')) && date("Y-m-d", strtotime($d)) < date("Y-m-d", strtotime('sunday this week'))) {
            return true;
        }
        return false;
    }

    function day_of_week($d) {
        return date('w', strtotime($d))-1;
    }

    function get_user($u) {
        foreach ($this->users as $x) {
            if ($u == $x['code']) {
                return $x;
            }
            return false;
        }
    }

    function generate_string($n) {
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
            if (!in_array($c, $codes)) {
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
            if ($this->date_in_week($date)) {
                if($ev['vijftig']!=0){$e_vijftig[$ev['vijftig']-1][$this->day_of_week($date)]=$ev;}
                if($ev['dertig']!=0){$e_dertig[$ev['dertig']-1][$this->day_of_week($date)]=$ev;}
            }
        }
        return $e_dertig;
    }
}

?>