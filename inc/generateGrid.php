<?php

class Battleships {

	/**
	 * Let's define our constants.
	 */
	const SIZE = 10;
	const WATER = 0;


	/**
	 * Array containing initial ship info.
	 */
	const SHIPS = array(
		'I' => array(
			'cells' => 4,
			'count' => 1
		),
		'L' => array(
			'cells' => 3,
			'count' => 1
		),
		'D1' => array(
			'cells' => 1,
			'count' => 1
		),
		'D2' => array(
			'cells' => 1,
			'count' => 1
		)
	);


	private $grid;
	private $ships = array();


	function __construct() {
		$this->generateBoard();
	}


	/**
	 * Returns the board with ships for the frontend.
	 *
	 * @return array
	 */
	public function getGrid() {
		return $this->grid;
	}


	/**
	 * Returns a string with SVG paths of ships for the frontend.
	 *
	 * @return string
	 */
	public function getShips() {
		$return = '';

		foreach ( $this->ships as $ship ) {
			$x = intval($ship['x']);
			$y = intval($ship['y']);

			switch ( $ship['type'] ) {
				case 'I':
					if ( $ship['orient'] === 0 || $ship['orient'] === 2 ) {
						$return .= '<path d="M' . $x . ',' . $y . ' ' . ($x+4) . ',' . $y . ' ' . ($x+4) . ',' . ($y+1) . ' ' . $x . ',' . ($y+1) . ' Z" class="ship-I"></path>';
					} else {
						$return .= '<path d="M' . $x . ',' . $y . ' ' . ($x+1) . ',' . $y . ' ' . ($x+1) . ',' . ($y+4) . ' ' . $x . ',' . ($y+4) . ' Z" class="ship-I"></path>';
					}
					break;
				case 'L':
					if ( $ship['orient'] === 0 ) {
						$return .= '<path d="M' . $x . ',' . $y . ' ' . ($x+2) . ',' . $y . ' ' . ($x+2) . ',' . ($y-1) . ' ' . ($x+3) . ',' . ($y-1) . ' ' . ($x+3) . ',' . ($y+1) . ' ' . $x . ',' . ($y+1) . ' Z" class="ship-L"></path>';
					} elseif ( $ship['orient'] === 1 ) {
						$return .= '<path d="M' . $x . ',' . $y . ' ' . ($x+1) . ',' . $y . ' ' . ($x+1) . ',' . ($y+2) . ' ' . ($x+2) . ',' . ($y+2) . ' ' . ($x+2) . ',' . ($y+3) . ' ' . $x . ',' . ($y+3) . ' Z" class="ship-L"></path>';
					} elseif ( $ship['orient'] === 2 ) {
						$return .= '<path d="M' . $x . ',' . $y . ' ' . ($x+3) . ',' . $y . ' ' . ($x+3) . ',' . ($y+1) . ' ' . ($x+1) . ',' . ($y+1) . ' ' . ($x+1) . ',' . ($y+2) . ' ' . $x . ',' . ($y+2) . ' Z" class="ship-L"></path>';
					} elseif ( $ship['orient'] === 3 ) {
						$return .= '<path d="M' . ($x-1) . ',' . $y . ' ' . ($x+1) . ',' . $y . ' ' . ($x+1) . ',' . ($y+3) . ' ' . $x . ',' . ($y+3) . ' ' . $x . ',' . ($y+1) . ' ' . ($x-1) . ',' . ($y+1) . ' Z" class="ship-L"></path>';
					}
					break;
				case 'D1' || 'D2':
					$return .= '<path d="M' . $x . ',' . $y . ' ' . ($x+1) . ',' . $y . ' ' . ($x+1) . ',' . ($y+1) . ' ' . $x . ',' . ($y+1) . ' Z" class="ship-D"></path>';
					break;
			}
		}

		return $return;
	}


	/**
	 * Generates the board and ships.
	 */
	private function generateBoard() {
		$this->resetBoard();
		$this->generateShips();
	}


	/**
	 * Resets the board and ships.
	 */
	private function resetBoard() {
		$this->grid = array_fill(0, self::SIZE, array_fill(0, self::SIZE, 0));
		$this->ships = array();
	}


	/**
	 * Generates ships and puts them on the board if possible.
	 */
	private function generateShips() {
		// Iterate through the types of ships.
		foreach( self::SHIPS as $ship => $props ) {
			$shipSize = $props['cells'];
			$shipMarker = $ship;
			$i = 0;

			// Try to place each ship a certain number of times.
			while ( $i < $props['count'] ) {
				$shipPlacement = $this->findPlaceOnBoard($shipSize);

				// Set the maximum number of tries for placing a ship.
				$count = 0;
				$maxCount = 100;

				// If the generated position is not suitable - try again until succeed.
				while ( !$this->checkForSafePlacement($shipMarker, $shipSize, $shipPlacement["start_x"], $shipPlacement["start_y"], $shipPlacement["orientation"])) {
					if ( $count > $maxCount ) {
						// If we exceed the allowed number of tries - let's regenerate all ships from the start once again.
						$this->resetBoard();
						$this->generateShips();
						return;
					} else {
						// If the allowed number of tries isn't exceeded - try to find a place on the board.
						$shipPlacement = $this->findPlaceOnBoard($shipSize);
						$count++;
					}
				}

				// Once successfully generated, put the ship on the board.
				$this->occupySpaceOnBoard($shipSize, $shipMarker, $shipPlacement["start_x"], $shipPlacement["start_y"], $shipPlacement["orientation"], $i);

				$i++;
			}
		}
	}


