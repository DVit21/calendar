<?php

namespace modules\calendar;

use libs\ComLine;
use libs\drawForms;
use libs\languages;
use DateTime;
use Exception;
use DateInterval;

/**
 * Класс вывода на экран календаря
 *
 */
class calendar
{
    private DateTime $today;
    private languages $lang;
    private ComLine $cmdline;
    private array $nameMonths=array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
    private array $nameGenitiveMonths=array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
    private array $nameDays=array("Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс");

    /**
     * Конструктор
     * @param ComLine $cmdline
     * @param languages $lang
     * @param DateTime $today
     */
    public function __construct(ComLine $cmdline, languages $lang, DateTime $today=new DateTime('now'))
    {
        $this->lang=$lang;
        $this->cmdline=$cmdline;
        $this->today= $today;
    }

    /**
     * Вызов класса по умолчанию
     * @return void
     * @throws Exception
     */
    public function __invoke():void
    {
        if($this->cmdline->exist("year")){
            $year=$this->cmdline->getArgvAsType("year");
            if(!is_numeric($year)){
                $year=$this->today->format('Y');
            }
        } else {
            $year = $this->today->format('Y');
        }
        $this->drawForm($year);
        $this->checkHolidays($year);
        $this->draw($year);
        $this->legend();
    }

    /**
     * Изобразить календарь на установленный год
     * @param int $year
     * @throws Exception
     */
    public function draw(int $year):void
    {
        echo '<h1>'.$this->lang->show("Календарь на ").$year.$this->lang->show("год").'</h1>';
        echo '<div class="calendar">';
        for($i=1;$i<13;$i++){
            $this->drawMonth($year, $i);
        }
        echo '</div>';
    }

    /**
     * Вывод на экран месяца
     * @param int $year
     * @param int $month
     * @throws Exception
     */
    public function drawMonth(int $year, int $month):void
    {
        echo '<div class="month">';
        echo '<div class="header">'.$this->lang->show($this->nameMonths[$month-1]).'</div>';
        echo '<div class="nameDaysOfWeek">';
        for($i=0;$i<7;$i++){
            echo '<div class="name">'.$this->lang->show($this->nameDays[$i]).'</div>';
        }
        echo '</div>';
        $d=new DateTime($year."-".$month."-1");
        $m=$d->format("n");
        while($m==$d->format("n")) {
            $d=$this->drawWeek($d);
        }
        echo '</div>';
    }

    /**
     * Вывод на экран недели
     * @param DateTime $firstDay
     * @return DateTime
     * @throws Exception
     */
    private function drawWeek(DateTime $firstDay):DateTime
    {
        $month=$firstDay->format("n");
        echo '<div class="week">';
        $dw=$firstDay->format("N");
        if($dw>1) {
            for($i=0;$i<($dw-1);$i++){
                echo '<div class="emptyCell">&nbsp;</div>';
            }
        }
        for($i=$dw;$i<8;$i++) {
            if($month==$firstDay->format("n")) {
                $this->drawDay($firstDay);
            } else {
                echo '<div class="usualDay">&nbsp;</div>';
            }
            $firstDay->add(new DateInterval("P1D"));
        }
        echo '</div>';
        return $firstDay;
    }

