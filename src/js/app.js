(function() {
	"use strict";

	var $ = jQuery;


	/**
	 * Define our constants.
	 */
	const WATER = 0;
	const HIT = 1;


	/**
	 * Define default containers for the cells.
	 */
	var defCells = [];
	var totalCells = 0;


	/**
	 * Define global variables.
	 */
	var cells = [];
	var ships = {};
	var availableCells = 0;
	var shipsAlive = 0;
	var shots = 0;
	var shotInt;


	/**
	 * Runs initial setup, creates a multidimensional array of cells
	 * and an object containing ship data.
	 */
	function setup() {
		$('#board').find('.row').each(function(index) {
			var row = index;
			defCells[row] = [];
			$(this).find('.cell').each(function(index) {
				var ship = $(this).data('type');
				defCells[row][index] = ship;
				totalCells++;

				if ( ship !== WATER ) {
					if ( ship === 'D' && ships['D'] ) {
						ships['D1'] = [];
						ships['D1'].push([row,index]);
						defCells[row][index] = 'D1';
					} else {
						if ( !ships[ship] ) {
							ships[ship] = [];
						}
						ships[ship][ships[ship].length] = [row,index];
					}
				}
			});
		});
		cells = JSON.parse(JSON.stringify(defCells));
		availableCells = totalCells;
		shipsAlive = Object.keys(ships).length;
	}


	/**
	 * Randomly chooses shot coordinates.
	 *
	 * @returns {{x: number, y: number}}
	 */
	function shotCoords() {
		var x = Math.floor(Math.random() * 10);
		var y = Math.floor(Math.random() * 10);
		return {'x': x, 'y': y};
	}


	/**
	 * Checks if the coordinates are valid for shooting.
	 *
	 * @param y
	 * @param x
	 * @returns {boolean}
	 */
	function validCoords(y, x) {
		return cells[y][x] !== HIT;
	}

	/**
	 * Checks if the game should continue, i.e.,
	 * there are unchecked cells left or any ships left alive.
	 *
	 * @returns {boolean}
	 */
	function gameOver() {
		return !availableCells > 0 || !shipsAlive > 0;
	}


	/**
	 * Makes a shot.
	 */
	function makeShot() {
		if ( !gameOver() ) {
			var coords = shotCoords();

			// If the generated coordinates are not suitable - try again until succeed.
			while ( !validCoords(coords.y, coords.x) ) {
				coords = shotCoords();
			}

			if ( cells[coords.y][coords.x] !== WATER ) {
				// If we hit anything but an empty water cell.
				maybeSinkShip(coords.y, coords.x);
			} else {
				// If hit an empty cell.
				cells[coords.y][coords.x] = HIT;
				availableCells--;
				updateGrid(coords.y, coords.x);
			}
			shots++;

			// If the game is over - update status and stop the process
			if ( gameOver() ) {
				clearInterval(shotInt);
				switchStatus('finished');
				showStats();
			}
		} else {
			// If the game is over - update status
			switchStatus('finished');
			showStats();
		}
	}


	/**
	 * Starts a series of shots.
	 */
	function multiShot() {
		switchStatus('active');
		shotInt = setInterval(function() {
			if ( !gameOver() ) {
				makeShot();
			}
		}, 100);
	}


	/**
	 * Checks if we have hit a ship, and if yes - sink it and
	 * mark the area underneath and around it.
	 *
	 * @param y
	 * @param x
	 */
	function maybeSinkShip(y, x) {
		var sinkShip = false;

		// Check the cell coordinates belong to any ship
		$.each(ships, function(ship, coords) {
			for ( var k = 0; k < coords.length; k++ ) {
				if ( coords[k][0] === y && coords[k][1] === x ) {
					sinkShip = ship;
					shipsAlive--;
					break;
				}
			}
		});

		// If it's a ship, mark area underneath and around it as "hit".
		for ( var i = 0; i < ships[sinkShip].length; i++ ) {
			for ( var j = -1; j <= 1; j++ ) {
				for ( var k = -1; k <= 1; k++ ) {
					var y_coord = Math.max(Math.min(ships[sinkShip][i][0] + j, 9), 0);
					var x_coord = Math.max(Math.min(ships[sinkShip][i][1] + k, 9), 0);
					if ( cells[y_coord][x_coord] !== HIT ) {
						cells[y_coord][x_coord] = HIT;
						availableCells--;
						updateGrid(y_coord, x_coord);
					}
				}
			}
		}
	}


	/**
	 * Switch the status of the control panel.
	 *
	 * @param status
	 */
	function switchStatus(status) {
		var $c = $('.controls');
		$c.removeClass('finished active');
		$c.addClass(status);
	}


	/**
	 * Display the game stats after it's finished.
	 */
	function showStats() {
		$('#fired').text("Shots Fired: " + shots);
		$('#left').text("Cells Left: " + availableCells);
	}


	/**
	 * Reset the board and revert all hits.
	 */
	function reset() {
		if ( !gameOver() ) {
			clearInterval(shotInt);
		}

		cells = JSON.parse(JSON.stringify(defCells));
		availableCells = totalCells;
		shipsAlive = Object.keys(ships).length;
		shots = 0;
		$('#board').find('.cell').removeClass('hit');
		$('#fired, #left').text('');
		switchStatus(false);
	}


	/**
	 * Add a visual result of a hit to the board.
	 *
	 * @param y
	 * @param x
	 */
	function updateGrid(y, x) {
		$('#board').find('.row').eq(y).find('.cell').eq(x).addClass('hit');
	}


	$(document).ready(function() {
		setup();
		$(document).on('click', '#single-shot', makeShot);
		$(document).on('click', '#multi-shot', multiShot);
		$(document).on('click', '#reset', reset);
		$(document).on('click', '#regenerate', function() {
			location.reload();
			return false;
		});
	});

})(jQuery);
