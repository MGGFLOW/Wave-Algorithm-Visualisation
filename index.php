<?php
define('CLASSES_DIR','Data/Classes');

function classPathCreate($class_name){
    $path = CLASSES_DIR.'/'.$class_name.'/'.$class_name.'.php';

    return $path;
}

spl_autoload_register(function ($class_name) {
    require classPathCreate($class_name);
});

$width = 20; // width of maze
$height = 16; // height of maze

$wave_alg = new wave_maze_passage($width,$height); // create maze with width and height
$wave_alg->wall_symbol = 'W'; // set symbol for walls
$wave_alg->hall_symbol = 'M'; // set symbol for halls
$wave_alg->in_symbol = 'A'; // set symbol for entry point
$wave_alg->out_symbol = 'B'; // set symbol for exit point
$wave_alg->mark_span_class = 'colortext'; // set CSS class name of span for mark the way

$move_mode = 0; // 0 - cross movement (+), 1 - asterisk movement (✵)
$stop_mode = 0; // 0 - waves to end, 1 - waves to exit point

$inx = rand(0,$width-1); // X entry point coordinate
$iny = rand(0,$height-1); // Y entry point coordinate

do{
    $outx = rand(0,$width-1); // X exit point coordinate
    $outy = rand(0,$height-1); // Y exit point coordinate
}while($outx==$inx and $outy==$iny);


?>

<!doctype html>
<html lang="en">
<head>
    <title>Wave algorithm visualisation</title>
    <meta charset="utf-8">
</head>
<style>
    .colortext { /* class for mark the way*/
        color: red;
    }
</style>
<body>

    <?php
        if($wave_alg->waves($inx,$iny,$outx,$outy,$move_mode,$stop_mode)) // fill the maze
            $wave_alg->viewMaze(); // view maze
    ?>

</body>
</html>