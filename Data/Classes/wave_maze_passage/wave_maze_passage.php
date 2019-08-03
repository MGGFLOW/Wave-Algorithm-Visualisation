<?php
/*
* Pavel Eliseev, State Marine Technical University, 3170, deusagnis@gmail.com
*/
class wave_maze_passage{
    public $wall_symbol = 'W';
    public $hall_symbol = 'M';
    public $in_symbol = 'A';
    public $out_symbol = 'B';
    public $move_mode = 0;
    public $stop_mode = 0;
    public $mark_span_class = 'colortext';

    public $width;
    public $height;

    public $in_x;
    public $in_y;

    public $out_x;
    public $out_y;

    public $maze;
    public $pull;

    public function __construct($width,$height){
        $this->width = $width;
        $this->height = $height;
    }

    public function createMaze(){
//        $maze = array_map(function(){return array_map(function(){return mt_rand(0,2)%2==0 ? "M" : "W";},range(1,$this->width));},range(1,$this->height));
        $maze = array_chunk(array_map(function(){return mt_rand(0,2)%2==0 ? $this->hall_symbol : $this->wall_symbol;},range(1,$this->width*$this->height)),$this->width);

        $this->maze = $maze;
    }

    public function viewMaze(){
        echo '<table border="1" cellpadding="5">';
        array_walk($this->maze,function($value){echo '<tr><th>'.join('</th><th>',$value).'</th><tr>';});
        echo '</table>';
    }


    public function markCell($x,$y){
        $this->maze[$y][$x] = '<span class="'.$this->mark_span_class.'">'.$this->getClearCell($x,$y).'</span>';
    }

    public function getClearCell($x,$y){
        $value = $this->maze[$y][$x];
        preg_match('/>(.*)</',$value,$matches);

        if(isset($matches[1])){
            return $matches[1];
        }

        return $value;
    }

    public function setIn($x,$y){
        $x>=0 ?: $x = rand(0,$this->width-1);
        $y>=0 ?: $y = rand(0,$this->height-1);

        $this->in_x = $x;
        $this->in_y = $y;

        $this->maze[$y][$x] = $this->in_symbol;
        $this->markCell($x,$y);
    }

    public function setOut($x,$y){
        $x>=0 ?: $x = rand(0,$this->width-1);
        $y>=0 ?: $y = rand(0,$this->height-1);

        $this->out_x = $x;
        $this->out_y = $y;

        $this->maze[$y][$x] = $this->out_symbol;
        $this->markCell($x,$y);
    }

    public function getAmbit($x,$y){
        $ambit = [];

        for($i=-1;$i<=1;$i++){
            for($j=-1;$j<=1;$j++){
                if($i+$x>=0 and $j+$y>=0 and $i+$x<$this->width and $j+$y<$this->height and !($i==0 and $j==0)){
                    if(($i<>0 and $j<>0) and $this->move_mode==0) continue;

                    $ambit[] = ['x'=>$i+$x,'y'=>$j+$y];
                }
            }
        }

        return $ambit;
    }

    public function initPull(){
        $this->pull[] = ['x'=>$this->in_x,'y'=>$this->in_y];
    }

    public function updatePull(){
        if(count($this->pull)==0) return false;

        foreach ($this->pull as $pull_index=>$cell){
            $maze_value = $this->getClearCell($cell['x'],$cell['y']);

            if($maze_value==$this->in_symbol){
                $maze_value = 0;
            }

            $ambit = $this->getAmbit($cell['x'],$cell['y']);

            foreach ($ambit as $two){
                $ambit_value = $this->getClearCell($two['x'],$two['y']);
                if($ambit_value==$this->out_symbol and $this->stop_mode!=0){
                    $this->pull = [];
                    return true;
                }
                if($ambit_value==$this->hall_symbol){
                    $this->maze[$two['y']][$two['x']] = $maze_value+1;
                    $this->pull[] = $two;
                }
            }

            unset($this->pull[$pull_index]);
        }

        return true;
    }

    public function chooseNearMin($x,$y){
        $answer = false;
        $min = -1;

        $ambit = $this->getAmbit($x,$y);

        foreach ($ambit as $two){
            $ambit_value = $this->getClearCell($two['x'],$two['y']);
            if(is_numeric($ambit_value)){
                if($min==-1 or $ambit_value<$min){
                    $answer = $two;
                    $min = $ambit_value;
                }
            }elseif($ambit_value==$this->in_symbol){
                return false;
            }
        }

        return $answer;
    }

    public function markWay(){
        $x = $this->out_x;
        $y = $this->out_y;
        $find_flag = true;

        while ($find_flag){
            $near_min = $this->chooseNearMin($x,$y);
            if(is_array($near_min)){
                $x = $near_min['x'];
                $y = $near_min['y'];
                $this->markCell($x,$y);
            }else{
                break;
            }
        }
    }

    private function checkInput($inx,$iny,$outx,$outy){
        $c_flag = true;
        if($inx>=$this->width or $inx<0) $c_flag = false;
        if($iny>=$this->height or $iny<0) $c_flag = false;
        if($outx>=$this->width or $outx<0) $c_flag = false;
        if($outy>=$this->height or $outy<0) $c_flag = false;
        if($inx==$outx and $iny==$outy) $c_flag = false;

        return $c_flag;
    }

    public function waves($inx,$iny,$outx,$outy,$move_mode,$stop_mode){
        if($this->checkInput($inx,$iny,$outx,$outy)){
            $this->createMaze();
            $this->setIn($inx,$iny);
            $this->setOut($outx,$outy);
            $this->move_mode = $move_mode;
            $this->initPull();
            $this->stop_mode = $stop_mode;

            $i = 0;
            while($this->updatePull()){
                $i++;
                if($i>=$this->width*$this->height)
                    break;
            }

            $this->markWay();
            return true;
        }
        return false;
    }
}
?>