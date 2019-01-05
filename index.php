<?php

require_once( 'inc/generateGrid.php' );

$battleships = new Battleships();
$grid = $battleships->getGrid();

?>

<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>BattleShips Game</title>
	<link rel="stylesheet" id="app" href="src/css/app.css" type="text/css" media="all">
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <div id="board">
                <?php foreach ( $grid as $row ) : ?>
                    <div class="row">
                        <?php foreach ( $row as $column ) : ?>
                            <div class="cell" data-type="<?php echo $column; ?>"></div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="board-overlay">
                <svg viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                    <?php echo $battleships->getShips(); ?>
                </svg>
            </div>
        </div>
        <div class="controls">
            <button id="single-shot">Single Shot</button>
            <button id="multi-shot">Full Game</button>
            <button id="reset">Reset Game</button>
            <button id="regenerate">Regenerate Board</button>
            <div id="stats">
                <span id="fired"></span>
                <span id="left"></span>
            </div>
        </div>
    </div>

    <script src="src/js/jquery-3.3.1.min.js"></script>
    <script src="src/js/app.js"></script>
</body>