	/**
	 * Generates a random ship position and returns its coordinates and orientation.
	 *
	 * @param $shipSize
	 *
	 * @return array
	 */
	private function findPlaceOnBoard($shipSize) {
		$grid = $this->grid;
		$orientation = rand(0,3);

		$maxStartCoord = (count($grid[0]) - $shipSize);

		if ($orientation == 0 || $orientation == 2) {
			// Horizontal
			$startX = rand(0,$maxStartCoord);
			$startY = rand(0,count($grid) - 1);
		} else {
			// Vertical
			$startX = rand(0,count($grid[0]) - 1);
			$startY = rand(0,$maxStartCoord);
		}

		return array("start_x" => $startX, "start_y" => $startY, "orientation" => $orientation);
	}


	/**
	 * Marks cells under the ship as occupied and updates and adds ship info to $ships array.
	 *
	 * @param $shipSize
	 * @param $shipMarker
	 * @param $startX
	 * @param $startY
	 * @param $orientation
	 */
	private function occupySpaceOnBoard($shipSize, $shipMarker, $startX, $startY, $orientation, $count) {
		if ( $orientation == 0 || $orientation == 2 ) {
			for ($i = 0; $i < $shipSize; $i++) {
				$this->grid[$startY][$startX + $i] = $shipMarker . $count;
			}
		} else {
			for ($i = 0; $i < $shipSize; $i++) {
				$this->grid[$startY + $i][$startX] = $shipMarker . $count;
			}
		}

		if ( $shipMarker === 'L' ) {
			$l = $this->additionalCellForL($startX, $startY, $orientation);
			$this->grid[$l['y']][$l['x']] = $shipMarker . $count;
		}

		$this->ships[] = array('x' => $startX, 'y' => $startY, 'orient' => $orientation, 'type' => $shipMarker);
	}


	/**
	 * Places an additional cell for an L-shaped ship
	 * given its starting position and orientation.
	 *
	 * @param $startX
	 * @param $startY
	 * @param $orientation
	 *
	 * @return array
	 */
	private function additionalCellForL($startX, $startY, $orientation) {
		$coords = array('x' => 0, 'y' => 0);

		if ( $orientation == 0 ) {
			$coords['x'] = $startX + self::SHIPS['L']['cells'] - 1;
			$coords['y'] = $startY - 1;
		} elseif( $orientation == 1 ) {
			$coords['x'] = $startX + 1;
			$coords['y'] = $startY + self::SHIPS['L']['cells'] - 1;
		} elseif( $orientation == 2 ) {
			$coords['x'] = $startX;
			$coords['y'] = $startY + 1;
		} elseif( $orientation == 3 ) {
			$coords['x'] = $startX - 1;
			$coords['y'] = $startY;
		}

		return $coords;
	}


	/**
	 * Checks if a cell is within the board.
	 *
	 * @param $x
	 * @param $y
	 *
	 * @return bool
	 */
	private function withinBoard($x, $y) {
		if ( $x < 0 || $x >= self::SIZE || $y < 0 || $y >= self::SIZE ) {
			return false;
		}
		return true;
	}


	/**
	 * Checks if current ship position is possible, i.e., is within the board and
	 * is at least one cell away from other ships.
	 *
	 * @param $shipSize
	 * @param $startX
	 * @param $startY
	 * @param $orientation
	 *
	 * @return bool
	 */
	private function checkForSafePlacement($shipMarker, $shipSize, $startX, $startY, $orientation) {

		// Use params based on ship's orientation
		if ( $orientation === 0 || $orientation === 2 ) {
			$lastX  = $shipSize;
			$lastY  = 1;
		} else {
			$lastX  = 1;
			$lastY  = $shipSize;
		}

		// Check if we can actually place a ship here
		for ($i = -1; $i <= $lastY; $i++) {
			for ( $k = -1; $k <= $lastX; $k++ ) {
				if ( !$this->withinBoard($startY + $i, $startX + $k) ) {
					// If outside the board - skip the loop
					continue;
				}
				if ($this->grid[$startY + $i][$startX + $k] !== self::WATER) {
					// If the cell contains anything except water - it's not acceptable
					return false;
				}
			}
		}

		if ( $shipMarker === 'L' ) {
			// Get coordinates of the additional cell in L-shaped ship and make sure it's within the board
			$l = $this->additionalCellForL($startX, $startY, $orientation);
			if ( !$this->withinBoard($l['x'], $l['y']) ) {
				return false;
			}

			// Check if we can place an additional cell for an L-shaped ship here
			for ($y = -1; $y <= 1; $y++) {
				for ( $x = -1; $x <= 1; $x++ ) {
					if ( !$this->withinBoard($l['y'] + $y, $l['x'] + $x) ) {
						continue;
					}
					if ($this->grid[$l['y'] + $y][$l['x'] + $x] !== self::WATER) {
						return false;
					}
				}
			}
		}

		return true;
	}

}