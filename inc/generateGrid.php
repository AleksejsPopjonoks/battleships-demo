<?php

class Battleships {

	/**
	 * Let's define our constants.
	 */
	const SIZE = 10;
	const WATER = 0;
	const HIT = 1;
	const SUNK = 2;


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
		'D' => array(
			'cells' => 1,
			'count' => 2
		)
	);


	private $grid;
	private $ships = array();


	function __construct() {
		$this->generateBoard();
	}


	/**
	 * Generates the board and puts ships on it.
	 */
	private function generateBoard() {
		$this->grid = array_fill(0, self::SIZE, array_fill(0, self::SIZE, 0));

		foreach( self::SHIPS as $ship => $props ) {
			$i = 0;
			while ( $i < $props['count'] ) {
				$this->placeShip($props['cells'], $ship);
				$i++;
			}
		}
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
				case 'D':
					$return .= '<path d="M' . $x . ',' . $y . ' ' . ($x+1) . ',' . $y . ' ' . ($x+1) . ',' . ($y+1) . ' ' . $x . ',' . ($y+1) . ' Z" class="ship-D"></path>';
					break;
			}
		}

		return $return;
	}


	/**
	 * Places a ship on a board.
	 *
	 * @param $shipSize
	 * @param $shipMarker
	 */
	private function placeShip($shipSize, $shipMarker) {
		$shipPlacement = $this->findPlaceOnBoard($shipSize);

		// If the generated position is not suitable - try again until succeed.
		$count = 0;
		while ( $count < 10000 && false === $this->checkForSafePlacement($shipSize, $shipPlacement["start_x"], $shipPlacement["start_y"], $shipPlacement["orientation"])) {
			$shipPlacement = $this->findPlaceOnBoard($shipSize);
			$count++;
		}

		// Once successfully generated, put the ship on the board.
		$this->occupySpaceOnBoard($shipSize, $shipMarker, $shipPlacement["start_x"], $shipPlacement["start_y"], $shipPlacement["orientation"]);
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
	private function occupySpaceOnBoard($shipSize, $shipMarker, $startX, $startY, $orientation) {
		if ( $orientation == 0 || $orientation == 2 ) {
			for ($i = 0; $i < $shipSize; $i++) {
				$this->grid[$startY][$startX + $i] = $shipMarker;
			}
		} else {
			for ($i = 0; $i < $shipSize; $i++) {
				$this->grid[$startY + $i][$startX] = $shipMarker;
			}
		}

		if ( $shipMarker === 'L' ) {
			$l = $this->additionalCellForL($startX, $startY, $orientation);
			$this->grid[$l['y']][$l['x']] = $shipMarker;
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
		if ( $x < 0 || $x >= self::SIZE ||  $y < 0 || $y >= self::SIZE ) {
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
	private function checkForSafePlacement($shipSize, $startX, $startY, $orientation) {
		$firstX = $lastX = $firstY = $lastY = 0;

		// Use params based on ship's orientation
		switch ($orientation) {
			case 0:
				$firstX = -1;
				$lastX   = $shipSize;
				$firstY = -1;
				$lastY   = $startY;
				break;
			case 1:
				$firstX = -1;
				$lastX   = $startX;
				$firstY = -1;
				$lastY   = $shipSize;
				break;
			case 2:
				$firstX = -1;
				$lastX   = $shipSize;
				$firstY = -1;
				$lastY   = $startY;
				break;
			case 3:
				$firstX = -1;
				$lastX   = $shipSize;
				$firstY = -1;
				$lastY   = $startY;
				break;
		}

		// Check if we can actually place a ship here
		for ($i = $firstX; $i <= $lastX; $i++) {
			for ( $k = $firstY; $k <= $lastY; $k++ ) {
				if ( !$this->withinBoard($startY + $k, $startX + $i) ) {
					// If outside the board - skip the loop
					continue;
				}
				if ($this->grid[$startY + $k][$startX + $i] !== self::WATER) {
					// If the cell contains anything except water - it's not acceptable
					return false;
				}
			}
		}

		// Get coordinates of the additional cell in L-shaped ship and make sure it's within the board
		$l = $this->additionalCellForL($startX, $startY, $orientation);
		if ( !$this->withinBoard($l['x'], $l['y']) ) {
			return false;
		}

		// Check if we can place an additional cell for an L-shaped ship here
		for ($y = -1; $y <= 1; $y++) {
			for ( $x = -1; $x <= 1; $x++ ) {
				if ( !$this->withinBoard($startY + $y, $startX + $i) ) {
					continue;
				}
				if ($this->grid[$l['y'] + $y][$l['x'] + $x] !== self::WATER) {
					return false;
				}
			}
		}

		return true;
	}

}