    /**
     * @throws Exception
     */
    private function drawDay(DateTime $day):void
    {
        $d=$day->format("d");
        if ($this->checkWeekend($day)){
            echo '<div class="weekend">';
        }
        else echo '<div class="workday">';
        //Сегодняшний день
        if($day->format("Y-m-d")==$this->today->format("Y-m-d")){
            echo '<div class="today">' . $d . '</div>';
        } else {
            //Праздники
            $str=$day->format("m-d");
            if(isset($this->churchHolidays[$str])) {
                echo '<div class="' . $this->churchHolidays[$str] . '">' . $d . '</div>';
            }elseif($this->checkKurbanAit($day)) {
                echo '<div class="muslimDay">' . $d . '</div>';
            }elseif(isset($this->nationalHolidays[$str])){
                echo '<div class="' . $this->nationalHolidays[$str] . '">' . $d . '</div>';
            } else {
                echo '<div class="usualDay">' . $d . '</div>';
            }
        }
        echo '</div>';
    }
    private function checkWeekend(DateTime $day):bool
    {
        $dw=$day->format("w");
        $str=$day->format("m-d");
        if(($dw==6)||($dw==0)) {//Если выходной
            if(isset($this->nationalHolidays[$str])){//Если выходной приходится на праздник
                $new=clone $day;
                while(1) {
                    $new->add(new DateInterval("P1D"));
                    $nd=$new->format("w");
                    if(($nd>0)&&($nd<6)){
                        $str=$new->format("m-d");
                        if(isset($this->nationalHolidays[$str])) continue;
                        $this->additionalHolidays[$str]="weekend";
                        return false;
                    }

                }
            }
            return true;
        }
        if(isset($this->additionalHolidays[$str])) return true;
        return false;
    }
    private function drawForm(int $year): void
    {
        $y=$this->today->format("Y")-4;
        $max=$y+9;
        $sel=array();
        for(;$y<$max;$y++){
            $sel[]=array("item"=>$y,"value"=>$y);
        }
        echo '<div class="form">';
        $form=new drawForms();
        $form->form($this->cmdline->CreateString(),"POST");
        $form->select("Выберите год:","year",$sel,$year);
        $form->submit("set",1,"Выбрать");
        $form->eof();
        echo '</div>';
    }
    private function checkHolidays(int $year):void
    {
        $t=$this->orthodox_eastern($year);
        $dt = new DateTime();
        $dt->setTimestamp($t);
        $this->churchHolidays[$dt->format("m-d")]="churchDay";
        $this->churchHolidaysNames[$dt->format("m-d")]="Православная пасха";
    }
    private array $nationalHolidays=array("01-01"=>"nationDay","01-02"=>"nationDay", "03-08"=>"nationDay",
        "03-21"=>"nationDay", "03-22"=>"nationDay", "03-23"=>"nationDay",
        "05-01"=>"nationDay", "05-07"=>"nationDay","05-09"=>"nationDay","07-06"=>"nationDay","08-30"=>"nationDay",
        "10-25"=>"nationDay","12-16"=>"nationDay");
    private array $additionalHolidays=array();
    private array $nationalHolidaysName=array("01-01"=>"Новый год",
                                    "03-08" => "Международный женский день",
                                    "03-21" => "Наурыз мейрамы",
                                    "05-01" => "Праздник единства народа Казахстана",
                                    "05-07" => "День защитника Отечества",
                                    "05-09" => "День Победы",
                                    "07-06" => "День Столицы",
                                    "08-30" => "День Конституции Республики Казахстан",
                                    "10-25" => "День Республики",
                                    "12-16" => "День Независимости");
    private array $churchHolidays=array("01-07"=>"churchDay");
    private array $churchHolidaysNames=array("01-07"=>"Рождество");
    private function orthodox_eastern(int $year):int
    {
        $a=$year %4;
        $b=$year %7;
        $c=$year %19;
        $d=(19*$c+15)%30;
        $e=(2*$a+4*$b-$d+34)%7;
        $month=floor(($d+$e+114)/31);
        $day=(($d+$e+114)%31)+1;
        return mktime(0,0,0,$month,$day+13,$year);
    }
    private function legend():void
    {
        echo '<div class="clear"></div>';
        echo '<div class="legend">';
        echo '<div class="partLegend">';
        echo '<h2 style="color: red;">'.$this->lang->show("Государственные праздники").'</h2>';
        echo '<ul>';
        foreach ($this->nationalHolidaysName as $d=>$item){
            echo '<li>';
            $new=explode("-", $d);
            echo intval($new[1]).' '.$this->nameGenitiveMonths[intval($new[0])-1].' - '.$item;
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '<div class="partLegend">';
        echo '<h2 style="color: darkblue;">'.$this->lang->show("Православные праздники").'</h2>';
        echo '<ul>';
        foreach ($this->churchHolidaysNames as $d=>$item){
            echo '<li>';
            $new=explode("-", $d);
            echo intval($new[1]).' '.$this->nameGenitiveMonths[intval($new[0])-1].' - '.$item;
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '<div class="partLegend">';
        echo '<h2 style="color: darkblue;">'.$this->lang->show("Мусульманские праздники").'</h2>';
        echo '<ul>';
        echo '<li>';
        $new=explode("-", $this->gregKurbanAit);
        echo intval($new[1]).' '.$this->nameGenitiveMonths[intval($new[0])-1].' - Курбан-айт';
        echo '<li>';
        echo '</ul>';
        echo '</div>';

        echo '</div>';
    }
    /*
     * Проверка праздника курбан-айт -10 день 12 месяца зуль-хиджа
     */
    /**
     * @throws Exception
     */
    private function checkKurbanAit(DateTime $checkDate):bool
    {
        if($this->kaSet) {
            if($checkDate->format("m-d")==$this->gregKurbanAit) return true;
            return false;
        }
        if(!isset($this->hijriCalendar)){
            $this->hijriCalendar=new \phpHijri\Calendar();
        }
        $day=$this->hijriCalendar->gregorianToHijri($checkDate->format("Y"), $checkDate->format("n"), $checkDate->format("j"));
        if(($day['d']<=10)||($day['m']<12)) {
            $this->kurbanAit["y"]=$day["y"];
            $nd=$this->hijriCalendar->hijriToGregorian($this->kurbanAit["y"],$this->kurbanAit["m"],$this->kurbanAit["d"]);
            $d=new DateTime($nd['y']."-".$nd['m'].'-'.$nd['d']);
            $this->gregKurbanAit=$d->format("m-d");
            $this->kaSet=true;
            if(($day['d']==10)&&($day['m']==12)) return true;
        }
        return false;
    }
    private \phpHijri\Calendar $hijriCalendar;
    private bool $kaSet=false;
    private array $kurbanAit=array ("y"=>0,"m"=>12,"d"=>10);
    private string $gregKurbanAit="m-d";
}